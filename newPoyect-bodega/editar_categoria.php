<?php

require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$id = $_GET['id'];

$query = $con->prepare("SELECT NOMBRE_CATE	
FROM t_categorias WHERE ID_CATEGORIAS=:id");
$query->execute(['id'=> $id]);
$row = $query->fetch(PDO::FETCH_ASSOC);

?>

<div class="container-form">
    <form action="guardar_categoria.php" method="POST" autocomplete="off">
        <h2>Editar Categoria</h2>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="content">
            <div class="input-box">
                <label for="categoria"><i class="fas fa-user"></i> Categoria</label>
                <input type="text" placeholder="Introduzca su nombre" id="categoria" name="categoria" value="<?php echo $row['NOMBRE_CATE']; ?>" autofocus>
            </div>
        </div>
        <div class="button-container-1">
            <a href="categoria.php" class="btn-1">
            <i class="fa-solid fa-circle-xmark"></i>
            </a>
            <button type="submit"> <i class="fa-solid fa-pen-to-square"></i> </button>
        </div>

    </form>
</div>

