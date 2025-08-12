<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$offset = ($page - 1) * $limit;

// Definir las columnas permitidas para el ordenamiento
$validColumns = ['ID_HISTORIAL', 'ID_RECIBO_DETALLE', 'ID_SALIENTE', 'PRODUCTOS', 'PIEZAS', 'FECHA', 'TIPO'];
$orderBy = isset($_GET['orderBy']) && in_array($_GET['orderBy'], $validColumns) ? $_GET['orderBy'] : 'ID_HISTORIAL';
$orderDir = isset($_GET['orderDir']) && strtolower($_GET['orderDir']) === 'desc' ? 'DESC' : 'ASC';

// Consulta SQL con limit y offset, uniendo la tabla de productos
$comando = $con->prepare("
    SELECT 
      h.ID_HISTORIAL,
      h.ID_RECIBO_DETALLE,
      h.ID_SALIENTE,
      p.NOMBRE_PRODUCTO AS PRODUCTO,  -- Cambiado de ID_PRODUCTOS a NOMBRE
      h.PIEZAS,
      h.FECHA,
      h.TIPO
    FROM t_historial_documentos h
    JOIN t_productos p ON h.PRODUCTOS = p.ID_PRODUCTOS  -- Unión con la tabla de productos
    ORDER BY $orderBy $orderDir
    LIMIT :limit OFFSET :offset
");

$comando->bindParam(':limit', $limit, PDO::PARAM_INT);
$comando->bindParam(':offset', $offset, PDO::PARAM_INT);
$comando->execute();
$resultado = $comando->fetchAll(PDO::FETCH_ASSOC);

// Contar el total de registros
$count_comando = $con->prepare("SELECT COUNT(*) as total FROM t_historial_documentos");
$count_comando->execute();
$total = $count_comando->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

//El contador de la cantidad de historial que hay
$count_comando = $con->prepare("SELECT COUNT(*) as total_documento FROM t_historial_documentos");
$count_comando->execute();
$total_documento = $count_comando->fetch(PDO::FETCH_ASSOC)['total_documento'];
?>

<!-- Aquí empieza el HTML de la tabla -->
<div class="table_section">
<!--<p>Total de documento: <//?php echo $total_documento; ?></p>-->
    <table>
        <thead>
            <tr>
                <th><a href="#" class="sort" data-orderby="ID_HISTORIAL"># <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="ID_RECIBO_DETALLE"># Entrante <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="ID_SALIENTE"># Saliente <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="PRODUCTO">Producto<i class="fa-solid fa-sort"></i></a></th> <!-- Cambiado a PRODUCTO -->
                <th><a href="#" class="sort" data-orderby="PIEZAS">Piezas <i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="FECHA">Fecha<i class="fa-solid fa-sort"></i></a></th>
                <th><a href="#" class="sort" data-orderby="TIPO">Tipo <i class="fa-solid fa-sort"></i></a></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultado as $row) : ?>
                <tr>
                    <td><?php echo $row['ID_HISTORIAL']; ?></td>
                    <td><?php echo $row['ID_RECIBO_DETALLE']; ?></td>
                    <td><?php echo $row['ID_SALIENTE']; ?></td>
                    <td><?php echo $row['PRODUCTO']; ?></td> <!-- Cambiado a PRODUCTO -->
                    <td><?php echo $row['PIEZAS']; ?></td>
                    <td><?php echo $row['FECHA']; ?></td>
                    <td>
                        <?php if ($row['TIPO'] === 'Entrante') { ?>
                            <i class="fa-solid fa-plus icon-activo"></i>
                        <?php } else { ?>
                            <i class="fa-solid fa-minus icon-inactivo"></i>
                        <?php } ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
