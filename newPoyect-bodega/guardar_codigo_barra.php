<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_recibo = isset($_POST['id_recibo']) ? $_POST['id_recibo'] : die('ID de recibo no especificado.');
    $id_detalle = isset($_POST['id_detalle']) ? $_POST['id_detalle'] : null;
    $tracking = isset($_POST['tracking']) ? $_POST['tracking'] : '';

    if ($id_detalle) {
        // Actualizar un registro existente
        $query = $con->prepare("
            UPDATE t_recibo_bodega_detalles 
            SET TRACKING = ? 
            WHERE ID_RECIBO_DETALLE = ?
        ");
        $resultado = $query->execute([$tracking, $id_detalle]);
        
        if ($resultado) {
            header("Location: detalles_recibo.php?id=$id_recibo&toast=" . urlencode('Detalles de entrada actualizados exitosamente y historial actualizado'));
            exit;
        } else {
            die('Error al actualizar los detalles.');
        }
    } else {
        // Verificar si el ID_RECIBO existe en la tabla t_recibo_bodega
        $comando_verificar = $con->prepare("SELECT ID_RECIBO FROM t_recibo_bodega WHERE ID_RECIBO = ?");
        $comando_verificar->execute([$id_recibo]);
        if ($comando_verificar->rowCount() == 0) {
            die('ID de recibo no válido.');
        }

        // Insertar un nuevo registro en t_recibo_bodega_detalles
        $comando = $con->prepare("
            INSERT INTO t_recibo_bodega_detalles 
            (ID_RECIBO, TRACKING) 
            VALUES (?, ?)
        ");
        $resultado = $comando->execute([$id_recibo, $tracking]);

        if ($resultado) {
            header("Location: detalles_recibo.php?id=$id_recibo&toast=" . urlencode('Detalles de entrada agregados exitosamente y historial actualizado'));
            exit;
        } else {
            die('Error al insertar los detalles.');
        }
    }
}