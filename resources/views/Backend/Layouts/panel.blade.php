<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title> @yield('title') | Gerador de boleto</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/admin-lte-assets/css/adminlte.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <link rel="stylesheet" href="/admin-lte-assets/css/select2-bootstrap4.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/admin-lte-assets/css/select2.min.css" rel="stylesheet">
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  @yield('css')  
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed {{env('DARK_MODE') ? 'dark-mode' : ''}}">

<div id="app" class="wrapper">

  <!-- Preloader -->
  <div class="flex-column justify-content-center align-items-center">
    <img class="animation__wobble" src="/admin-lte-assets/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div>

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand {{env('DARK_MODE') ? 'navbar-dark' : 'navbar-light'}}">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{route('backend.index')}}" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{route('backend.customer.create')}}" class="nav-link">Adicionar Cliente</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{route('backend.service.create')}}" class="nav-link">Adicionar Serviço</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{route('backend.fatura.pix.create')}}" class="nav-link">Gerar Pix</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{route('backend.fatura.boleto.create')}}" class="nav-link">Gerar boleto</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    @if(!empty(env('NOTIFISH_UUID')))
      @if(request()->routeIs('backend.index', 'backend.fatura.boleto.index', 'backend.fatura.pix.index'))
        <ul class="navbar-nav ml-auto">
          <check-status-whats></check-status-whats>
        </ul>
      @endif
    @endif
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar elevation-4 {{env('DARK_MODE') ? 'sidebar-dark-primary' : 'sidebar-light-primary'}}">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
      <img src="/admin-lte-assets/img/lnsistemas.png" alt="Boleto Inter Logo" class="brand-image img-cirecle elevation-3" style="opacity: .8; max-height:40px!important;">
      <span class="brand-text font-weight-light">{{env('APP_NAME')}}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://i.imgur.com/M7eDYTH.jpg" class="img-circle elevation-2" alt="{{Auth()->user()->name}}" style="width:4.5rem!important;">
        </div>
        <div class="info mt-3">
          <a href="#" class="d-block">{{Auth()->user()->name}}</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          
          <li class="nav-item">
            <a href="{{route('backend.index')}}" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Minha conta
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('backend.customer.index')}}" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>
                  Clientes
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('backend.service.index')}}" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                  Serviços
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
             <i class="nav-icon fas fa-receipt"></i>
              <p>
                Faturas
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" style="display: none;">
              <li class="nav-item">
                <a href="{{route('backend.fatura.boleto.index')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Boletos</p>
                </a>
              </li>
              <li class="nav-item">
              <a href="{{route('backend.fatura.pix.index')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pix</p>
                </a>
              </li>
            </ul>
          </li>


          <li class="nav-item">
            <a href="#" class="nav-link">
             <i class="nav-icon fas fa-exchange-alt"></i>
              <p>
                Webhook
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" style="display: none;">
              <li class="nav-item">
                <a href="{{route('backend.fatura.boleto.get.webhook')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Boleto</p>
                </a>
              </li>
              <li class="nav-item">
              <a href="{{route('backend.fatura.pix.get.webhook')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pix</p>
                </a>
              </li>
            </ul>
          </li>
          @if(!empty(env('NFE_BASE_URL')) && !empty(env('CIDADE_ID_NFE')) && !empty(env('AUTHORIZATION_NFE')) && !empty(env('CNPJ_NF_PRESTADOR')))
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fab fa-nav-icon fas fa-skull-crossbones"></i>
              <p>
                Notas Fiscais
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" style="display: none;">
              <li class="nav-item">
                <a href="{{route('backend.nfe.index')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Lista de notas</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('backend.nfe.indexTypeNote')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listar tipo de nota</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('backend.nfe.create')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Cadastrar tipo de nota</p>
                </a>
              </li>
            </ul>
          </li>
          @endif
          <li class="nav-item">
            <a href="{{route('backend.get-month-report', ['ano' => date('Y'), 'mes' => date('m')])}}" class="nav-link">
              <i class="nav-icon fas fa-file-pdf"></i>
              <p>
                Extrato mês PDF
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fab fa-whatsapp"></i>
              <p>
                WhatsApp
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" style="display: none;">
              <li class="nav-item">
                <a href="{{route('backend.whatsapp.index')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Login</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('backend.whatsapp.restart')}}" onclick="return confirm('Tem certeza que deseja reiniciar o WhatsApp?')" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Reiniciar WhatsApp</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('backend.whatsapp.logout')}}" onclick="return confirm('Tem certeza que deseja encerrar a sessão no WhatsApp e no site?')" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Encerrar sessão</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="{{route('backend.feriado.index')}}" class="nav-link">
              <i class="nav-icon fas fa-lock"></i>
              <p>
                Feriados
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('backend.cache-clear')}}" class="nav-link" onclick="return confirm('Tem certeza que deseja limpar o cache de todo o sistema?')">
              <i class="nav-icon fas fa-eraser"></i>
              <p>
                Limpar cache
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="fas fa-sign-out-alt"></i>
              <p>
                Sair
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                  {{ csrf_field() }}
                </form>
              </p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Main content -->
    <section class="content mt-n3">
      <div class="container-fluid">
        @yield('content')
      </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer" style="position: inherit;">
    <strong>Copyright &copy; 2014-{{date('Y')}} <a href="https://adminlte.io" target="_blanl" rel="noopener">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.2.0
    </div>
  </footer>
</div>
<!-- ./wrapper -->
<script src="{{ mix('/js/app.js') }}?v=3"></script>

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- overlayScrollbars -->
<script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="/admin-lte-assets/js/adminlte.js"></script>

<!-- PAGE /PLUGINS -->
<!-- jQuery Mapael (comentado - não usado) -->
<!-- <script src="/plugins/jquery-mousewheel/jquery.mousewheel.js"></script> -->
<!-- <script src="/plugins/raphael/raphael.min.js"></script> -->
<!-- <script src="/plugins/jquery-mapael/jquery.mapael.min.js"></script> -->
<!-- <script src="/plugins/jquery-mapael/maps/usa_states.min.js"></script> -->
<!-- ChartJS (comentado - não usado) -->
<!-- <script src="/plugins/chart.js/Chart.min.js"></script> -->
<!-- dashboard2.js (comentado - não usado, causava erro) -->
<!-- <script src="/admin-lte-assets/js/pages/dashboard2.js"></script> -->
<script src="/admin-lte-assets/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script type="text/javascript">

$(function () {
    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
  });

      toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
      }
      @if(Session::has('message'))
          var type = "{{ Session::get('alert-type', 'info') }}";
          switch(type){
              case 'info':
                  toastr.info("{{ Session::get('message') }}");
                  break;
              case 'warning':
                  toastr.warning("{{ Session::get('message') }}");
                  break;
              case 'success':
                  toastr.success("{{ Session::get('message') }}");
                  break;
              case 'error':
                  toastr.error("{{ Session::get('message') }}");
                  break;
          }
      @endif
    </script>
@yield('js')
</body>
</html>
