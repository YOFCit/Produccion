<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>
    @hasSection('title')
    @yield('title')
    @else
    Reporte de Producción
    @endif
  </title>

  <!-- Bootstrap CSS local -->
  <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />

  <style>
    html {
      font-family: 'Inter', sans-serif;
    }

    /* Estilo glass para contenido */
    .glass {
      background-color: rgba(255, 255, 255, 0.6);
      backdrop-filter: blur(8px);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(200, 200, 200, 0.4);
      border-radius: 1rem;
    }

    /* Mejoras para móvil y ajustes personalizados */
    @media (max-width: 768px) {
      body {
        padding-top: 68px;
        /* espacio para header fijo */
      }
    }
  </style>

  @livewireStyles
</head>

<body class="bg-light d-flex flex-column min-vh-100">

  <!-- NAVBAR MEJORADA Bootstrap 5 -->
  <header
    class="navbar navbar-expand-md navbar-light bg-white fixed-top border-bottom shadow-sm">
    <div class="container">
      <a href="/" class="navbar-brand d-flex align-items-center gap-2 text-primary fw-bold">
        <svg class="bi" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" role="img" aria-label="Logo">
          <path
            d="M9.75 9V5.25m0 13.5V15M14.25 9V5.25m0 13.5V15M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="d-none d-sm-inline">Plan de Producción</span>
        <span class="d-sm-none">YOFC</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"
        aria-controls="navbarMenu" aria-expanded="false" aria-label="Abrir menú móvil">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarMenu">
        <ul class="navbar-nav ms-auto mb-2 mb-md-0">
          <li class="nav-item">
            <a href="{{ route('sh') }}"
              class="nav-link {{ request()->routeIs('sh') ? 'active fw-semibold' : '' }}">Sheating</a>
          </li>
          <li class="nav-item">
            <a href="{{ route('sz') }}"
              class="nav-link {{ request()->routeIs('sz') ? 'active fw-semibold' : '' }}">Stranding</a>
          </li>
          <li class="nav-item">
            <a href="{{ route('sc') }}"
              class="nav-link {{ request()->routeIs('sc') ? 'active fw-semibold' : '' }}">S. Coating</a>
          </li>
        </ul>
      </div>
    </div>
  </header>

  <!-- CONTENIDO -->
  <main class="flex-grow-1 px-3 px-md-5 py-4 pt-5 mt-5">
    <div class="glass p-4 shadow-lg">
      @yield('datos')
    </div>
  </main>

  <!-- FOOTER -->
  <footer class="text-center text-muted small py-3 border-top mt-auto bg-white">
    <div class="container">
      © {{ date('Y') }} <span class="fw-semibold text-primary">YOFC México</span>. Todos los derechos reservados.
    </div>
  </footer>

  <!-- Bootstrap Bundle JS (Popper incluido) -->
  <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>

  @livewireScripts
</body>

</html>