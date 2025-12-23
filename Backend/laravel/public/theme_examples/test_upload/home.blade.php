<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Example Upload Theme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('themes/test_upload/css/style.css') }}" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="#">Upload Theme</a>
  </div>
</nav>

<main class="container py-5">
  <h1>Theme de prueba para subir</h1>
  <p>Este tema sirve para probar la subida e instalación desde el ZIP.</p>
</main>

<footer class="text-center py-4">&copy; {{ date('Y') }} Upload Theme</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('themes/test_upload/js/main.js') }}"></script>
</body>
</html>
