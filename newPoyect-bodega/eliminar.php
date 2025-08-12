<?php

require 'config/database.php';

$db = new Database();
$con = $db->conectar();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = $con->prepare("DELETE FROM t_proveedores WHERE ID_PROVEEDOR=?");
    if ($query->execute([$id])) {
        echo 'Registro eliminado';
    } else {
        echo 'Error al eliminar registro';
    }
} else {
    echo 'ID no proporcionado';
}

?>