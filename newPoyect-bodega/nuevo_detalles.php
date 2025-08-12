<?php
// Conexión a la base de datos y consultas para obtener bodegas y proveedores
require_once 'config/database.php';
$id_recibo = isset($_GET['id']) ? $_GET['id'] : die('ID de recibo no especificado.');
$db = new Database();
$con = $db->conectar();

// Obtener bodegas
$comandoProductos = $con->prepare("SELECT ID_PRODUCTOS, NOMBRE_PRODUCTO FROM t_productos");
$comandoProductos->execute();
$productos = $comandoProductos->fetchAll(PDO::FETCH_ASSOC);

?>

<body>
    <div class="container-form">
        <form action="guardar_detalles.php" method="POST" autocomplete="off">
            <input type="hidden" name="id_recibo" value="<?php echo $id_recibo; ?>">
            <h2>Nuevo Detalle de Recibo</h2>

            <div class="content">
                <div class="input-box">
                    <label for="fecha_entrada"><i class="fa-solid fa-calendar"></i> Fecha y Hora de salida:</label>
                    <?php
                    // Establecer la zona horaria a Panamá
                    date_default_timezone_set('America/Panama');
                    // Obtener la fecha y hora actual en el formato necesario
                    $fechaHoraPanama = date('Y-m-d\TH:i');
                    ?>
                    <input type="datetime-local" id="fecha_entrada" name="fecha_entrada" value="<?php echo $fechaHoraPanama; ?>" required>
                </div>

                <div class="gender-category">
                    <span class="gender-title"><i class="fa-solid fa-box"></i> Tipo de Empaque</span>
                    <select name="tipo_empaque" required>
                        <option value="">Seleccione una opción</option>
                        <option value="Papel">Papel</option>
                        <option value="Carton">Carton</option>
                        <option value="Metálico">Metálico</option>
                        <option value="Vidrio">Vidrio</option>
                        <option value="Madera">Madera</option>
                    </select>
                </div>

                <div class="gender-category">
                    <span class="gender-title"><i class="fa-solid fa-warehouse"></i> Nombre Producto:</span>
                    <select id="productos" name="productos" required>
                        <option value="">Seleccione una bodega</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['ID_PRODUCTOS']; ?>"><?php echo $producto['NOMBRE_PRODUCTO']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-box">
                    <label for="piezas"><i class="fa-solid fa-cubes"></i> Piezas</label>
                    <input type="number" placeholder="Introduzca la cantidad de piezas" id="piezas" name="piezas" min="1" max="99" required>
                </div>

                <div class="input-box">
                    <label for="altura"><i class="fa-solid fa-arrows-alt-v"></i> Altura (in)</label>
                    <input type="number" step="any" placeholder="Introduzca la altura en pulgadas" id="altura" name="altura" min="1" max="99" required>
                </div>
                <div class="input-box">
                    <label for="ancho"><i class="fa-solid fa-arrows-alt-h"></i> Ancho (in)</label>
                    <input type="number" step="any" placeholder="Introduzca la anchura en pulgadas" id="ancho" name="ancho" min="1" max="99" required>
                </div>
                <div class="input-box">
                    <label for="largo"><i class="fa-solid fa-ruler-horizontal"></i> Largo (in)</label>
                    <input type="number" step="any" placeholder="Introduzca el largo en pulgadas" id="largo" name="largo" min="1" max="99" required>
                </div>
                <div class="input-box">
                    <label for="peso"><i class="fa-solid fa-weight-hanging"></i> Peso (lbs.)</label>
                    <input type="number" step="any" placeholder="Introduzca el peso en libras" id="peso" name="peso" min="1" max="99" required>
                </div>
                <div class="input-box">
                    <label for="observaciones"><i class="fa-solid fa-clipboard"></i> Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="4" placeholder="¿Detalles que falten?"></textarea>
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

</html>