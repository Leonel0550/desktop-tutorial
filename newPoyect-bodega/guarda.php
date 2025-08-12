<?php

require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$correcto = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id'])) {
        // Manejo del caso en que se actualiza un proveedor existente
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $ruc = $_POST['ruc'];
        $dv = $_POST['dv'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $correo = $_POST['correo'];
        $observaciones = $_POST['observaciones'];
        
        // Validación del estado
        $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
        $valid_states = ['activo', 'inactivo'];

        if (!in_array($estado, $valid_states)) {
            die('Estado no válido');
        }

        $query = $con->prepare("UPDATE t_proveedores SET NOMBRE=?, RUC=?, DV=?, DIRECCION=?, TELEFONO=?, CORREO=?, OBSERVACIONES=?, ESTADO=? WHERE ID_PROVEEDOR=? ");
        $resultado = $query->execute(array($nombre, $ruc, $dv, $direccion, $telefono, $correo, $observaciones, $estado, $id));

        if ($resultado) {
            $correcto = true;
        }

    } else {
        // Validación del estado
        $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
        $valid_states = ['activo', 'inactivo'];

        if (!in_array($estado, $valid_states)) {
            die('Estado no válido');
        }

        $nombre = $_POST['nombre'];
        $ruc = $_POST['ruc'];
        $dv = $_POST['dv'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $correo = $_POST['correo'];
        $observaciones = $_POST['observaciones'];

        $query = $con->prepare("INSERT INTO t_proveedores (NOMBRE, RUC, DV, DIRECCION, TELEFONO, CORREO, OBSERVACIONES, ESTADO, ACTIVO) VALUES (:nom, :ruc, :dv, :dire, :tel, :cor, :obs, :est, 1)");
        $resultado = $query->execute([
            'nom' => $nombre,
            'ruc' => $ruc,
            'dv' => $dv,
            'dire' => $direccion,
            'tel' => $telefono,
            'cor' => $correo,
            'obs' => $observaciones,
            'est' => $estado
        ]);

    }
    if ($resultado) {
        $correcto = true;
        header('Location: index.php?toast=' . urlencode('proveedor guardado exitosamente'));
        exit();
    } else {
        echo "Error al guardar el registro.";
    }
}
?>
