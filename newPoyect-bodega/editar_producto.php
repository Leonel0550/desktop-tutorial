<?php

require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$id = $_GET['id'];

$query = $con->prepare("SELECT NOMBRE_PRODUCTO, DETALLES_CATEGORIA, ID_CATEGORIA	
FROM t_productos WHERE ID_PRODUCTOS=:id");
$query->execute(['id' => $id]);
$row = $query->fetch(PDO::FETCH_ASSOC);


$comandoCategorias = $con->prepare("SELECT ID_CATEGORIAS, NOMBRE_CATE FROM t_categorias");
$comandoCategorias->execute();
$categorias = $comandoCategorias->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="container-form">
    <form action="guardar_producto.php" method="POST" autocomplete="off">
        <h2>Nuevo Producto</h2>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="content">
            <div class="gender-category">
                <span class="gender-title"><i class="fa-solid fa-list"></i>Categoria:</span>
                <select id="categorias" name="categorias" required>
                    <option value="">Seleccione una categoria</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['ID_CATEGORIAS']; ?>" <?php echo ($row['ID_CATEGORIA'] == $categoria['ID_CATEGORIAS']) ? 'selected' : ''; ?>>
                            <?php echo $categoria['NOMBRE_CATE']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-box">
                <label for="producto"><i class="fa-brands fa-product-hunt"></i> Producto:</label>
                <input type="text" placeholder="Introduzca el nuevo producto" id="producto" name="producto" value="<?php echo $row['NOMBRE_PRODUCTO']; ?>" autofocus>
            </div>

            <div class="input-box">
                <label for="detalles_producto"><i class="fa-solid fa-clipboard"></i> Detalles:</label>
                <textarea name="detalles_producto" id="detalles_producto" rows="4" placeholder="¿Detalles que falten?"><?php echo htmlspecialchars($row['DETALLES_CATEGORIA'], ENT_QUOTES); ?></textarea>
            </div>
        </div>
        <div class="button-container-1">
            <a href="producto.php" class="btn-1">
            <i class="fa-solid fa-circle-xmark"></i>
            </a>
            <button type="submit"> <i class="fa-solid fa-pen-to-square"></i> </button>
        </div>

    </form>
</div>