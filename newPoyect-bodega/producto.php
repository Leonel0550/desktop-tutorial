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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <section class="tablas_posicion">
        <div class="table_container">
            <div class="table">
                <div class="table_header">
                    <h3>Lista de todos los Productos</h3>
                    <div>
                        <a href="#" id="nuevoReciboBtn" class="add_new" title="Agregar una Nueva Categoria">
                            <i class="fa-solid fa-user-plus"></i> Agregar
                        </a>
                    </div>
                </div>
                <div id="table-section">
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

        <!-- Modal para Ver Detalles -->
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nuevoReciboBtn = document.getElementById('nuevoReciboBtn');
            const nuevoModal = document.getElementById('nuevoReciboModal');
            const editarModal = document.getElementById('editarReciboModal');
            const closeModalBtns = document.querySelectorAll('.modal-close');
            let currentOrderBy = 'ID_PRODUCTOS';
            let currentOrderDir = 'ASC';

            nuevoReciboBtn.addEventListener('click', function(event) {
                event.preventDefault();
                openModal(nuevoModal, 'nuevo_producto.php');
            });

            function openModal(modal, url) {
                modal.style.display = 'flex';
                setTimeout(() => modal.classList.add('show'), 10);
                const modalContent = modal.querySelector('.modal-content');
                modalContent.innerHTML = '';
                fetch(url)
                    .then(response => response.text())
                    .then(data => {
                        modalContent.innerHTML = data;
                        modalContent.querySelector('.modal-close').addEventListener('click', function() {
                            closeModal(modal);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }

            function closeModal(modal) {
                modal.classList.remove('show');
                setTimeout(() => modal.style.display = 'none', 300);
            }

            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    closeModal(btn.closest('.modal'));
                });
            });

            [nuevoModal, editarModal].forEach(modal => {
                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        closeModal(modal);
                    }
                });
            });

            function openEditModal(id) {
                openModal(editarModal, 'editar_producto.php?id=' + id);
            }

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
                fetch(`pagination_producto.php?page=${page}&limit=15&orderBy=${orderBy}&orderDir=${orderDir}`)
                    .then(response => response.text())
                    .then(data => {
                        const tableSection = document.getElementById('table-section');
                        tableSection.innerHTML = data;

                        // Actualizar íconos de ordenación
                        updateSortIcons();

                        tableSection.querySelectorAll('.boton-editar').forEach(button => {
                            button.addEventListener('click', function(event) {
                                event.preventDefault();
                                const id = this.getAttribute('data-id');
                                openEditModal(id);
                            });
                        });

                        tableSection.querySelectorAll('.boton-ver').forEach(button => {
                            button.addEventListener('click', function(event) {
                                event.preventDefault();
                                const id = this.getAttribute('data-id');
                                openDetallesModal(id);
                            });
                        });

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

            document.querySelectorAll('.pagination button').forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const page = this.getAttribute('data-page');
                    loadPage(page);
                });
            });

            document.querySelectorAll('.pagination .link').forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const page = this.getAttribute('data-page');
                    loadPage(page);
                });
            });

            loadPage(1);
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
    <div id="toast-container"></div>
    <script src="js/script.js"></script>
</body>

</html>

