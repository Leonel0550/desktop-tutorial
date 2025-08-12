<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

// Obtener el ID del recibo desde la URL
$id_recibo = isset($_GET['id']) ? $_GET['id'] : die('ID de recibo no especificado.');

// Obtener la página actual y elementos por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$offset = ($page - 1) * $items_per_page;

// Definir las columnas permitidas para el ordenamiento
$validColumns = ['ID_RECIBO_DETALLE', 'FECHA_ENTRADA', 'TIPO_EMPAQUE', 'NOMBRE_PRODUCTOS', 'PIEZAS', 'ALTO', 'ANCHO', 'LARGO', 'PESO', 'OBSERVACION', 'TRACKING'];
$orderBy = isset($_GET['orderBy']) && in_array($_GET['orderBy'], $validColumns) ? $_GET['orderBy'] : 'ID_RECIBO_DETALLE';
$orderDir = isset($_GET['orderDir']) && strtolower($_GET['orderDir']) === 'desc' ? 'DESC' : 'ASC';

// Preparar y ejecutar la consulta para obtener los detalles específicos del recibo con paginación
$comando_detalles = $con->prepare("
SELECT 
    r.ID_RECIBO_DETALLE,
    r.FECHA_ENTRADA, 
    r.TIPO_EMPAQUE,
    r.PIEZAS, 
    r.ALTO,
    r.ANCHO,
    r.LARGO,
    r.PESO,
    r.OBSERVACION,
    r.TRACKING,
    b.NOMBRE_PRODUCTO AS NOMBRE_PRODUCTOS
FROM t_recibo_bodega_detalles r
JOIN t_productos b ON r.ID_PRODUCTOS = b.ID_PRODUCTOS
WHERE ID_RECIBO = ?
ORDER BY $orderBy $orderDir
LIMIT ? OFFSET ?
");
$comando_detalles->execute([$id_recibo, $items_per_page, $offset]);
$detalles = $comando_detalles->fetchAll(PDO::FETCH_ASSOC);

// Obtener la suma total de piezas para el recibo
$sum_piezas_query = $con->prepare("
    SELECT SUM(PIEZAS) AS total_piezas
    FROM t_recibo_bodega_detalles 
    WHERE ID_RECIBO = ?
");
$sum_piezas_query->execute([$id_recibo]);
$total_piezas = $sum_piezas_query->fetchColumn();

// Obtener el número total de detalles
$total_detalles_query = $con->prepare("
    SELECT COUNT(*) 
    FROM t_recibo_bodega_detalles 
    WHERE ID_RECIBO = ?
");
$total_detalles_query->execute([$id_recibo]);
$total_detalles = $total_detalles_query->fetchColumn();
$total_pages = ceil($total_detalles / $items_per_page);

$count_comando = $con->prepare("SELECT COUNT(*) as total_saliente FROM t_saliente");
$count_comando->execute();
$total_saliente = $count_comando->fetch(PDO::FETCH_ASSOC)['total_saliente'];
?>

<!-- Aquí empieza el HTML para los detalles del recibo -->
<div class="table_section">
     
    <table>
        <thead>
            <tr>
                <th><a href="#" class="sort" data-orderby="ID_RECIBO_DETALLE"># Detalle <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="TIPO_EMPAQUE">Tipo de Empaque <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="PRODUCTOS">Producto <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="PIEZAS">Piezas <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="ALTO">Alto (in) <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="ANCHO">Ancho (in) <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="LARGO">Largo (in) <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="PESO">Peso (lb) <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="OBSERVACION">Observación <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="TRACKING">Tracking <i class="fa-solid fa-sort"></i></a></th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $row) : ?>

                <tr>
                    <td><?php echo htmlspecialchars($row['ID_RECIBO_DETALLE'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['TIPO_EMPAQUE'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['NOMBRE_PRODUCTOS'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['PIEZAS'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['ALTO'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['ANCHO'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['LARGO'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['PESO'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['OBSERVACION'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['TRACKING'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="#" data-id="<?php echo htmlspecialchars($id_recibo, ENT_QUOTES, 'UTF-8'); ?>&id_detalle=<?php echo htmlspecialchars($row['ID_RECIBO_DETALLE'], ENT_QUOTES, 'UTF-8'); ?>" class="boton-editar">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="total-piezas">
        <strong>Cant.disp:</strong> <?php echo htmlspecialchars($total_piezas, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<!-- Paginación -->
<div class="pagination">
    <div><button data-page="1" <?php if ($page <= 1) echo 'disabled'; ?>><i class="fa-solid fa-angles-left"></i></button></div>
    <div><button data-page="<?php echo max(1, $page - 1); ?>" <?php if ($page <= 1) echo 'disabled'; ?>><i class="fa-solid fa-angle-left"></i></button></div>
    <div class="links">
        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
            <a href="?id=<?php echo $id_recibo; ?>&page=<?php echo $i; ?>" class="link <?php if ($i == $page) echo 'active'; ?>" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php } ?>
    </div>
    <div><button data-page="<?php echo min($total_pages, $page + 1); ?>" <?php if ($page >= $total_pages) echo 'disabled'; ?>><i class="fa-solid fa-angle-right"></i></button></div>
    <div><button data-page="<?php echo $total_pages; ?>" <?php if ($page >= $total_pages) echo 'disabled'; ?>><i class="fa-solid fa-angles-right"></i></button></div>
    <div>Página <?php echo $page; ?> de <?php echo $total_pages; ?></div>
</div>