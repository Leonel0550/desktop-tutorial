<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
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

//La suma y resta de la cantidad de producto que hay
$suma_piezas_query = $con->prepare("
SELECT SUM(PIEZAS) as total_piezas
FROM t_recibo_bodega_detalles
");
$suma_piezas_query->execute();
$total_piezas = $suma_piezas_query->fetch(PDO::FETCH_ASSOC)['total_piezas'];

//El contador de la cantidad de categorias que hay
$count_comando = $con->prepare("SELECT COUNT(*) as total FROM t_categorias");
$count_comando->execute();
$total = $count_comando->fetch(PDO::FETCH_ASSOC)['total'];

//El contador de la cantidad de producto que hay
$count_comando = $con->prepare("SELECT COUNT(*) as total_producto FROM t_productos");
$count_comando->execute();
$total_producto = $count_comando->fetch(PDO::FETCH_ASSOC)['total_producto'];

//El contador de la cantidad de proveedores que hay
$count_comando = $con->prepare("SELECT COUNT(*) as total_proveedor FROM t_proveedores");
$count_comando->execute();
$total_proveedor = $count_comando->fetch(PDO::FETCH_ASSOC)['total_proveedor'];

//El contador de la cantidad de historial que hay
$count_comando = $con->prepare("SELECT COUNT(*) as total_documento FROM t_historial_documentos");
$count_comando->execute();
$total_documento = $count_comando->fetch(PDO::FETCH_ASSOC)['total_documento'];

$count_comando = $con->prepare("SELECT COUNT(*) as total_saliente FROM t_saliente");
$count_comando->execute();
$total_saliente = $count_comando->fetch(PDO::FETCH_ASSOC)['total_saliente'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboards</title>
  <link rel="stylesheet" href="inicio.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>

<body>
  <?php include 'navbar.php'; ?>

  <section class="tablas_posicion">
    <div class="table_container">
      <div class="table">
        <div class="table_header">
          <h3>Página de Inicio</h3>

        </div>
        
        <div id="table-section">
          <div class="table_section">
            <div class="card-container">
              <div class="card">
                <div class="card-icon"><i class='bx bx-list-ul'></i></div>
                <h3>Categorias</h3>
                <p>Se encuentra cantidad de categorias que existe en la bodega.</p>
                <h3><?php echo $total; ?></h3>
                <a href="categoria.php" class="card-button">Ir a Categorias</a>
              </div>

              <div class="card">
                <div class="card-icon"><i class='bx bxl-product-hunt'></i></div>
                <h3>Productos</h3>
                <p>Se encuentran todos los productos que tiene disponible la bodega.</p>
                <h3><?php echo $total_producto; ?></h3>
                <a href="producto.php" class="card-button">Ir a Productos</a>
              </div>

              <div class="card">
                <div class="card-icon"><i class='bx bx-plus-circle'></i></div>
                <h3>Entrantes</h3>
                <p>Se realiza el proceso de entrante de productos en la bodega.</p>
                <h3><?php echo number_format($total_piezas); ?></h3>
                <a href="index_recibo.php" class="card-button">Ir a Entrantes</a>
              </div>

              <div class="card">
                <div class="card-icon"><i class='bx bx-minus-circle'></i></div>
                <h3>Salientes</h3>
                <p>Se realiza las salidas de productos y el lugar de destino.</p>
                <h3><?php echo $total_saliente; ?></h3>
                <a href="saliente.php" class="card-button">Ir a Salientes</a>
              </div>
              <div class="card">
                <div class="card-icon"><i class='bx bxs-truck'></i></div>
                <h3>Proveedores</h3>
                <p>Consulta los proveedores que han sido registrados.</p>
                <h3><?php echo $total_proveedor; ?></h3>
                <a href="index.php" class="card-button">Ver Proveedores</a>
              </div>
              <div class="card">
                <div class="card-icon"><i class='bx bxs-report'></i></div>
                <h3>Documentos</h3>
                <p>Se encuentra el historial de los entrantes y salientes.</p>
                <h3><?php echo $total_documento; ?></h3>
                <a href="documento.php" class="card-button">Ver Historial</a>
              </div>
              <table>
                <thead>
                  <tr>
                    <th><a href="#" class="sort" data-orderby="ID_HISTORIAL"># <i class="fa-solid fa-sort"></i></a></th>
                    <th><a href="#" class="sort" data-orderby="PRODUCTOS">Producto<i class="fa-solid fa-sort"></i></a></th>
                    <th><a href="#" class="sort" data-orderby="PIEZAS">Piezas <i class="fa-solid fa-sort"></i></a></th>
                    <th><a href="#" class="sort" data-orderby="FECHA">Fecha<i class="fa-solid fa-sort"></i></a></th>
                    <th><a href="#" class="sort" data-orderby="TIPO">Tipo <i class="fa-solid fa-sort"></i></a></th>

                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($resultado as $row) : ?>
                    <tr>
                      <td><?php echo $row['ID_HISTORIAL']; ?></td>
                      <td><?php echo $row['PRODUCTO']; ?></td>
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


          </div>
        <!-- Modal para Nuevo Recibo -->
        <div id="nuevoReciboModal" class="modal">
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <!-- El contenido de nuevo.php se cargará aquí -->
            </div>
        </div>

        <!-- Modal para Editar Recibo -->
        <div id="editarReciboModal" class="modal">
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <!-- El contenido de editar.php se cargará aquí -->
            </div>
        </div>
        <div id="toast-container"></div>

  </section>
  <script>src="js/script.js"</script>


</body>

</html>
