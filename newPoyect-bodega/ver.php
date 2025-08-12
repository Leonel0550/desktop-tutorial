<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$comando = $con->prepare("SELECT * FROM t_proveedores WHERE ID_PROVEEDOR = :id LIMIT 1");
$comando->bindParam(':id', $id, PDO::PARAM_INT);
$comando->execute();
$proveedor = $comando->fetch(PDO::FETCH_ASSOC);

if (!$proveedor) {
    echo "Proveedor no encontrado.";
    exit;
}
?>

<div class="detalles-content">
    <h2>Detalles del Proveedor</h2>
    <p><strong><i class="fas fa-user"></i> Nombre:</strong> <?php echo htmlspecialchars($proveedor['NOMBRE']); ?></p>
    <p><strong><i class="fas fa-id-card"></i> RUC:</strong> <?php echo htmlspecialchars($proveedor['RUC']); ?></p>
    <p><strong><i class="fas fa-id-badge"></i> DV:</strong> <?php echo htmlspecialchars($proveedor['DV']); ?></p>
    <p><strong><i class="fas fa-map-marker-alt"></i> Dirección:</strong> <?php echo htmlspecialchars($proveedor['DIRECCION']); ?></p>
    <p><strong><i class="fas fa-phone"></i> Teléfono:</strong> <?php echo htmlspecialchars($proveedor['TELEFONO']); ?></p>
    <p><strong><i class="fas fa-envelope"></i> Correo:</strong> <?php echo htmlspecialchars($proveedor['CORREO']); ?></p>
    <p><strong><i class="fas fa-toggle-on"></i> Estado:</strong> <?php echo htmlspecialchars($proveedor['ESTADO']); ?></p>
    <p><strong><i class="fas fa-comment-dots"></i> Observaciones:</strong> <?php echo htmlspecialchars($proveedor['OBSERVACIONES']); ?></p>
    <div class="botones">
        <a href="index.php" class="boton-regresar">Regresar</a>
    </div>
</div>