<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$offset = ($page - 1) * $limit;

$activo = 1;
$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'ID_CATEGORIAS';
$orderDir = isset($_GET['orderDir']) && strtolower($_GET['orderDir']) === 'desc' ? 'DESC' : 'ASC';

$comando = $con->prepare("SELECT ID_CATEGORIAS, NOMBRE_CATE FROM t_categorias ORDER BY $orderBy $orderDir LIMIT :limit OFFSET :offset");
$comando->bindParam(':limit', $limit, PDO::PARAM_INT);
$comando->bindParam(':offset', $offset, PDO::PARAM_INT);
$comando->execute();
$resultado = $comando->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total de registros para la paginación
$count_comando = $con->prepare("SELECT COUNT(*) as total FROM t_categorias");
$count_comando->execute();
$total = $count_comando->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

$count_comando = $con->prepare("SELECT COUNT(*) as total FROM t_categorias");
$count_comando->execute();
$total = $count_comando->fetch(PDO::FETCH_ASSOC)['total'];
?>


<div class="table_section">
<!--<p>Total de Categorías: <//?php echo $total; ?></p> -->
    <table>
        <thead>
            <tr>
                <th><a href="#" class="sort" data-orderby="ID_CATEGORIAS"># <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="NOMBRE_CATE">Categorias <i class="fa-solid fa-sort"></i></a></th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultado as $row) { ?>
                <tr>

                    <td><?php echo $row['ID_CATEGORIAS']; ?></td>
                    <td><?php echo $row['NOMBRE_CATE']; ?></td>
                    <td>
                        <a href="#" data-id="<?php echo $row['ID_CATEGORIAS']; ?>" class="boton-editar" title="Editar Proveedor">
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