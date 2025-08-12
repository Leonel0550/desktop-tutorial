<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$offset = ($page - 1) * $limit;

$activo = 1;
$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'ID_PRODUCTOS';
$orderDir = isset($_GET['orderDir']) && strtolower($_GET['orderDir']) === 'desc' ? 'DESC' : 'ASC';

$comando = $con->prepare("
    SELECT 
        r.ID_PRODUCTOS, 
        r.NOMBRE_PRODUCTO, 
        r.DETALLES_CATEGORIA, 
        b.NOMBRE_CATE AS NOMBRE_CATEGORIA
    FROM t_productos r 
    JOIN t_categorias b ON r.ID_CATEGORIA = b.ID_CATEGORIAS
    ORDER BY $orderBy $orderDir
    LIMIT :limit OFFSET :offset
");


$comando->bindParam(':limit', $limit, PDO::PARAM_INT);
$comando->bindParam(':offset', $offset, PDO::PARAM_INT);
$comando->execute();
$resultado = $comando->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total de registros para la paginación
$count_comando = $con->prepare("SELECT COUNT(*) as total FROM t_productos");
$count_comando->execute();
$total = $count_comando->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);


$count_comando = $con->prepare("SELECT COUNT(*) as total_producto FROM t_productos");
$count_comando->execute();
$total_producto = $count_comando->fetch(PDO::FETCH_ASSOC)['total_producto'];

?>


<div class="table_section">
<!--<p>Total de Producto: <//?php echo $total_producto; ?></p> -->
    <table>
        <thead>
            <tr>
                <th><a href="#" class="sort" data-orderby="ID_PRODUCTOS"># <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="NOMBRE_CATEGORIA">Categorias<i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="NOMBRE_PRODUCTO">Productos<i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="DETALLES_CATEGORIA">Detalles de producto <i class="fa-solid fa-sort"></i></a></th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultado as $row) { ?>
                <tr>

                    <td><?php echo $row['ID_PRODUCTOS']; ?></td>
                    <td><?php echo $row['NOMBRE_CATEGORIA']; ?></td>
                    <td><?php echo $row['NOMBRE_PRODUCTO']; ?></td>
                    <td><?php echo $row['DETALLES_CATEGORIA']; ?></td>

                    <td>
                        <a href="#" data-id="<?php echo $row['ID_PRODUCTOS']; ?>" class="boton-editar" title="Editar Proveedor">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </td>

                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

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