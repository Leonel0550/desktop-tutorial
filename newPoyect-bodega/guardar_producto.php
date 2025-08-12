<?php

require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$correcto = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Manejo del caso en que se actualiza una categoría existente
        $id = $_POST['id'];
        $categoria = $_POST['categorias'];
        $producto = $_POST['producto'];
        $detalles_producto = $_POST['detalles_producto'];
    
        $query = $con->prepare("UPDATE t_productos SET NOMBRE_PRODUCTO=?, DETALLES_CATEGORIA=?, ID_CATEGORIA=? WHERE ID_PRODUCTOS=?");
        $resultado = $query->execute([$producto,$detalles_producto,$categoria, $id]);

        if ($resultado) {
            header('Location: producto.php?toast=' . urlencode('Producto actualizada exitosamente'));
            exit();
        } else {
            echo "Error al actualizar la categoría.";
        }

    } else {
        // Inserción de nueva categoría
        $categoria = $_POST['categorias'];
        $producto = $_POST['producto'];
        $detalles_producto = $_POST['detalles_producto'];
        $query = $con->prepare("INSERT INTO t_productos (NOMBRE_PRODUCTO, DETALLES_CATEGORIA, ID_CATEGORIA)  VALUES (:prod, :detta, :cat)");
        $resultado = $query->execute(['prod' => $producto, 'detta' => $detalles_producto, 'cat' => $categoria ]);

        if ($resultado) {
            header('Location: producto.php?toast=' . urlencode('producto guardada exitosamente'));
            exit();
        } else {
            echo "Error al guardar la producto.";
        }
    }
}
?>
