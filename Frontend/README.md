# Frontend (Bootstrap)

Archivos principales:

- `index.html` — página principal con navbar, carousel, galería, sección de clientes, contacto y footer.
- `css/styles.css` — estilos mínimos personalizados.
- `js/main.js` — comportamiento: modal de galería, validación de formulario y año dinámico.

Cómo usar:

1. Abrir `Frontend/index.html` en un navegador (localmente). No requiere servidor.
2. Reemplaza las imágenes de ejemplo (usamos `picsum.photos`) por tus imágenes en la galería y logos.
3. Para enviar el formulario a un backend, cambia el `submit` del formulario en `js/main.js` para hacer `fetch()` a tu endpoint.

Notas:

- Este es un scaffold simple con Bootstrap 5 por CDN. Para producción, considera descargar y servir los assets apropiadamente.
- Si quieres que implemente subida de imágenes o integración con un backend, dime y lo añado.
