<?php

require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$correcto = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Manejo del caso en que se actualiza una categoría existente
        $id = $_POST['id'];
        $nombre = $_POST['categoria'];
    
        $query = $con->prepare("UPDATE t_categorias SET NOMBRE_CATE=? WHERE ID_CATEGORIAS=?");
        $resultado = $query->execute([$nombre, $id]);

        if ($resultado) {
            header('Location: categoria.php?toast=' . urlencode('Categoría actualizada exitosamente'));
            exit();
        } else {
            echo "Error al actualizar la categoría.";
        }

    } else {
        // Inserción de nueva categoría
        $nombre = $_POST['categoria'];
        $query = $con->prepare("INSERT INTO t_categorias (NOMBRE_CATE) VALUES (:nom)");
        $resultado = $query->execute(['nom' => $nombre]);

        if ($resultado) {
            header('Location: categoria.php?toast=' . urlencode('Categoría guardada exitosamente'));
            exit();
        } else {
            echo "Error al guardar la categoría.";
        }
    }
}
?>
