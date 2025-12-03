# App Unificada — Movilidad

Esta carpeta contiene la versión homologada del proyecto Julio — un único punto de entrada que reutiliza elementos comunes (topbar, reloj, estilos y scripts) para las interfaces de Silla de ruedas, Bicicleta y Peatón.

Estructura importante:

 - index.html — Entrada principal (SPA ligero). Navega directamente a páginas dedicadas (`pages/*.html`) en lugar de cargarlas dentro del index.
	*Nota:* La app ahora navega directamente a páginas dedicadas (por ejemplo `pages/silla.html`, `pages/map.html`) en lugar de insertar contenido dentro de `index.html`.
- assets/css/common.css — Estilos compartidos (topbar, botones, cards, modo oscuro)
- assets/js/common.js — Helpers compartidos (renderTopbar, startClock, loadPageContent). Detecta si la página ya tiene un `.top-bar` y enlaza comportamientos.
- assets/js/app.js — Inicializador: gestiona botones de selección y carga de páginas.
- pages/*.html — Plantillas de cada flujo (silla / bicicleta / a_pie)
	- pages/register.html — Formulario de registro (persistencia demo en localStorage).
	- pages/select_mode.html — Página para elegir modo (silla / bicicleta / a pie) en el primer inicio o al cambiar modo.
	- pages/map.html — Mapa (mock) que muestra el modo actual y preferencias guardadas.

Cómo probar localmente (desde el directorio `Julio`):

1. Abrir un servidor estático (recomendado):

```bash
cd /home/yuutamk/Documentos/Hackaton/Movilidad2025/docs/AppWeb/Proyectos/Julio
python3 -m http.server 8080
```

2. Abrir en el navegador: http://localhost:8080/app_unificada/index.html

Flujo básico implementado (demo, sin backend):

- Inicio de sesión desde `index.html`. Si el usuario no existe se ofrece registrarse.
- Registro crea un usuario en localStorage (`users||demo`) y marca `currentUser||demo`.
- Al registrarse o en el primer inicio de sesión, el usuario selecciona un modo o es dirigido a `select_mode`.
- En `select_mode` el flujo redirige a la pantalla de preferencias del modo (por ejemplo `silla`, `bicicleta` o `a_pie`) — allí se guardan preferencias y se dirige a `map`.
- En `map` el usuario puede ver su modo y preferencias y usar "Cambiar modo" para volver a `select_mode` y actualizar el mapa.

Estado del modo oscuro:
- El modo oscuro se controla con el botón del topbar y se persiste en `localStorage` bajo la clave `ui_dark`.

Notas de seguridad y producción:
- Actualmente la app usa localStorage para demo — no es segura ni adecuada para producción. Debes integrar un backend y almacenamiento seguro para manejar usuarios y sesiones.


Notas:
- Para compatibilidad hacia atrás las carpetas originales (`1. Silla_de_Ruedas`, `2. Bicicleta`, `3. A_Pie`) se mantienen pero ahora referencian el CSS/JS unificado.
- Siguientes mejoras recomendadas: renombrar archivos sin espacios y numeración, mover todos assets de páginas individuales dentro de `app_unificada/assets` y eliminar duplicados.
