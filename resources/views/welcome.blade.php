<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title','')Production Report</title>
  <!-- Bootstrap CSS local -->
  <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
  <style>
    html {
      font-family: 'Inter', sans-serif;
    }

    body {
      padding-top: .5rem;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .hero-container {
      max-width: 600px;
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(6px);
      border-radius: 1rem;
      padding: 3rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-primary-lg {
      font-size: 1.25rem;
      padding: 0.75rem 1.5rem;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- HEADER -->
  <header class="navbar navbar-expand-md navbar-light bg-white fixed-top border-bottom shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
      <a href="/" class="navbar-brand fw-bold text-primary">
        YOFC Mexico
      </a>

      @if(Request::routeIs('excel.index'))
      <a href="{{ url('/') }}" class="btn btn-outline-primary btn-sm">
        Back to Home
      </a>
      @endif
    </div>
  </header>

  <!-- MAIN CONTENT -->
  <main class="flex-grow-1 px-3 px-md-5 mt-5 d-flex justify-content-center align-items-center">

    @if(Request::routeIs('excel.index'))
    {{-- Aquí se muestra la sección Excel --}}
    <div class="w-100">
      @yield('datos')
    </div>
    @else
    {{-- Hero principal solo para Home --}}
    <div class="hero-container text-center">
      <h1 class="mb-4 fw-bold">Welcome to the Production Management System</h1>
      <p class="mb-4 text-secondary">
        Streamline your production planning, monitor multiple sheets, and manage your data efficiently.
      </p>
      <a href="{{ route('excel.index') }}" class="btn btn-primary btn-lg btn-primary-lg">
        📊 Go to Production Plan
      </a>
    </div>
    @endif

  </main>

  <!-- FOOTER -->
  <footer class="text-center text-muted small py-3 border-top mt-auto bg-white">
    <div class="container">
      © {{ date('Y') }} <span class="fw-semibold text-primary">YOFC Mexico</span>. All rights reserved.
    </div>
  </footer>

  <!-- Bootstrap Bundle JS (with Popper) -->
  <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>