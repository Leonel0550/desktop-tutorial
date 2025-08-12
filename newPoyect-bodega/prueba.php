<?php
session_start();

if (isset($_SESSION['user'])) {
    echo "Hola, " . $_SESSION['user']['displayName'];
    echo "<br><a href='logout.php'>Cerrar sesión</a>";
} else {
    echo "<a href='login.php'>Iniciar sesión</a>";
}
?>
