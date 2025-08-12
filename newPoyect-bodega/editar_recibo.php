<?php

require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$id = isset($_GET['id']) ? $_GET['id'] : '';

if ($id) {
    $query = $con->prepare("SELECT FECHA_RECEPCION, ESTADO_EMPAQUE, ESTADO_PRODUCTO, RECIBIDO_POR, ID_BODEGA, ID_PROVEEDOR FROM t_recibo_bodega WHERE ID_RECIBO = ?");
    $query->execute([$id]);
    $row = $query->fetch(PDO::FETCH_ASSOC);
} else {
    $row = ['FECHA_RECEPCION' => '', 'ESTADO_EMPAQUE' => '', 'ESTADO_PRODUCTO' => '', 'RECIBIDO_POR' => '', 'ID_BODEGA' => '', 'ID_PROVEEDOR' => ''];
}

// Obtener las opciones para los selects de bodega y proveedor
$query_bodegas = $con->query("SELECT ID_BODEGA, NOMBRE FROM t_stp_bodega");
$bodegas = $query_bodegas->fetchAll(PDO::FETCH_ASSOC);

$query_proveedores = $con->query("SELECT ID_PROVEEDOR, NOMBRE FROM t_proveedores");
$proveedores = $query_proveedores->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-form">
        <form action="guarda_recibo.php" method="POST" autocomplete="off">
            <input type="hidden" id="id" name="id" value="<?php echo $id ?>">
            <h2>Editar Recibo</h2>
            <div class="content">
                <div class="input-box">
                    <label for="fecha"><i class="fa-solid fa-calendar"></i> Fecha de recepción:</label>
                    <input type="date" placeholder="Introduzca la fecha" id="fecha" name="fecha" value="<?php echo $row['FECHA_RECEPCION']; ?>" required autofocus>
                </div>
                <div class="gender-category">
                    <span class="gender-title"><i class="fa-solid fa-box-open"></i> Estado del Empaque</span>
                    <select name="estado_empaque" required>
                        <option value="">Seleccione una opción</option>
                        <option value="Nuevo"<?php echo ($row['ESTADO_EMPAQUE'] == 'Nuevo') ? 'selected' : ''; ?>>Nuevo</option>
                        <option value="Abierto"<?php echo ($row['ESTADO_EMPAQUE'] == 'Abierto') ? 'selected' : ''; ?>>Abierto</option>
                        <option value="Dañado"<?php echo ($row['ESTADO_EMPAQUE'] == 'Dañado') ? 'selected' : ''; ?>>Dañado</option>
                        <option value="Rechazado"<?php echo ($row['ESTADO_EMPAQUE'] == 'Rechazado') ? 'selected' : ''; ?>>Rechazado</option>
                    </select>
                </div>
                <div class="gender-category">
                    <span class="gender-title"><i class="fa-solid fa-box"></i> Estado del producto</span>
                    <select name="estado_producto" required>
                        <option value="">Seleccione una opción</option>
                        <option value="En buen estado" <?php echo ($row['ESTADO_PRODUCTO'] == 'En buen estado') ? 'selected' : ''; ?>>En buen estado</option>
                        <option value="Dañado"<?php echo ($row['ESTADO_PRODUCTO'] == 'Dañado') ? 'selected' : ''; ?>>Dañado</option>
                        <option value="Caducado"<?php echo ($row['ESTADO_PRODUCTO'] == 'Caducado') ? 'selected' : ''; ?>>Caducado</option>
                    </select>
                </div>
                <div class="input-box">
                    <label for="recibido"><i class="fa-solid fa-user"></i> Recibido por:</label>
                    <input type="text" placeholder="Introduzca la persona que lo recibe" id="recibido" name="recibido" value="<?php echo $row['RECIBIDO_POR']; ?>" required>
                </div>

                <!-- Integracion de los datos ya almacenados de las tablas proveedor y bodega  -->

                <div class="gender-category">
                    <span class="gender-title"><i class="fa-solid fa-warehouse"></i> Nombre Bodega</span>
                    <select id="Bodega" name="Bodega" required>
                        <option value="">Seleccione una bodega</option>
                        <?php foreach ($bodegas as $bodega): ?>
                            <option value="<?php echo $bodega['ID_BODEGA']; ?>" <?php echo ($row['ID_BODEGA'] == $bodega['ID_BODEGA']) ? 'selected' : ''; ?>>
                                <?php echo $bodega['NOMBRE']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="gender-category">
                    <span class="gender-title"><i class="fa-solid fa-truck"></i> Nombre del proveedor</span>
                    <select id="proveedor" name="proveedor" required>
                        <option value="">Seleccione un proveedor</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <option value="<?php echo $proveedor['ID_PROVEEDOR']; ?>" <?php echo ($row['ID_PROVEEDOR'] == $proveedor['ID_PROVEEDOR']) ? 'selected' : ''; ?>>
                                <?php echo $proveedor['NOMBRE']; ?>
                            </option>
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