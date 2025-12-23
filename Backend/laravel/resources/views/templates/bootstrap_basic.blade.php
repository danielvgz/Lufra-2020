<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sitio público - Plantilla Bootstrap Básica</title>
    <!-- Bootstrap 5 CDN (core framework) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tema local -->
    <link href="{{ asset('templates/bootstrap_basic/css/style.css') }}" rel="stylesheet">
    <style>
        body { padding-top: 56px; }
        .hero { background: linear-gradient(90deg,#0069d9 0%, #00b4ff 100%); color: white; }
        .feature { padding: 2rem 0; }
        footer { padding: 2rem 0; background:#f8f9fa; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Mi Sitio Público</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Características</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contacto</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero text-center py-5">
        <div class="container">
            <h1 class="display-5">Plantilla Bootstrap Básica</h1>
            <p class="lead">Ejemplo público y ligero que no muestra la interfaz de administración.</p>
            <p><a class="btn btn-light" href="#features">Ver características</a></p>
        </div>
    </header>

    <main class="container my-5">
        <section id="features" class="feature">
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <h4>Diseño responsivo</h4>
                    <p>Basado en Bootstrap, se adapta a móviles y escritorio.</p>
                </div>
                <div class="col-md-4 text-center">
                    <h4>Contenido público</h4>
                    <p>Esta vista es independiente del panel de administración y está pensada para visitantes.</p>
                </div>
                <div class="col-md-4 text-center">
                    <h4>Fácil de personalizar</h4>
                    <p>Coloca archivos CSS/JS en <code>public/templates/bootstrap_basic</code> si necesitas assets locales.</p>
                </div>
            </div>
        </section>

        <hr>

        <section id="contact" class="my-4">
            <h3>Contacto</h3>
            <p>Información de contacto pública o enlaces.</p>
        </section>
    </main>

    <footer class="text-center">
        <div class="container">
            <small>&copy; {{ date('Y') }} Mi Sitio Público</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('templates/bootstrap_basic/js/main.js') }}"></script>
</body>
</html>
