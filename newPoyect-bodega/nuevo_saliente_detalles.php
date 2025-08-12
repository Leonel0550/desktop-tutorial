<?php
// Conexión a la base de datos y consultas para obtener bodegas y proveedores
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$id_recibo = isset($_GET['id']) ? $_GET['id'] : die('ID de recibo no especificado.');
$id_detalle = isset($_GET['id_detalle']) ? $_GET['id_detalle'] : die('ID de detalle no especificado.');

// Obtener bodegas
$comandoBodegas = $con->prepare("SELECT ID_BODEGA, NOMBRE FROM t_stp_bodega");
$comandoBodegas->execute();
$bodegas = $comandoBodegas->fetchAll(PDO::FETCH_ASSOC);

// Obtener bodegas
$comandoProductos = $con->prepare("SELECT ID_PRODUCTOS, NOMBRE_PRODUCTO FROM t_productos");
$comandoProductos->execute();
$productos = $comandoProductos->fetchAll(PDO::FETCH_ASSOC);

// Obtener detalles del recibo, incluyendo el nombre del producto
$comando = $con->prepare("
    SELECT 
        r.ID_RECIBO_DETALLE, 
        r.TIPO_EMPAQUE,
        r.ID_PRODUCTOS, 
        r.PIEZAS, 
        r.ALTO,
        r.ANCHO,
        r.LARGO,
        r.PESO,
        r.OBSERVACION,
        r.TRACKING,
        p.NOMBRE_PRODUCTO AS NOMBRE_PRODUCTO
    FROM t_recibo_bodega_detalles r
    JOIN t_productos p ON r.ID_PRODUCTOS = p.ID_PRODUCTOS
    WHERE r.ID_RECIBO_DETALLE = ?
");

try {
    $comando->execute([$id_detalle]);
    $detalles = $comando->fetch(PDO::FETCH_ASSOC);
    if (!$detalles) {
        die('Detalle de recibo no encontrado.');
    }
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<body>
    <div class="container-form">
        <form action="guardar_saliente_detalles.php" method="POST" autocomplete="off">
            <input type="hidden" name="id_recibo" value="<?php echo $id_recibo; ?>">
            <input type="hidden" name="id_detalle" value="<?php echo $id_detalle; ?>">
            <h2>Nuevo Pedido</h2>

            <div class="content">
                <!-- Mostrar el ID de detalle -->

                <div class="input-box">
                    <label for="fecha_salida"><i class="fa-solid fa-calendar"></i> Fecha y Hora de salida:</label>
                    <?php
                    // Establecer la zona horaria a Panamá
                    date_default_timezone_set('America/Panama');

                    // Obtener la fecha y hora actual en el formato necesario
                    $fechaHoraPanama = date('Y-m-d\TH:i');
                    ?>
                    <input type="datetime-local" id="fecha_salida" name="fecha_salida"
                        value="<?php echo $fechaHoraPanama; ?>" required autofocus>
                </div>

                <div class="input-box">
                    <label for="id_detalle"><i class="fa-solid fa-id-card"></i> ID Detalle:</label>
                    <input type="text" id="id_detalle" name="id_detalle" value="<?php echo $id_detalle; ?>" readonly>
                </div>

                <div class="input-box">
                    <label for="productos"><i class="fa-solid fa-box"></i> Producto:</label>
                    <input type="text" id="productos" name="productos" value="<?php echo $detalles['NOMBRE_PRODUCTO']; ?>" readonly>
                    <input type="hidden" name="productos" value="<?php echo $detalles['ID_PRODUCTOS']; ?>"> <!-- Campo oculto con ID -->
                </div>
                <!-- Mostrar las Piezas -->
                <div class="input-box">
                    <label for="piezas"><i class="fa-solid fa-cubes"></i> Piezas:</label>
                    <input type="text" id="piezas" name="piezas" value="<?php echo $detalles['PIEZAS']; ?>" readonly>
                </div>

                <div class="input-box">
                    <label for="piezas_salida"><i class="fa-solid fa-cubes"></i> Piezas de salida:</label>
                    <input type="number" placeholder="Introduzca la cantidad de piezas de salida"
                        id="piezas_salida" name="piezas_salida" min="1"
                        max="<?php echo $detalles['PIEZAS']; ?>" required
                        oninput="validarPiezasSalida(this, <?php echo $detalles['PIEZAS']; ?>)">
                </div>
                <div class="gender-category">
                    <span class="gender-title"><i class="fa-solid fa-warehouse"></i> Destinatario:</span>
                    <select id="Bodega" name="Bodega" onchange="toggleOtroBodega(this)" required>
                        <option value="">Seleccione el destinatario</option>
                        <?php foreach ($bodegas as $bodega): ?>
                            <option value="<?php echo $bodega['ID_BODEGA']; ?>"><?php echo $bodega['NOMBRE']; ?></option>
                        <?php endforeach; ?>
                        <option value="otro">Otro (Especificar)</option>
                    </select>
                </div>

                <div class="input-box" id="otroBodegaInput" style="display: none;">
                    <label for="otroDestinatario"><i class="fa-solid fa-list"></i> Especificar Destinatario:</label>
                    <input type="text" placeholder="Introduzca el destinatario" id="otroDestinatario" name="otroDestinatario">
                </div>


                <div class="input-box">
                    <label for="razon_salida"><i class="fa-solid fa-user"></i> Razón de salida:</label>
                    <input type="text" placeholder="Introduzca la razón de salida" id="razon_salida" name="razon_salida" required>
                </div>
            </div>
            <div class="button-container-1">
                <a href="saliente.php" class="btn-1">
                    <i class="fa-solid fa-reply"></i> Regresar
                </a>
                <button type="submit"> <i class="fa-solid fa-plus"></i> Agregar</button>
            </div>
        </form>
    </div>





    <!-- Validación para que peso no sea negativo-->
    <script>
        // Agregar evento de escucha para cada campo de entrada
        document.getElementById('piezas').addEventListener('input', function(e) {
            validarNoNegativo(e.target);
        });

        // Función para validar que el valor no sea negativo
        function validarNoNegativo(input) {
            const value = input.value;
            if (value < 0) {
                input.setCustomValidity('El valor no puede ser negativo');
            } else {
                input.setCustomValidity('');
            }
        }

        function validarPiezasSalida(input, maxPiezas) {
            const value = parseInt(input.value, 10);
            if (value > maxPiezas) {
                input.setCustomValidity('No puede exceder la cantidad de piezas disponibles: ' + maxPiezas);
            } else {
                input.setCustomValidity('');
            }
        }

        function toggleOtroBodega(select) {
            const otroBodegaInput = document.getElementById('otroBodegaInput');

            if (select.value === 'otro') {
                otroBodegaInput.style.display = 'block'; // Mostrar el input
                document.getElementById('nuevoDestinatario').focus(); // Opcional: enfocar el input
            } else {
                otroBodegaInput.style.display = 'none'; // Ocultar el input
            }
        }
    </script>
</body>