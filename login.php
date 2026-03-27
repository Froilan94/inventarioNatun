<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $errors[] = "Ingrese usuario y contraseña.";
    } else {

        $sql = "SELECT u.id_usuario, u.nombre_completo,
                       u.password_hash, r.id_rol, r.nombre_rol
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id_rol
                WHERE u.nombre_usuario = ?
                LIMIT 1";

        if ($stmt = $mysqli->prepare($sql)) {

            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password_hash'])) {

                // 🔐 Protección contra fijación de sesión
                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id_usuario'];
                $_SESSION['user_name'] = $user['nombre_completo'];
                $_SESSION['role_id']   = $user['id_rol'];
                $_SESSION['role_name'] = $user['nombre_rol'];

                $role = $user['nombre_rol'];

                // 🏗 Mapa profesional de redirecciones
                $redirectMap = [
                    'admin_super'   => 'index.php',

                    // Medicamentos
                    'operadormed'   => 'indexmedicamentos.php',
                    'supervisormed' => 'indexmedicamentos.php',

                    // Materia Prima
                    'operadormp'    => 'materiaprima/dashboard.php',
                    'supervisormp'  => 'materiaprima/dashboard.php',

                    // Artículos
                    'operadorart'   => 'articulos/dashboard.php',
                    'supervisorart' => 'articulos/dashboard.php',
                ];

                if (isset($redirectMap[$role])) {
                    header('Location: ' . $redirectMap[$role]);
                    exit;
                }

                // Rol no definido
                session_destroy();
                $errors[] = "Rol no autorizado.";

            } else {
                $errors[] = "Usuario o contraseña incorrectos.";
            }

            $stmt->close();

        } else {
            $errors[] = "Error interno del sistema.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <!-- Background Slider -->
    <div class="login-background">
        <div class="bg-slide active" style="background-image: url('img/login/bg-1.jpg');"></div>
        <div class="bg-slide" style="background-image: url('img/login/bg-2.jpg');"></div>
        <div class="bg-slide" style="background-image: url('img/login/bg-3.jpg');"></div>
        <div class="bg-slide" style="background-image: url('img/login/bg-4.jpg');"></div>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="col-12 col-sm-10 col-md-6 col-lg-5 col-xl-4">
            <div class="card login-card border-0">
                <div class="card-body">
                    <!-- Logo (opcional) -->
                    <!-- <img src="assets/img/logo.png" alt="Logo" class="login-logo"> -->
                    
                    <h1 class="login-title">Bienvenido</h1>
                    <p class="login-subtitle">Ingresa tus credenciales para continuar</p>

                    <?php if ($errors): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php foreach ($errors as $e): ?>
                                <div><i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($e); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="usuario" class="form-control" 
                                   placeholder="Ingresa tu usuario" 
                                   value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>" 
                                   required autofocus>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Ingresa tu contraseña" 
                                   required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login">
                                Iniciar Sesión
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">¿Olvidaste tu contraseña? 
                            <a href="#" class="text-decoration-none">Recuperar</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/login.js"></script>
</body>
</html>
