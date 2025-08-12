<?php
// Conexión a la base de datos y consultas para obtener bodegas y proveedores
require_once 'config/database.php';

?>

<div class="container-form">
    <form action="guardar_categoria.php" method="POST" autocomplete="off">
        <h2>Nueva Categoria</h2>
        <div class="content">
            <div class="input-box">
                <label for="categoria"><i class="fa-solid fa-list"></i>Categoria</label>
                <input type="text" placeholder="Introduzca una nueva categoria" id="categoria" name="categoria" required autofocus>
            </div>
        </div>
        <div class="button-container-1">
            <a href="categoria.php" class="btn-1">
            <i class="fa-solid fa-circle-xmark"></i>
            </a>
            <button type="submit"> <i class="fa-solid fa-plus"></i> </button>
        </div>

    </form>
</div>