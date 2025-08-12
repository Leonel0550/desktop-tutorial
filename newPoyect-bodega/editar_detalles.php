<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$id_recibo = isset($_GET['id']) ? $_GET['id'] : die('ID de recibo no especificado.');
$id_detalle = isset($_GET['id_detalle']) ? $_GET['id_detalle'] : die('ID de detalle no especificado.');

// Obtener los datos del detalle específico
$query = $con->prepare("
    SELECT TIPO_EMPAQUE,ID_PRODUCTOS, PIEZAS, ALTO, ANCHO, LARGO, PESO, OBSERVACION 
    FROM t_recibo_bodega_detalles 
    WHERE ID_RECIBO_DETALLE = ?
");
$query->execute([$id_detalle]);
$row = $query->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('ID de detalle no encontrado en la base de datos.');
}

// Obtener bodegas
$comandoProductos = $con->prepare("SELECT ID_PRODUCTOS, NOMBRE_PRODUCTO FROM t_productos");
$comandoProductos->execute();
$productos = $comandoProductos->fetchAll(PDO::FETCH_ASSOC);

?>

<body>
    <div class="container-form">
        <form action="guardar_detalles.php" method="POST" autocomplete="off">
            <input type="hidden" name="id_recibo" value="<?php echo htmlspecialchars($id_recibo, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id_detalle, ENT_QUOTES, 'UTF-8'); ?>">
            <h2>Editar Detalle del Recibo</h2>

            <div class="content">
                <div class="input-box">
                    <label for="fecha_entrada"><i class="fa-solid fa-calendar"></i> Fecha y Hora de salida:</label>
                    <?php
                    // Establecer la zona horaria a Panamá
                    date_default_timezone_set('America/Panama');

                    // Obtener la fecha y hora actual en el formato necesario
                    $fechaHoraPanama = date('Y-m-d\TH:i');
                    ?>
                    <input type="datetime-local" id="fecha_entrada" name="fecha_entrada"
                        value="<?php echo $fechaHoraPanama; ?>" required>
                </div>

                <div class="gender-category">
                    <span class="gender-title"><i class="fa-solid fa-box"></i> Tipo de Empaque</span>
                    <select name="tipo_empaque" required>
                        <option value="">Seleccione una opción</option>
                        <option value="Papel" <?php echo ($row['TIPO_EMPAQUE'] == 'Papel') ? 'selected' : ''; ?>>Papel</option>
                        <option value="Carton" <?php echo ($row['TIPO_EMPAQUE'] == 'Carton') ? 'selected' : ''; ?>>Carton</option>
                        <option value="Metálico" <?php echo ($row['TIPO_EMPAQUE'] == 'Metálico') ? 'selected' : ''; ?>>Metálico</option>
                        <option value="Vidrio" <?php echo ($row['TIPO_EMPAQUE'] == 'Vidrio') ? 'selected' : ''; ?>>Vidrio</option>
                        <option value="Madera" <?php echo ($row['TIPO_EMPAQUE'] == 'Madera') ? 'selected' : ''; ?>>Madera</option>
                    </select>
                </div>
                <div class="gender-category">
                    <span class="gender-title"><i class="fa-solid fa-warehouse"></i> Nombre Producto:</span>
                    <select id="productos" name="productos" required>
                        <option value="">Seleccione una bodega</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['ID_PRODUCTOS']; ?>" <?php echo ($row['ID_PRODUCTOS'] == $producto['ID_PRODUCTOS']) ? 'selected' : ''; ?>>
                                <?php echo $producto['NOMBRE_PRODUCTO']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-box">
                    <label for="piezas"><i class="fa-solid fa-cubes"></i> Piezas</label>
                    <input type="number" placeholder="Introduzca la cantidad de piezas" id="piezas" name="piezas" min="1" max="99" value="<?php echo htmlspecialchars($row['PIEZAS'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="input-box">
                    <label for="altura"><i class="fa-solid fa-arrows-alt-v"></i> Altura (in)</label>
                    <input type="number" step="any" placeholder="Introduzca la altura en pulgadas" id="altura" name="altura" min="1" max="99" value="<?php echo htmlspecialchars($row['ALTO'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="input-box">
                    <label for="ancho"><i class="fa-solid fa-arrows-alt-h"></i> Ancho (in)</label>
                    <input type="number" step="any" placeholder="Introduzca la Anchura en pulgadas" id="ancho" name="ancho" min="1" max="99" value="<?php echo htmlspecialchars($row['ANCHO'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="input-box">
                    <label for="largo"><i class="fa-solid fa-ruler-horizontal"></i> Largo (in)</label>
                    <input type="number" step="any" placeholder="Introduzca el largo en pulgadas" id="largo" name="largo" min="1" max="99" value="<?php echo htmlspecialchars($row['LARGO'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="input-box">
                    <label for="peso"><i class="fa-solid fa-weight-hanging"></i> Peso (lbs.)</label>
                    <input type="number" step="any" placeholder="Introduzca el peso en libras" id="peso" name="peso" min="1" max="99" value="<?php echo htmlspecialchars($row['PESO'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="input-box">
                    <label for="observaciones"><i class="fa-solid fa-clipboard"></i> Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="8" placeholder="¿Detalles que falten?" required><?php echo htmlspecialchars($row['OBSERVACION'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>
            <div class="button-container-1">
                <a href="index.php" class="btn-1">
                    <i class="fa-solid fa-reply"></i> Regresar
                </a>
                <button type="submit"> <i class="fa-solid fa-plus"></i> Agregar</button>
            </div>
        </form>
    </div>
</body>