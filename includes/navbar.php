<?php
// includes/navbar.php
if (session_status() == PHP_SESSION_NONE) session_start();

$rol_name = $_SESSION['role_name'] ?? null;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="/inventario/index.php">Inventarios</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if ($rol_name === 'admin_super'): ?>
          <li class="nav-item"><a class="nav-link" href="/inventario/mod_mp/dashboard_mp.php">MP</a></li>
          <li class="nav-item"><a class="nav-link" href="/inventario/mod_med/dashboard_med.php">MED</a></li>
          <li class="nav-item"><a class="nav-link" href="/inventario/consultas_global/dashboard_global.php">Consultas</a></li>
        <?php else: ?>
          <?php
            // accesos por rol
            if (strpos($rol_name, 'mp') !== false) {
              echo '<li class="nav-item"><a class="nav-link" href="/inventario/mod_mp/dashboard_mp.php">MP</a></li>';
            }
            if (strpos($rol_name, 'med') !== false) {
              echo '<li class="nav-item"><a class="nav-link" href="/inventario/mod_med/dashboard_med.php">MED</a></li>';
            }
            if ($rol_name === 'consultas_global') {
              echo '<li class="nav-item"><a class="nav-link" href="/inventario/consultas_global/dashboard_global.php">Consultas</a></li>';
            }
          ?>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_name'])): ?>
          <li class="nav-item"><span class="nav-link disabled">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span></li>
          <li class="nav-item"><a class="nav-link" href="/inventario/logout.php">Cerrar sesión</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/inventario/login.php">Ingresar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
