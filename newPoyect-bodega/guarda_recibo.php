<?php

require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$correcto = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validación del estado del empaque
    $estado_empaque = isset($_POST['estado_empaque']) ? $_POST['estado_empaque'] : '';
    $valid_states_empaque = ['Nuevo', 'Abierto', 'Dañado', 'Rechazado'];

    if (!in_array($estado_empaque, $valid_states_empaque)) {
        die('Estado del empaque no válido');
    }

    // Validación del estado del producto
    $estado_producto = isset($_POST['estado_producto']) ? $_POST['estado_producto'] : '';
    $valid_states_producto = ['En buen estado', 'Dañado', 'Caducado'];

    if (!in_array($estado_producto, $valid_states_producto)) {
        die('Estado del producto no válido');
    }

    $fecha = $_POST['fecha'];
    $recibido = $_POST['recibido'];
    $id_bodega = $_POST['Bodega'];
    $id_proveedor = $_POST['proveedor'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Actualizar un registro existente
        $id = $_POST['id'];
        $query = $con->prepare("UPDATE t_recibo_bodega SET FECHA_RECEPCION = ?, ESTADO_EMPAQUE = ?, ESTADO_PRODUCTO = ?, RECIBIDO_POR = ?, ID_BODEGA = ?, ID_PROVEEDOR = ? WHERE ID_RECIBO = ?");
        $resultado = $query->execute(array($fecha, $estado_empaque, $estado_producto, $recibido, $id_bodega, $id_proveedor, $id));
        
    } else {
        // Insertar un nuevo registro
        $query = $con->prepare("INSERT INTO t_recibo_bodega 
            (FECHA_RECEPCION, ESTADO_EMPAQUE, ESTADO_PRODUCTO, RECIBIDO_POR, ID_BODEGA, ID_PROVEEDOR) 
            VALUES 
            (:fecha, :estado_empaque, :estado_producto, :recibido, :id_bodega, :id_proveedor)");
        $resultado = $query->execute([
            'fecha' => $fecha,
            'estado_empaque' => $estado_empaque,
            'estado_producto' => $estado_producto,
            'recibido' => $recibido,
            'id_bodega' => $id_bodega,
            'id_proveedor' => $id_proveedor,
        ]);
    }

    if ($resultado) {
        $correcto = true;
        header('Location: index_recibo.php?toast=' . urlencode('Registro guardado exitosamente'));
        exit();
    } else {
        echo "Error al guardar el registro.";
    }
}
?>
