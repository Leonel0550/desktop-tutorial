<?php
session_start(); // Iniciar la sesión
// Conexión a la base de datos y consultas para obtener bodegas y proveedores
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

// Obtener bodegas
$comandoBodegas = $con->prepare("SELECT ID_BODEGA, NOMBRE FROM t_stp_bodega");
$comandoBodegas->execute();
$bodegas = $comandoBodegas->fetchAll(PDO::FETCH_ASSOC);

// Obtener proveedores
$comandoProveedores = $con->prepare("SELECT ID_PROVEEDOR, NOMBRE FROM t_proveedores");
$comandoProveedores->execute();
$proveedores = $comandoProveedores->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-form">
    <form action="guarda_recibo.php" method="POST" autocomplete="off">
        <h2>Nuevo Recibo</h2>
        <div class="content">
            <div class="input-box">
                <label for="fecha_entrada"><i class="fa-solid fa-calendar"></i> Fecha y Hora de salida:</label>
                <?php
                // Establecer la zona horaria a Panamá
                date_default_timezone_set('America/Panama');
                // Obtener la fecha y hora actual en el formato necesario
                $fechaHoraPanama = date('Y-m-d\TH:i');
                ?>
                <input type="datetime-local" id="fecha" name="fecha" value="<?php echo $fechaHoraPanama; ?>" required>
            </div>
            <div class="gender-category">
                <span class="gender-title"><i class="fa-solid fa-box-open"></i> Estado del Empanque:</span>
                <select name="estado_empaque" required>
                    <option value="">Seleccione una opcion</option>
                    <option value="Nuevo">Nuevo</option>
                    <option value="Abierto">Abierto</option>
                    <option value="Dañado">Dañado</option>
                    <option value="Rechazado">Rechazado</option>
                </select>
            </div>

            <div class="gender-category">
                <span class="gender-title"><i class="fa-solid fa-box"></i> Estado del producto:</span>
                <select name="estado_producto" required>
                    <option value="">Seleccione una opcion</option>
                    <option value="En buen estado">En buen estado</option>
                    <option value="Dañado">Dañado</option>
                    <option value="Caducado">Caducado</option>
                </select>
            </div>
            <div class="input-box">
                <label for="recibido"><i class="fa-solid fa-user"></i> Recibido por:</label>
                <input type="text" placeholder="Introduzca la persona que lo recibe" id="recibido" name="recibido" value="<?php
                            if (isset($_SESSION['user_email'])) {
                                echo htmlspecialchars($_SESSION['user_email']);
                            } else {
                                echo "Usuario";
                            }
                            ?>"readonly >
            </div>

            <!-- Integracion de los datos ya almacenados de las tablas proveedor y bodega  -->

            <div class="gender-category">
                <span class="gender-title"><i class="fa-solid fa-warehouse"></i> Nombre Bodega:</span>
                <select id="Bodega" name="Bodega" required>
                    <option value="">Seleccione una bodega</option>
                    <?php foreach ($bodegas as $bodega): ?>
                        <option value="<?php echo $bodega['ID_BODEGA']; ?>"><?php echo $bodega['NOMBRE']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="gender-category">
                <span class="gender-title"><i class="fa-solid fa-truck"></i> Nombre del proveedor:</span>
                <select id="proveedor" name="proveedor" required>
                    <option value="">Seleccione un proveedor</option>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo $proveedor['ID_PROVEEDOR']; ?>"><?php echo $proveedor['NOMBRE']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="button-container-1">
            <a href="index_recibo.php" class="btn-1">
                <i class="fa-solid fa-reply"></i> Regresar
            </a>
            <button type="submit"> <i class="fa-solid fa-plus"></i> Agregar</button>
        </div>

    </form>
</div>