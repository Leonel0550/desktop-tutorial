<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $id_recibo = isset($_POST['id_recibo']) ? $_POST['id_recibo'] : die('ID de recibo no especificado.');
    $id_detalle = isset($_POST['id_detalle']) ? $_POST['id_detalle'] : die('ID de detalles no especificado.');
    $fecha_salida = $_POST['fecha_salida'];
    $piezas_salida = $_POST['piezas_salida'];
    $destinatario = $_POST['Bodega'];
    $razon_salida = $_POST['razon_salida'];
    $producto = $_POST['productos'];

    // Obtener la cantidad de piezas disponibles y el nombre del producto
    $comando_detalle = $con->prepare("SELECT PIEZAS, ID_PRODUCTOS FROM t_recibo_bodega_detalles WHERE ID_RECIBO = ? AND ID_RECIBO_DETALLE = ?");
    $comando_detalle->execute([$id_recibo, $id_detalle]);
    $detalles = $comando_detalle->fetch(PDO::FETCH_ASSOC);

  // Obtener el destinatario basado en la selección
  if (isset($_POST['Bodega']) && $_POST['Bodega'] !== '') {
    if ($_POST['Bodega'] === 'otro') {
        // Si se seleccionó "Otro", usar el valor del input correspondiente
        $destinatario = isset($_POST['otroDestinatario']) ? $_POST['otroDestinatario'] : '';
    } else {
        // Obtener el nombre de la bodega seleccionada
        $bodega_id = $_POST['Bodega'];
        $comando_bodega = $con->prepare("SELECT NOMBRE FROM t_stp_bodega WHERE ID_BODEGA = ?");
        $comando_bodega->execute([$bodega_id]);
        $bodega = $comando_bodega->fetch(PDO::FETCH_ASSOC);
        $destinatario = $bodega['NOMBRE'] ?? ''; // Asegúrate de manejar el caso en que no se encuentre la bodega
    }
} else {
    die('No se seleccionó ningún destinatario.');
}
    if (!$detalles) {
        die('No se encontraron detalles para el recibo especificado.');
    }

    // Obtener el nombre del producto usando el ID_PRODUCTOS
    $comando_producto = $con->prepare("SELECT NOMBRE_PRODUCTO FROM t_productos WHERE ID_PRODUCTOS = ?");
    $comando_producto->execute([$detalles['ID_PRODUCTOS']]);
    $producto_data = $comando_producto->fetch(PDO::FETCH_ASSOC);

    if (!$producto_data) {
        die('No se encontró el producto.');
    }

    // Verificar si la cantidad de piezas de salida es válida
    if ($piezas_salida > $detalles['PIEZAS']) {
        die('La cantidad de piezas de salida no puede ser mayor que la cantidad disponible.');
    }

    // Verificar si se está actualizando un registro existente
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Actualizar un registro existente
        $id_saliente = $_POST['id'];
        $query = $con->prepare("
            UPDATE t_saliente 
            SET FECHA_SALIDA = ?, CANTIDAD_SALIDA = ?, RAZON_SALIDA = ?, DESTINATARIO = ?
            WHERE ID_SALIENTE = ?
        ");
        $resultado = $query->execute([$fecha_salida, $piezas_salida, $razon_salida, $destinatario, $id_saliente]);

        if ($resultado) {
            // Aquí deberías utilizar el ID del producto en vez del nombre
            $producto_id = isset($_POST['productos']) ? $_POST['productos'] : die('ID del producto no especificado.');

            // Luego, cuando insertes en t_historial_documentos, usa $producto_id
            $comando_historial = $con->prepare("
                INSERT INTO t_historial_documentos 
                (ID_SALIENTE, PRODUCTOS, PIEZAS, FECHA, TIPO) 
                VALUES (?, ?, ?, ?, 'Saliente')
                ");
            $comando_historial->execute([$id_saliente, $producto_id, $piezas_salida, $fecha_salida]);


            // Restar las piezas de salida de las piezas disponibles
            $nueva_cantidad = $detalles['PIEZAS'] - $piezas_salida;
            $query_actualizar = $con->prepare("UPDATE t_recibo_bodega_detalles SET PIEZAS = ? WHERE ID_RECIBO = ? AND ID_RECIBO_DETALLE = ?");
            $query_actualizar->execute([$nueva_cantidad, $id_recibo, $id_detalle]);

            header("Location: saliente_detalles.php?id=$id_recibo&toast=" . urlencode('Saliente actualizado exitosamente'));
            exit;
        } else {
            echo "Error al guardar los salientes.";
        }
    } else {
        // Verificar si el ID_RECIBO existe en la tabla t_recibo_bodega_detalles
        $comando_verificar = $con->prepare("SELECT ID_RECIBO FROM t_recibo_bodega_detalles WHERE ID_RECIBO = ?");
        $comando_verificar->execute([$id_recibo]);
        if ($comando_verificar->rowCount() == 0) {
            die('ID de recibo no válido.');
        }

        // Insertar un nuevo registro en t_saliente
        $query = $con->prepare("
            INSERT INTO t_saliente 
            (ID_RECIBO_DETALLES, FECHA_SALIDA, CANTIDAD_SALIDA, RAZON_SALIDA, DESTINATARIO) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $resultado = $query->execute([$id_detalle, $fecha_salida, $piezas_salida, $razon_salida, $destinatario]);

        if ($resultado) {
            // Intentar obtener el ID del registro insertado
            $id_saliente = $con->lastInsertId();

            if (!$id_saliente) {
                die("Error al obtener el ID del registro saliente.");
            }

            // Insertar en t_historial_documentos usando el nombre del producto
            // Inserción en t_historial_documentos
            // Aquí deberías utilizar el ID del producto en vez del nombre
            $producto_id = isset($_POST['productos']) ? $_POST['productos'] : die('ID del producto no especificado.');

            // Luego, cuando insertes en t_historial_documentos, usa $producto_id
            $comando_historial = $con->prepare("
    INSERT INTO t_historial_documentos 
    (ID_SALIENTE, PRODUCTOS, PIEZAS, FECHA, TIPO) 
    VALUES (?, ?, ?, ?, 'Saliente')
");
            $comando_historial->execute([$id_saliente, $producto_id, $piezas_salida, $fecha_salida]);
            // Restar las piezas de salida de las piezas disponibles
            $nueva_cantidad = $detalles['PIEZAS'] - $piezas_salida;
            $query_actualizar = $con->prepare("UPDATE t_recibo_bodega_detalles SET PIEZAS = ? WHERE ID_RECIBO = ? AND ID_RECIBO_DETALLE = ?");
            $query_actualizar->execute([$nueva_cantidad, $id_recibo, $id_detalle]);

            header("Location: saliente.php?id=$id_recibo&toast=" . urlencode('Detalles de salida agregados exitosamente y historial actualizado'));
            exit;
        } else {
            $errorInfo = $query->errorInfo();
            die("Error al insertar en t_saliente: " . $errorInfo[2]);
        }
    }
}
