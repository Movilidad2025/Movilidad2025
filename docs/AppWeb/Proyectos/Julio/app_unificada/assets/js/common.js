// Shared helpers + topbar for the unified app
export function renderTopbar(containerSelector = '#topbar-target'){
  // If page already has a top-bar, re-use it and attach behaviors
  const existing = document.querySelector('.top-bar');
  let container = existing || document.querySelector(containerSelector);
  // if there is no container on the page, create one at the top of body
  if(!container){
    const wrapper = document.createElement('div');
    wrapper.id = containerSelector.replace('#','') || 'topbar-target';
    document.body.insertBefore(wrapper, document.body.firstChild);
    container = wrapper;
  }
  if(!existing){
    container.innerHTML = `
      <div class="topbar card">
      <div class="left">
        <button class="icon-btn" id="dark-mode" title="Modo oscuro"><i class="fas fa-moon"></i></button>
        <button class="icon-btn" id="text-size" title="Ajustar texto"><i class="fas fa-text-height"></i></button>
      </div>
      <div class="center"><div id="clock">00:00</div></div>
      <div class="right">
        <button class="icon-btn" id="lang" title="Idioma"><i class="fas fa-globe"></i></button>
        <button class="icon-btn" id="sound" title="Sonido"><i class="fas fa-volume-up"></i></button>
      </div>
    </div>`;
  }

  // initialize dark-mode based on saved preference
  try{
    const saved = localStorage.getItem('ui_dark') === '1';
    if(saved) document.body.classList.add('dark-mode');
    else document.body.classList.remove('dark-mode');
  } catch(e){ }

  // wire actions
  // Attach behaviors to buttons in whichever topbar is present
  const darkBtn = container.querySelector('#dark-mode') || container.querySelector('#dark-mode-btn') || container.querySelector('.icon-btn#dark-mode');
  if(darkBtn) darkBtn.addEventListener('click', () => {
    const is = document.body.classList.toggle('dark-mode');
    try{ localStorage.setItem('ui_dark', is ? '1' : '0'); }catch(e){}
  });

  const soundBtn = container.querySelector('#sound') || container.querySelector('#sound-btn');
  if(soundBtn) soundBtn.addEventListener('click', function(){
    const i = this.querySelector('i');
    if(i) i.classList.toggle('fa-volume-up'), i.classList.toggle('fa-volume-xmark');
  });
}

export function startClock(selector = '#clock'){
  const el = document.querySelector(selector);
  if(!el) return;
  function update(){
    const d = new Date();
    el.textContent = String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
  }
  update();
  // avoid creating multiple intervals when called repeatedly
  if(window.__mob_clock_interval) return window.__mob_clock_interval;
  window.__mob_clock_interval = setInterval(update, 60_000);
  return window.__mob_clock_interval;
}

// auto initialize topbar+clock when the module is loaded directly on a page
if(typeof window !== 'undefined'){
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', ()=>{ renderTopbar(); startClock(); });
  } else {
    // run immediately
    try{ renderTopbar(); startClock(); }catch(e){}
  }
}

export async function loadPageContent(pageName){
  const url = `pages/${pageName}.html`;
  try{
    const res = await fetch(url);
    if(!res.ok) throw new Error('No encontrado: '+url);
    const html = await res.text();
    document.getElementById('page-root').innerHTML = html;
    // re-render shared topbar + clock in case page includes its own topbar
    try{ renderTopbar(); startClock(); } catch(e){}
    // If page contains scripts inside, run them
    const scripts = [...document.getElementById('page-root').querySelectorAll('script')];
    scripts.forEach(s=>{
      const newS = document.createElement('script');
      if(s.src) newS.src = s.src;
      else newS.textContent = s.textContent;
      document.body.appendChild(newS);
      s.remove();
    });
  } catch(err){
    document.getElementById('page-root').innerHTML = `<div class="card">Error cargando ${pageName}: ${err.message}</div>`;
  }
}
