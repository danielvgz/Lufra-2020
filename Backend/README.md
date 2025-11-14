# Backend (Laravel) — Setup

Este directorio contiene (o contendrá) la aplicación Laravel. Si aún no has creado el proyecto, usa el script `setup-backend.sh` para generar el scaffold, instalar dependencias y preparar git.

Pasos rápidos (local):

1. Abrir terminal y situarse en la raíz del repo:

```bash
cd /ruta/al/proyecto/Lufra-2020/Backend
```

2. Hacer el script ejecutable y ejecutarlo:

```bash
chmod +x setup-backend.sh
./setup-backend.sh
```

3. Edita `.env` con tus credenciales (si no lo hizo el script):

```bash
# cp .env.example .env
# editar .env
```

4. Ejecuta migraciones y crea el storage link:

```bash
php artisan migrate
php artisan storage:link
```

5. Crear el repositorio remoto (opcional):

Con `gh` (GitHub CLI) autenticado:

```bash
gh repo create <owner>/<repo-name> --public --source=. --remote=origin
git push -u origin main
```

Notas:
- El script asume que tienes `php`, `composer` (y opcionalmente `npm`) instalados.
- No subas tu `.env` al repositorio. Usa `.env.example` para compartir configuraciones no sensibles.

Si quieres, puedo crear el repo remoto por ti (si `gh` está autenticado aquí) y empujar el primer commit. Dime si lo hago.