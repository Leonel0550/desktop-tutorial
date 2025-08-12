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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada de Producto</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <section class="tablas_posicion">
        <div class="table_container">
            <div class="table">
                <div class="table_header">
                    <h3>Historial de entrantes y salientes</h3>
                    <div>
                        <a href="#" id="nuevoReciboBtn">
                        </a>
                    </div>
                </div>
                <div id="table-section">
                    <div class="table_section">
                    <!--<p>Total de documento: <//?php echo $total_documento; ?></p>-->
                        <table>
                            <thead>
                                <tr>
                                    <th><a href="#" class="sort" data-orderby="ID_HISTORIAL"># <i class="fa-solid fa-sort"></i></a></th>
                                    <th><a href="#" class="sort" data-orderby="ID_RECIBO_DETALLE"># Entrante <i class="fa-solid fa-sort"></i></a></th>
                                    <th><a href="#" class="sort" data-orderby="ID_SALIENTE"># Saliente <i class="fa-solid fa-sort"></i></a></th>
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
                                        <td><?php echo $row['ID_RECIBO_DETALLE']; ?></td>
                                        <td><?php echo $row['ID_SALIENTE']; ?></td>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {

const nuevoReciboBtn = document.getElementById('nuevoReciboBtn');
const nuevoModal = document.getElementById('nuevoReciboModal');
const editarModal = document.getElementById('editarReciboModal');
const closeModalBtns = document.querySelectorAll('.modal-close');
let currentOrderBy = 'ID_HISTORIAL';
let currentOrderDir = 'ASC';

nuevoReciboBtn.addEventListener('click', function(event) {
    event.preventDefault();
    openModal(nuevoModal, 'nuevo_recibo.php');
});

function openModal(modal, url) {
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 15);
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

document.querySelectorAll('.boton-editar').forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        const id = button.getAttribute('data-id');
        openModal(editarModal, 'editar_recibo.php?id=' + id);
    });
});

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
    fetch(`pagination_documento.php?page=${page}&limit=15&orderBy=${orderBy}&orderDir=${orderDir}`)
        .then(response => response.text())
        .then(data => {
            const tableSection = document.getElementById('table-section');
            tableSection.innerHTML = data;

            // Actualizar el icono 
            updateSortIcons();

            tableSection.querySelectorAll('.boton-editar').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const id = this.getAttribute('data-id');
                    openModal(editarModal, 'editar_recibo.php?id=' + id);
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