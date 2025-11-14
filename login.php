<?php
// login.php
session_start();
require 'config/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $errors[] = "Ingrese usuario y contraseña.";
    } else {
        // Buscar usuario y rol
        $sql = "SELECT u.id_usuario, u.nombre_completo, u.nombre_usuario, u.password_hash, r.id_rol, r.nombre_rol
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id_rol
                WHERE u.nombre_usuario = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                // verificar password
                if (password_verify($password, $row['password_hash'])) {
                    // guardar sesión
                    $_SESSION['user_id'] = $row['id_usuario'];
                    $_SESSION['user_name'] = $row['nombre_completo'];
                    $_SESSION['role_id'] = $row['id_rol'];
                    $_SESSION['role_name'] = $row['nombre_rol'];

                    // redirección por rol
                    $role = $row['nombre_rol'];
                    if ($role === 'admin_super') {
                        header('Location: index.php');
                        exit;
                    }
                    if (strpos($role, 'med') !== false) {
                        header('Location: mod_med/dashboard_med.php');
                        exit;
                    }
                    if (strpos($role, 'mp') !== false) {
                        header('Location: mod_mp/dashboard_mp.php');
                        exit;
                    }
                    if ($role === 'consultas_global') {
                        header('Location: consultas_global/dashboard_global.php');
                        exit;
                    }

                    // fallback
                    header('Location: index.php');
                    exit;
                } else {
                    $errors[] = "Usuario o contraseña incorrecta.";
                }
            } else {
                $errors[] = "Usuario no encontrado.";
            }
            $stmt->close();
        } else {
            $errors[] = "Error en la consulta.";
        }
    }
}
include 'includes/header.php';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow">
        <div class="card-body">
          <h4 class="card-title mb-3">Iniciar sesión</h4>

          <?php if ($errors): ?>
            <div class="alert alert-danger">
              <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
            </div>
          <?php endif; ?>

          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label">Usuario</label>
              <input type="text" name="usuario" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="d-grid">
              <button class="btn btn-primary">Ingresar</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
