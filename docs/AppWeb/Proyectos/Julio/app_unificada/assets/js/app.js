import { renderTopbar, startClock, loadPageContent } from './common.js';

document.addEventListener('DOMContentLoaded', async ()=>{
  renderTopbar();
  startClock();

  // helper for accessing stored users
  const getUsers = ()=> JSON.parse(localStorage.getItem('users||demo') || '{}');
  const saveUsers = (o)=> localStorage.setItem('users||demo', JSON.stringify(o));

  // mode buttons wiring (legacy mode buttons) — navigate directly to pages
  document.querySelectorAll('.mode-btn').forEach(btn=>{
    btn.addEventListener('click', (e)=>{
      const page = btn.dataset.page;
      // navigate to dedicated page instead of injecting into index
      window.location.href = `pages/${page}.html`;
    });
  });

  // login form submission (index)
  const loginForm = document.getElementById('login-form');
  if(loginForm){
    loginForm.addEventListener('submit', (ev)=>{
      ev.preventDefault();
      const email = document.getElementById('login-email').value.trim().toLowerCase();
      const pass = document.getElementById('login-password').value;
      const selectedMode = document.querySelector('input[name="mode"]:checked')?.value;
      const users = getUsers();
      const user = users[email];
      if(!user){
        // user not found - prompt to register
        if(confirm('Usuario no encontrado. ¿Quieres crear una cuenta con este correo?')){
          localStorage.setItem('pending_register_email', email);
          // navigate to register page
          window.location.href = 'pages/register.html';
        }
        return;
      }
      // very basic password check (demo)
      if(user.password !== pass){ alert('Contraseña incorrecta'); return; }

      // set current user
      localStorage.setItem('currentUser||demo', email);

      // if user hasn't set a selected mode yet but login form offered one, store it
      if(!user.selectedMode && selectedMode){
        users[email].selectedMode = selectedMode;
        saveUsers(users);
      }

      // if first run (no preferences) -> go to selectedMode preferences or select_mode
      const updatedUser = users[email];
      if(updatedUser.firstRun || !updatedUser.preferences){
        const target = updatedUser.selectedMode || 'select_mode';
        // go to the dedicated page for preferences / selection
        if(target === 'select_mode') window.location.href = 'pages/select_mode.html';
        else window.location.href = `pages/${target}.html`;
        return;
      }

      // otherwise go to map
      window.location.href = 'pages/map.html';
    });
  }

  // handle back/forward
  window.addEventListener('popstate', async (ev) => {
    const page = (ev.state && ev.state.page) || location.hash.replace('#','') || '';
    if(page) await loadPageContent(page);
  });

  // intercept internal hash anchors and navigate to dedicated pages instead
  document.addEventListener('click', (e)=>{
    const a = e.target.closest && e.target.closest('a[href]');
    if(!a) return;
    const href = a.getAttribute('href');
    if(!href) return;
    if(href.startsWith('#')){
      e.preventDefault();
      const name = href.replace(/^#/, '') || '';
      // map logical names to page files
      if(name === 'register') window.location.href = 'pages/register.html';
      else if(name === 'select_mode') window.location.href = 'pages/select_mode.html';
      else if(name === 'map') window.location.href = 'pages/map.html';
      else if(name) window.location.href = `pages/${name}.html`;
    }
  });

  // if URL has hash, load initial page (or default to index content)
  const initial = location.hash.replace('#','');
  if(initial){ await loadPageContent(initial); }
  else {
    // if there's already a logged in user, auto route them
    const current = localStorage.getItem('currentUser||demo');
    const users = getUsers();
    if(current && users[current]){
      const u = users[current];
      if(u.firstRun || !u.preferences){
        const target = u.selectedMode || 'select_mode';
        history.replaceState({page:target}, '', `#${target}`);
        await loadPageContent(target);
      } else {
        history.replaceState({page:'map'}, '', '#map');
        await loadPageContent('map');
      }
    }
  }
});

// (renderTopbar + startClock are triggered after loadPageContent in common.js)
