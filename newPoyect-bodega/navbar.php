<?php
session_start(); // Iniciar la sesión
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$suma_piezas_query = $con->prepare("
SELECT SUM(PIEZAS) as total_piezas
FROM t_recibo_bodega_detalles
");
$suma_piezas_query->execute();
$total_piezas = $suma_piezas_query->fetch(PDO::FETCH_ASSOC)['total_piezas'];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página principal</title>
    <link rel="stylesheet" href="navbar.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <nav class="sidebar">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="assets/img/logo-itse.png" alt="">
                </span>

                <div class="text header-text">
                    <span class="name">Sitio Web de</span>
                    <span class="profession">Manejo de Bodega</span>
                </div>
            </div>

            <i class='bx bx-chevron-right toggle'></i>
        </header>
        <div class="menu-bar">
            <div class="menu">
                <li class="nav-links">
                    <div class="nav-user">
                        <i class='bx bxs-user icon'></i>
                        <span class="text nav-text">
                            <?php
                            if (isset($_SESSION['user_email'])) {
                                echo htmlspecialchars($_SESSION['user_email']);
                            } else {
                                echo "Usuario";
                            }
                            ?>
                        </span>
                    </div>
                </li>

                <li class="search-box">
                    <i class='bx bx-search icon'></i>
                    <input type="text" placeholder="Search.....">
                </li>
                <ul class="menu-links">
                    <li class="nav-links">
                        <a href="inicio.php" class="nav-inicio">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Página de Inicio</span>
                        </a>
                    </li>
                    <li class="nav-links">
                        <a href="categoria.php" class="nav-categoria">
                            <i class='bx bx-list-ul icon'></i>
                            <span class="text nav-text">Categorías</span>
                        </a>
                    </li>
                    <li class="nav-links">
                        <a href="producto.php" class="nav-producto">
                            <i class='bx bxl-product-hunt icon'></i>
                            <span class="text nav-text">Productos</span>
                        </a>
                    </li>
                    <li class="nav-links">
                        <a href="index_recibo.php" class="nav-bienes">
                            <i class='bx bx-package icon'></i>
                            <span class="text nav-text">Bienes
                                <span class="total_piezas">(<?php echo number_format($total_piezas); ?>)</span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-links">
                        <a href="index_recibo.php" class="nav-nuevo">
                            <i class='bx bx-plus-circle icon'></i>
                            <span class="text nav-text">Nuevo Entrante</span>
                        </a>
                    </li>
                    <li class="nav-links">
                        <a href="saliente.php" class="nav-saliente">
                            <i class='bx bx-minus-circle icon'></i>
                            <span class="text nav-text">Nuevo Saliente</span>
                        </a>
                    </li>
                    <li class="nav-links">
                        <a href="index.php" class="nav-proveedor">
                            <i class='bx bxs-truck icon'></i>
                            <span class="text nav-text">Proveedor</span>
                        </a>
                    </li>
                    <li class="nav-links">
                        <a href="documento.php" class="nav-documento">
                            <i class='bx bxs-report icon'></i>
                            <span class="text nav-text">Documentos</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bottom-content">
                <li class="nav-links">
                    <a href="logout.php" class="nav-login">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Cerrar Sesión</span>
                    </a>
                </li>

                <li class="mode">
                    <div class="moon-sun">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Modo Oscuro</span>

                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>
            </div>
        </div>
    </nav>

    <script src="js/script.js"></script>

</body>

</html>