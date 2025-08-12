<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$offset = ($page - 1) * $limit;

// Definir las columnas permitidas para el ordenamiento
$validColumns = ['ID_RECIBO', 'FECHA_RECEPCION', 'ESTADO_EMPAQUE', 'ESTADO_PRODUCTO', 'RECIBIDO_POR', 'NOMBRE_BODEGA', 'NOMBRE_PROVEEDOR'];
$orderBy = isset($_GET['orderBy']) && in_array($_GET['orderBy'], $validColumns) ? $_GET['orderBy'] : 'ID_RECIBO';
$orderDir = isset($_GET['orderDir']) && strtolower($_GET['orderDir']) === 'desc' ? 'DESC' : 'ASC';

// Consulta SQL para obtener los datos paginados
$comando = $con->prepare("
    SELECT 
        r.ID_RECIBO, 
        r.FECHA_RECEPCION, 
        r.ESTADO_EMPAQUE, 
        r.ESTADO_PRODUCTO, 
        r.RECIBIDO_POR,
        b.NOMBRE AS NOMBRE_BODEGA,
        p.NOMBRE AS NOMBRE_PROVEEDOR
    FROM t_recibo_bodega r 
    JOIN t_stp_bodega b ON r.ID_BODEGA = b.ID_BODEGA
    JOIN t_proveedores p ON r.ID_PROVEEDOR = p.ID_PROVEEDOR
    ORDER BY $orderBy $orderDir
    LIMIT :limit OFFSET :offset
");

$comando->bindParam(':limit', $limit, PDO::PARAM_INT);
$comando->bindParam(':offset', $offset, PDO::PARAM_INT);
$comando->execute();
$resultado = $comando->fetchAll(PDO::FETCH_ASSOC);

// Contar el total de registros
$count_comando = $con->prepare("SELECT COUNT(*) as total FROM t_recibo_bodega");
$count_comando->execute();
$total = $count_comando->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

// Consulta SQL para obtener la suma total de las piezas
$suma_piezas_query = $con->prepare("
    SELECT SUM(PIEZAS) as total_piezas
    FROM t_recibo_bodega_detalles
");
$suma_piezas_query->execute();
$total_piezas = $suma_piezas_query->fetch(PDO::FETCH_ASSOC)['total_piezas'];
?>
<!-- Aquí empieza el HTML de la tabla -->
<div class="table_section">
    <table>
        <thead>
            <tr>
                <th><a href="#" class="sort" data-orderby="ID_RECIBO"># <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="FECHA_RECEPCION">Fecha Recibido <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="ESTADO_EMPAQUE">Calidad del Empaque <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="ESTADO_PRODUCTO">Calidad del Producto <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="RECIBIDO_POR">Recibido por <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="NOMBRE_BODEGA">Ubicación de Almacén <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="NOMBRE_PROVEEDOR">Productor <i class="fa-solid fa-sort"></i></a></th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultado as $row) : ?>
                <tr>
                    <td><?php echo $row['ID_RECIBO']; ?></td>
                    <td><?php echo $row['FECHA_RECEPCION']; ?></td>
                    <td><?php echo $row['ESTADO_EMPAQUE']; ?></td>
                    <td><?php echo $row['ESTADO_PRODUCTO']; ?></td>
                    <td><?php echo $row['RECIBIDO_POR']; ?></td>
                    <td><?php echo $row['NOMBRE_BODEGA']; ?></td>
                    <td><?php echo $row['NOMBRE_PROVEEDOR']; ?></td>
                    <td>
                        <a href="saliente_detalles.php?id=<?php echo $row['ID_RECIBO']; ?>" class="boton-detalles" title="Detalles de recibo">
                            <i class="fa-solid fa-circle-info"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="total-piezas">
    <strong>Total de Piezas: </strong><?php echo number_format($total_piezas); ?>
</div>

<!-- Paginación -->
<div class="pagination">
    <div><button data-page="1" <?php if ($page <= 1) echo 'disabled'; ?>><i class="fa-solid fa-angles-left"></i></button></div>
    <div><button data-page="<?php echo max(1, $page - 1); ?>" <?php if ($page <= 1) echo 'disabled'; ?>><i class="fa-solid fa-angle-left"></i></button></div>
    <div class="links">
        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
            <a href="#" class="link <?php if ($i == $page) echo 'active'; ?>" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php } ?>
    </div>
    <div><button data-page="<?php echo min($total_pages, $page + 1); ?>" <?php if ($page >= $total_pages) echo 'disabled'; ?>><i class="fa-solid fa-angle-right"></i></button></div>
    <div><button data-page="<?php echo $total_pages; ?>" <?php if ($page >= $total_pages) echo 'disabled'; ?>><i class="fa-solid fa-angles-right"></i></button></div>
    <div>Página <?php echo $page; ?> de <?php echo $total_pages; ?></div>
</div>



