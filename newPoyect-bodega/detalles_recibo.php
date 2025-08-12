<?php
require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

// Obtener el ID del recibo desde la URL
$id_recibo = isset($_GET['id']) ? $_GET['id'] : die('ID de recibo no especificado.');

// Preparar y ejecutar la consulta para obtener los detalles del recibo
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
    WHERE r.ID_RECIBO = ?
");
try {
    $comando->execute([$id_recibo]);
    $recibo = $comando->fetch(PDO::FETCH_ASSOC);
    if (!$recibo) {
        die('Recibo no encontrado.');
    }
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Recibo</title>
    <link rel="stylesheet" href="detalles_recibo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <section class="tablas_posicion">
        <div class="table_container">
            <div class="container">
                <div class="accordion">
                    <div class="accordion-item">
                        <button class="accordion-header" aria-expanded="false" aria-controls="description-content">
                            <h1>Descripción del Recibo</h1>
                            <i class="fa fa-chevron-down"></i>
                        </button>
                        <div id="description-content" class="accordion-content" role="region" aria-labelledby="description-header">
                            <div class="info-grid">
                                <div><strong>ID Recibo:</strong> <?php echo $recibo['ID_RECIBO']; ?></div>
                                <div><strong>Fecha Recepción:</strong> <?php echo $recibo['FECHA_RECEPCION']; ?></div>
                                <div><strong>Estado Empaque:</strong> <?php echo $recibo['ESTADO_EMPAQUE']; ?></div>
                                <div><strong>Estado Producto:</strong> <?php echo $recibo['ESTADO_PRODUCTO']; ?></div>
                                <div><strong>Recibido Por:</strong> <?php echo $recibo['RECIBIDO_POR']; ?></div>
                                <div><strong>Nombre Bodega:</strong> <?php echo $recibo['NOMBRE_BODEGA']; ?></div>
                                <div><strong>Nombre Proveedor:</strong> <?php echo $recibo['NOMBRE_PROVEEDOR']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección para Detalles del Recibo -->
            <div class="table">
                <div class="table_header">
                    <h3>Detalles de Recibo de Bodega</h3>
                    <div>
                        <a href="#" id="nuevoReciboBtn" data-id="<?php echo htmlspecialchars($recibo['ID_RECIBO'], ENT_QUOTES, 'UTF-8'); ?>" class="add_new">
                            <i class="fa-solid fa-user-plus"></i> Agregar
                        </a>
                    </div>
                </div>
                <div id="table-section">
                    <?php include 'pagination_detalles.php'; ?>
                </div>
            </div>
        </div>

        <!-- Modal para Nuevo Recibo -->
        <div id="nuevoReciboModal" class="modal">
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <!-- El contenido de nuevo_detalles.php se cargará aquí -->
            </div>
        </div>

        <!-- Modal para Editar Recibo -->
        <div id="editarReciboModal" class="modal">
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <!-- El contenido de editar_detalles.php se cargará aquí -->
            </div>
        </div>

        <!-- Modal para Lector de Código de Barras -->
        <div id="trackingReciboModal" class="modal">
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <h2>Escanea el Código de Barras</h2>
                <div id="camera" style="width: 100%"></div>
                <div class="input-box">
                    <label for="tracking"><i class="fa-solid fa-barcode"></i> Tracking</label>
                    <textarea name="tracking" id="tracking" rows="4" placeholder="Codigo de barra"></textarea>
                </div>
                <button id="save-tracking" class="btn">Guardar Tracking</button>
            </div>
        </div>
        <div id="toast-container"></div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const nuevoReciboBtn = document.getElementById('nuevoReciboBtn');
        const nuevoModal = document.getElementById('nuevoReciboModal');
        const editarModal = document.getElementById('editarReciboModal');
        const trackingModal = document.getElementById('trackingReciboModal');
        const closeModalBtns = document.querySelectorAll('.modal-close');
        let currentOrderBy = 'ID_RECIBO_DETALLE';
        let currentOrderDir = 'ASC';

        // Función para abrir el modal de nuevo recibo
        nuevoReciboBtn.addEventListener('click', function(event) {
            event.preventDefault();
            const idRecibo = this.getAttribute('data-id');
            nuevoModal.style.display = 'flex';
            setTimeout(() => nuevoModal.classList.add('show'), 10);
            const modalContent = nuevoModal.querySelector('.modal-content');
            modalContent.innerHTML = '';
            fetch('nuevo_detalles.php?id=' + idRecibo)
                .then(response => response.text())
                .then(data => {
                    modalContent.innerHTML = data;
                    modalContent.querySelector('.modal-close').addEventListener('click', function() {
                        closeModal(nuevoModal);
                    });
                })
                .catch(error => console.error('Error:', error));
        });

        // Función para cerrar el modal
        function closeModal(modal) {
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 300);
        }

        // Cerrar modales al hacer clic en el botón de cierre o fuera del contenido del modal
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                closeModal(btn.closest('.modal'));
            });
        });

        [nuevoModal, editarModal, trackingModal].forEach(modal => {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

        // Función para abrir el modal de edición
        function openEditModal(id) {
            const [reciboId, detalleId] = id.split('&id_detalle=');
            fetch(`editar_detalles.php?id=${reciboId}&id_detalle=${detalleId}`)
                .then(response => response.text())
                .then(data => {
                    const modalContent = editarModal.querySelector('.modal-content');
                    modalContent.innerHTML = data;
                    editarModal.style.display = 'flex';
                    setTimeout(() => editarModal.classList.add('show'), 10);
                    modalContent.querySelector('.modal-close').addEventListener('click', function() {
                        closeModal(editarModal);
                    });
                })
                .catch(error => console.error('Error:', error));
        }

// Función para abrir el modal de seguimiento
function openTrackingModal(id) {
    const [reciboId, detalleId] = id.split('&id_detalle=');
    fetch(`codigo_barra.php?id=${reciboId}&id_detalle=${detalleId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text(); // o `response.json()` si el archivo PHP devuelve JSON
        })
        .then(data => {
            // Aquí se puede hacer algo con la respuesta, si es necesario
            // Ejemplo: actualizar el contenido del modal
            const modalContent = trackingModal.querySelector('.modal-content');
            modalContent.innerHTML = data;

            // Mostrar el modal
            trackingModal.style.display = 'flex';
            setTimeout(() => trackingModal.classList.add('show'), 10);

            // Inicializar el lector de códigos de barras
            startBarcodeScanner();

            // Configurar el botón de cierre
            modalContent.querySelector('.modal-close').addEventListener('click', function() {
                closeModal(trackingModal);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            // Puedes mostrar un mensaje de error al usuario aquí
        });
}
        // Función para inicializar el lector de códigos de barras
        
        function startBarcodeScanner() {
            const quaggaConf = {
                inputStream: {
                    type: "LiveStream",
                    target: document.querySelector("#camera"),
                    constraints: {
                        width: { min: 640 },
                        height: { min: 480 },
                        facingMode: "environment"
                    }
                },
                decoder: {
                    readers: ['code_128_reader']
                }
            };

            Quagga.init(quaggaConf, function (err) {
                if (err) {
                    console.log(err);
                    return;
                }
                Quagga.start();
            });

            Quagga.onDetected(function (result) {
                document.getElementById('barcode-value').value = result.codeResult.code;
                Quagga.stop(); // Detener el escáner después de detectar un código
            });
        }

        // Reasignar eventos a los botones de edición
        function setupEditEventListeners() {
            document.querySelectorAll('.boton-editar').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const id = this.getAttribute('data-id');
                    openEditModal(id);
                });
            });
        }

        // Reasignar eventos a los botones de seguimiento
        function setupTrackingEventListeners() {
            document.querySelectorAll('.boton-tracking').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const id = this.getAttribute('data-id');
                    openTrackingModal(id);
                });
            });
        }

        // Actualizar icono cambiante
        function loadPage(page, orderBy = currentOrderBy, orderDir = currentOrderDir) {
            fetch(`pagination_detalles.php?page=${page}&limit=15&orderBy=${orderBy}&orderDir=${orderDir}&id=${<?php echo json_encode($id_recibo); ?>}`)
                .then(response => response.text())
                .then(data => {
                    const tableSection = document.getElementById('table-section');
                    tableSection.innerHTML = data;

                    // Actualizar el icono
                    updateSortIcons();

                    // Reasignar eventos a los botones
                    setupEventListeners();
                    setupEditEventListeners(); // Asegúrate de reasignar eventos de edición
                    setupTrackingEventListeners(); // Asegúrate de reasignar eventos de seguimiento

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
                });
        }

        function setupEventListeners() {
            document.querySelectorAll('.sort').forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const orderBy = this.getAttribute('data-orderby');
                    let orderDir = 'ASC';
                    if (orderBy === currentOrderBy && currentOrderDir === 'ASC') {
                        orderDir = 'DESC';
                    }
                    currentOrderBy = orderBy;
                    currentOrderDir = orderDir;
                    loadPage(1, orderBy, orderDir); // Cambia a la primera página al ordenar
                });
            });
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

        loadPage(1, currentOrderBy, currentOrderDir); // Cargar la primera página al inicio

        // Actualizar la paginación después de hacer clic en los botones
        document.querySelectorAll('.pagination button').forEach(button => {
            button.addEventListener('click', function() {
                const page = this.getAttribute('data-page');
                const idRecibo = '<?php echo $id_recibo; ?>';
                window.location.href = `detalles_recibo.php?id=${idRecibo}&page=${page}`;
            });
        });

        var accordionHeaders = document.querySelectorAll('.accordion-header');
        accordionHeaders.forEach(header => {
            header.addEventListener('click', function() {
                var content = this.nextElementSibling;
                var icon = this.querySelector('.fa');

                if (content.style.display === 'block') {
                    content.style.display = 'none';
                    icon.classList.remove('active');
                } else {
                    content.style.display = 'block';
                    icon.classList.add('active');
                }

                accordionHeaders.forEach(otherHeader => {
                    if (otherHeader !== this) {
                        var otherContent = otherHeader.nextElementSibling;
                        var otherIcon = otherHeader.querySelector('.fa');
                        otherContent.style.display = 'none';
                        otherIcon.classList.remove('active');
                    }
                });
            });
        });
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