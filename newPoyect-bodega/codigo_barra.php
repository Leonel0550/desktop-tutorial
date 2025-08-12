<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$id_recibo = isset($_GET['id']) ? $_GET['id'] : die('ID de recibo no especificado.');
$id_detalle = isset($_GET['id_detalle']) ? $_GET['id_detalle'] : '';

$tracking = '';
if ($id_detalle) {
    // Obtener los datos del detalle específico
    $query = $con->prepare("
        SELECT TRACKING 
        FROM t_recibo_bodega_detalles 
        WHERE ID_RECIBO_DETALLE = ?
    ");
    $query->execute([$id_detalle]);
    $row = $query->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $tracking = $row['TRACKING'];
    } else {
        die('ID de detalle no encontrado en la base de datos.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>QuaggaJS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <style>
        #camera video {
            width: 100%;
            max-width: 640px;
        }
    </style>
</head>
<body>
    <div class="container-form">
        <form action="guardar_codigo_barra.php" method="POST" autocomplete="off">
            <input type="hidden" name="id_recibo" value="<?php echo htmlspecialchars($id_recibo, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="id_detalle" value="<?php echo htmlspecialchars($id_detalle, ENT_QUOTES, 'UTF-8'); ?>">
            <h2><?php echo $id_detalle ? 'Editar Detalle de Recibo' : 'Nuevo Detalle de Recibo'; ?></h2>

            <div id="camera" style="width:100%"></div>
            <script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js"></script>
            <script>
                const quaggaConf = {
                    inputStream: {
                        target: document.querySelector("#camera"),
                        type: "LiveStream",
                        constraints: {
                            width: { min: 640 },
                            height: { min: 480 },
                            facingMode: "environment",
                            aspectRatio: { min: 1, max: 2 }
                        }
                    },
                    decoder: { readers: ['code_128_reader'] }
                }

                Quagga.init(quaggaConf, function(err) {
                    if (err) { return console.log(err); }
                    Quagga.start();
                });

                Quagga.onDetected(function(result) {
                    document.getElementById('tracking').value = result.codeResult.code;
                    Quagga.stop(); // Detener el escáner después de detectar un código
                });
            </script>

            <div class="input-box">
                <label for="tracking"><i class="fa-solid fa-barcode"></i> Tracking</label>
                <textarea name="tracking" id="tracking" rows="8" placeholder="Código de barra" required><?php echo htmlspecialchars($tracking, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="button-container-1">
                <a href="index_recibo.php" class="btn-1">
                    <i class="fa-solid fa-reply"></i> Regresar
                </a>
                <button type="submit"> <i class="fa-solid fa-plus"></i> <?php echo $id_detalle ? 'Actualizar' : 'Agregar'; ?></button>
            </div>
        </form>
    </div>
</body>
</html>