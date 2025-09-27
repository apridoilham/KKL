<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

  <!-- Sidebar - Brand -->
  <a href="/" class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
    <div class="sidebar-brand-icon rotate-n-15">
      <i class="fas fa-box"></i>
    </div>
    <div class="sidebar-brand-text mx-3">Inventory</div>
  </a>

  <!-- Divider -->
  <hr class="sidebar-divider my-0">

  <!-- Nav Item - Dashboard -->
  <li class="nav-item {{$data['urlPath'] == 'home' ? 'active' : ''}}">
    <a class="nav-link" href="/">
      <i class="fas fa-fw fa-home"></i>
      <span>Home</span></a>
  </li>

  <!-- Divider -->
  <hr class="sidebar-divider">

  <!-- Heading -->
  <div class="sidebar-heading">
    Manage
  </div>

  <li class="nav-item {{$data['urlPath'] == 'item' ? 'active' : ''}}">
    <a class="nav-link" href="/item">
      <i class="fas fa-fw fa-box"></i>
      <span>Item</span></a>
  </li>
  <li class="nav-item {{$data['urlPath'] == 'transaction' ? 'active' : ''}}">
    <a class="nav-link" href="/transaction">
      <i class="fas fa-fw fa-cog"></i>
      <span>Transaction</span></a>
  </li>

  <!-- Divider -->
  <hr class="sidebar-divider d-none d-md-block">
  <div class="sidebar-heading">
    Report
  </div>
  <li class="nav-item {{$data['urlPath'] == 'report' ? 'active' : ''}}">
    <a class="nav-link" href="/report">
      <i class="fas fa-fw fa-book"></i>
      <span>Report</span></a>
  </li>
  <!-- Divider -->
  <hr class="sidebar-divider d-none d-md-block">

  <!-- Sidebar Toggler (Sidebar) -->
  <div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
  </div>

</ul>
<!-- End of Sidebar -->