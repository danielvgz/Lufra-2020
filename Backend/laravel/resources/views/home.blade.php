<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Frontend - Bootstrap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
  </head>
  <body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
      <div class="container">
        <a class="navbar-brand" href="#">
           @if (config('app.name')) 
              {{ config('app.name') }}
           @else
            <p> Mi empresa </p>
           @endif
          
          </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="#navMenu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link active" href="#hero">Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="#about">Nosotros</a></li>
            <li class="nav-item"><a class="nav-link" href="#gallery">Galería</a></li>
            <li class="nav-item"><a class="nav-link" href="#clients">Clientes</a></li>
            <li class="nav-item"><a class="nav-link" href="#contact">Contacto</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- HERO / CAROUSEL -->
    <section id="hero" class="py-4">
      <div class="container">
        <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-indicators">
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
          </div>
          <div class="carousel-inner rounded shadow-sm">
            <div class="carousel-item active">
              <img src="https://picsum.photos/1200/500?random=1" class="d-block w-100" alt="Slide 1">
              <div class="carousel-caption d-none d-md-block">
                <h5>Soluciones creativas</h5>
                <p>Diseño y tecnología para tu negocio.</p>
              </div>
            </div>
            <div class="carousel-item">
              <img src="https://picsum.photos/1200/500?random=2" class="d-block w-100" alt="Slide 2">
              <div class="carousel-caption d-none d-md-block">
                <h5>Proyectos a medida</h5>
                <p>Calidad, rapidez y soporte continuo.</p>
              </div>
            </div>
            <div class="carousel-item">
              <img src="https://picsum.photos/1200/500?random=3" class="d-block w-100" alt="Slide 3">
              <div class="carousel-caption d-none d-md-block">
                <h5>Alcanza tus metas</h5>
                <p>Transformamos ideas en resultados.</p>
              </div>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
          </button>
        </div>
      </div>
    </section>

    <!-- ABOUT / WHO WE ARE -->
    <section id="about" class="py-5">
      <div class="container">
        <div class="row align-items-center gy-4">
         <div class="col-md-12">
            <div class="col-md-6">
            <h2 class="h3">Quiénes somos</h2>
            <p class="text-muted">Somos una empresa dedicada a ofrecer soluciones tecnológicas y de diseño enfocadas en impulsar negocios. Contamos con un equipo multidisciplinario con experiencia en desarrollo, UX/UI y estrategia digital.</p>
             </div>
          </div>
           <div class="col-md-6">
                <h4 class="h6">Misión</h4>
                <p class="small text-muted">Proporcionar productos y servicios digitales que ayuden a nuestros clientes a crecer de forma sostenible mediante innovación y soporte cercano.</p>
              </div>
           <div class="col-md-6">
                <h4 class="h6">Visión</h4>
                <p class="small text-muted">Ser referentes en soluciones digitales de alto impacto en la región, reconocidos por la calidad y el compromiso con el cliente.</p>
              </div>
          <div class="col-lg-12">
            <h3 class="h5">Algunos de nuestros productos</h3>
            <div class="row g-3 mt-2">
              <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm">
                  <img src="https://picsum.photos/600/300?random=21" class="card-img-top" alt="Producto 1">
                  <div class="card-body">
                    <h5 class="card-title">Producto A</h5>
                    <p class="card-text small text-muted">Plataforma web a medida para gestión de clientes y procesos.</p>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm">
                  <img src="https://picsum.photos/600/300?random=22" class="card-img-top" alt="Producto 2">
                  <div class="card-body">
                    <h5 class="card-title">Producto B</h5>
                    <p class="card-text small text-muted">App móvil para interacción de usuarios y seguimiento en tiempo real.</p>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm">
                  <img src="https://picsum.photos/600/300?random=23" class="card-img-top" alt="Producto 3">
                  <div class="card-body">
                    <h5 class="card-title">Producto C</h5>
                    <p class="card-text small text-muted">Servicio de consultoría para transformación digital y optimización.</p>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm">
                  <img src="https://picsum.photos/600/300?random=24" class="card-img-top" alt="Producto 4">
                  <div class="card-body">
                    <h5 class="card-title">Producto D</h5>
                    <p class="card-text small text-muted">Soluciones de e-commerce y tiendas online optimizadas para conversión.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- GALLERY -->
    <section id="gallery" class="py-5 bg-light">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="h3">Galería</h2>
          <p class="mb-0 text-muted">Haz click en las imágenes para verlas ampliadas.</p>
        </div>
        <div class="row g-3 gallery-grid">
          <!-- Thumbnails -->
          <!-- We'll use picsum placeholders; replace with tus imágenes -->
          <div class="col-6 col-sm-4 col-md-3">
            <a href="#" class="gallery-item" data-bs-toggle="modal" data-src="https://picsum.photos/1200/800?image=1015"><img src="https://picsum.photos/400/300?image=1015" class="img-fluid rounded" alt="Thumb 1"></a>
          </div>
          <div class="col-6 col-sm-4 col-md-3">
            <a href="#" class="gallery-item" data-bs-toggle="modal" data-src="https://picsum.photos/1200/800?image=1025"><img src="https://picsum.photos/400/300?image=1025" class="img-fluid rounded" alt="Thumb 2"></a>
          </div>
          <div class="col-6 col-sm-4 col-md-3">
            <a href="#" class="gallery-item" data-bs-toggle="modal" data-src="https://picsum.photos/1200/800?image=1035"><img src="https://picsum.photos/400/300?image=1035" class="img-fluid rounded" alt="Thumb 3"></a>
          </div>
          <div class="col-6 col-sm-4 col-md-3">
            <a href="#" class="gallery-item" data-bs-toggle="modal" data-src="https://picsum.photos/1200/800?image=1045"><img src="https://picsum.photos/400/300?image=1045" class="img-fluid rounded" alt="Thumb 4"></a>
          </div>
          <div class="col-6 col-sm-4 col-md-3">
            <a href="#" class="gallery-item" data-bs-toggle="modal" data-src="https://picsum.photos/1200/800?image=1055"><img src="https://picsum.photos/400/300?image=1055" class="img-fluid rounded" alt="Thumb 5"></a>
          </div>
          <div class="col-6 col-sm-4 col-md-3">
            <a href="#" class="gallery-item" data-bs-toggle="modal" data-src="https://picsum.photos/1200/800?image=1065"><img src="https://picsum.photos/400/300?image=1065" class="img-fluid rounded" alt="Thumb 6"></a>
          </div>
        </div>
      </div>

      <!-- Modal para ver imagen ampliada -->
      <div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-body p-0">
              <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              <img src="" id="galleryModalImg" class="w-100" alt="Imagen ampliada">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CLIENTS / LOGOS -->
    <section id="clients" class="py-5">
      <div class="container">
        <h2 class="h3 mb-4">Clientes</h2>
        <div class="row g-3 align-items-center">
          <div class="col-6 col-md-3 text-center p-3"><img src="https://picsum.photos/200/80?random=11" class="img-fluid" alt="Logo 1"></div>
          <div class="col-6 col-md-3 text-center p-3"><img src="https://picsum.photos/200/80?random=12" class="img-fluid" alt="Logo 2"></div>
          <div class="col-6 col-md-3 text-center p-3"><img src="https://picsum.photos/200/80?random=13" class="img-fluid" alt="Logo 3"></div>
          <div class="col-6 col-md-3 text-center p-3"><img src="https://picsum.photos/200/80?random=14" class="img-fluid" alt="Logo 4"></div>
        </div>
      </div>
    </section>

    <!-- CONTACT -->
    <section id="contact" class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <h2 class="h3">Contáctanos</h2>
            <p class="text-muted">Rellena el formulario y nos pondremos en contacto contigo.</p>
            <form id="contactForm" novalidate>
              <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="name" required>
                <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" required>
                <div class="invalid-feedback">Por favor ingresa un email válido.</div>
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Mensaje</label>
                <textarea id="message" class="form-control" rows="5" required></textarea>
                <div class="invalid-feedback">Escribe tu mensaje.</div>
              </div>
              <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
          </div>
          <div class="col-md-6">
            <h3 class="h5">Información</h3>
            <p class="mb-1"><strong>Teléfono:</strong> +34 600 000 000</p>
            <p class="mb-1"><strong>Email:</strong> contacto@miempresa.com</p>
            <p class="mb-0"><strong>Dirección:</strong> Calle Ejemplo 123, Ciudad</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-4 bg-dark text-light">
      <div class="container d-flex justify-content-between align-items-center">
        <small>&copy; <span id="year"></span> Mi Empresa. Todos los derechos reservados.</small>
        <div>
          <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
          <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
          <a href="#" class="text-light"><i class="bi bi-instagram"></i></a>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
  </body>
</html>
