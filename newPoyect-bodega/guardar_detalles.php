<?php

require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validación del tipo de empaque
    $tipo_empaque = isset($_POST['tipo_empaque']) ? $_POST['tipo_empaque'] : '';
    $valid_type_empaque = ['Papel', 'Carton', 'Metálico', 'Vidrio', 'Madera'];

    if (!in_array($tipo_empaque, $valid_type_empaque)) {
        die('Tipo de empaque no válido');
    }

    $id_recibo = isset($_POST['id_recibo']) ? $_POST['id_recibo'] : die('ID de recibo no especificado.');
    $fecha_entrada = $_POST['fecha_entrada'];
    $producto = $_POST['productos'];
    $piezas = $_POST['piezas'];
    $alto = $_POST['altura'];
    $ancho = $_POST['ancho'];
    $largo = $_POST['largo'];
    $peso = $_POST['peso'];
    $observacion = $_POST['observaciones'];

   // $tracking = $_POST['tracking'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Actualizar un registro existente
        $id_detalle = $_POST['id'];
        $query = $con->prepare("
            UPDATE t_recibo_bodega_detalles 
            SET TIPO_EMPAQUE = ?, FECHA_ENTRADA = ?, ID_PRODUCTOS = ?, PIEZAS = ?, ALTO = ?, ANCHO = ?, LARGO = ?, PESO = ?, OBSERVACION = ?
            WHERE ID_RECIBO_DETALLE = ?
        ");
        $resultado = $query->execute([$tipo_empaque, $fecha_entrada, $producto, $piezas, $alto, $ancho, $largo, $peso, $observacion, $id_detalle]);
        
        if ($resultado) {
            // Actualización exitosa, ahora registrar en el historial
            $comando_historial = $con->prepare("
                INSERT INTO t_historial_documentos 
                (ID_RECIBO_DETALLE, PRODUCTOS, PIEZAS, FECHA, TIPO) 
                VALUES (?, ?, ?, ?, 'Entrante')
            ");
            $comando_historial->execute([$id_detalle, $producto, $piezas, $fecha_entrada]);
            
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
            (ID_RECIBO, TIPO_EMPAQUE, FECHA_ENTRADA, ID_PRODUCTOS, PIEZAS, ALTO, ANCHO, LARGO, PESO, OBSERVACION) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $resultado = $comando->execute([$id_recibo, $tipo_empaque, $fecha_entrada, $producto, $piezas, $alto, $ancho, $largo, $peso, $observacion]);

        if ($resultado) {
            // Registro exitoso en t_recibo_bodega_detalles, ahora registrar en el historial
            $id_recibo_detalle = $con->lastInsertId(); // Obtener el ID de la última inserción
            
            $comando_historial = $con->prepare("
                INSERT INTO t_historial_documentos 
                (ID_RECIBO_DETALLE, PRODUCTOS, PIEZAS, FECHA, TIPO) 
                VALUES (?, ?, ?, ?, 'Entrante')
            ");
            $comando_historial->execute([$id_recibo_detalle, $producto, $piezas, $fecha_entrada]);

            header("Location: detalles_recibo.php?id=$id_recibo&toast=" . urlencode('Detalles de entrada agregados exitosamente y historial actualizado'));
            exit;
        } else {
            die('Error al insertar los detalles.');
        }
    }
}
?>

