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

// Consulta SQL con limit y offset
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salida de Producto</title>
    <link rel="stylesheet" href="recibo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <section class="tablas_posicion">
        <div class="table_container">
            <div class="table">
                <div class="table_header">
                    <h3>Saliente de Bodega</h3>
                    <div>
                    <a href="#" id="nuevoReciboBtn" >
                            
                        </a>
                    </div>
                </div>
                <div id="table-section">
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
                </div>
            </div>

        </div>

        <div id="toast-container"></div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentOrderBy = 'ID_RECIBO';
            let currentOrderDir = 'ASC';


            function updateSortIcons() {
                const sortLinks = document.querySelectorAll('.sort');
                sortLinks.forEach(link => {
                    const icon = link.querySelector('i');
                    if (link.getAttribute('data-orderby') === currentOrderBy) {
                        link.classList.add(currentOrderDir.toLowerCase());
                        icon.classList.remove('fa-sort');
                        icon.classList.add(currentOrderDir === 'ASC' ? 'fa-sort-up' : 'fa-sort-down');
                    } else {
                        link.classList.remove('asc', 'desc');
                        icon.classList.remove('fa-sort-up', 'fa-sort-down');
                        icon.classList.add('fa-sort');
                    }
                });
            }

            function loadPage(page, orderBy = currentOrderBy, orderDir = currentOrderDir) {
                fetch(`pagination_saliente.php?page=${page}&limit=15&orderBy=${orderBy}&orderDir=${orderDir}`)
                    .then(response => response.text())
                    .then(data => {
                        const tableSection = document.getElementById('table-section');
                        tableSection.innerHTML = data;

                        // Actualizar el icono 
                        updateSortIcons();

                        tableSection.querySelectorAll('.pagination button').forEach(link => {
                            link.addEventListener('click', function(event) {
                                event.preventDefault();
                                const page = this.getAttribute('data-page');
                                loadPage(page);
                            });
                        });

                        tableSection.querySelectorAll('.pagination .link').forEach(link => {
                            link.addEventListener('click', function(event) {
                                event.preventDefault();
                                const page = this.getAttribute('data-page');
                                loadPage(page);
                            });
                        });

                        tableSection.querySelectorAll('.sort').forEach(link => {
                            link.addEventListener('click', function(event) {
                                event.preventDefault();
                                const orderBy = this.getAttribute('data-orderby');
                                let orderDir = 'ASC';
                                if (orderBy === currentOrderBy && currentOrderDir === 'ASC') {
                                    orderDir = 'DESC';
                                }
                                currentOrderBy = orderBy;
                                currentOrderDir = orderDir;
                                loadPage(page, orderBy, orderDir);
                            });
                        });
                    });
            }

            // Inicializar la carga de la página actual
            loadPage(<?php echo $page; ?>, '<?php echo htmlspecialchars($orderBy); ?>', '<?php echo htmlspecialchars($orderDir); ?>');
        });

        function showToast(message) {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerText = message;

            toastContainer.appendChild(toast);

            // Mostrar el toast
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            // Ocultar el toast después de 3 segundos
            setTimeout(() => {
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => {
                    toastContainer.removeChild(toast);
                }, 300);
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const toastMessage = urlParams.get('toast');
            if (toastMessage) {
                showToast(decodeURIComponent(toastMessage));
            }
        });
    </script>
</body>

</html>

