<?php
// Conexión a la base de datos y consultas para obtener bodegas y proveedores
require_once 'config/database.php';

?>

<div class="container-form">
    <form action="guarda.php" method="POST" autocomplete="off">
        <h2>Nuevo Proveedores</h2>
        <div class="content">
            <div class="input-box">
                <label for="nombre"><i class="fas fa-user"></i> Nombre</label>
                <input type="text" placeholder="Introduzca su nombre" id="nombre" name="nombre" required autofocus>
            </div>
            <div class="input-box">
                <label for="ruc"><i class="fas fa-id-card"></i> RUC</label>
                <input type="text" placeholder="Introduzca el RUC" id="ruc" name="ruc" required>
            </div>
            <div class="input-box">
                <label for="dv"><i class="fas fa-id-badge"></i> DV</label>
                <input type="text" placeholder="Introduzca el DV" id="dv" name="dv" required>
            </div>
            <div class="input-box">
                <label for="correo"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                <input type="email" placeholder="Introduzca el correo" id="correo" name="correo" required>
            </div>
            <div class="input-box">
                <label for="telefono"><i class="fas fa-phone"></i> Teléfono</label>
                <input type="tel" placeholder="Introduzca el teléfono" id="telefono" name="telefono" required>
            </div>
            <div class="gender-category">
                <span class="gender-title"><i class="fas fa-toggle-on"></i> Estado</span>
                <select name="estado">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div class="input-box">
                <label for="direccion"><i class="fas fa-map-marker-alt"></i> Dirección</label>
                <textarea name="direccion" id="direccion" rows="8" placeholder="Introduzca su dirección" required></textarea>
            </div>
            <div class="input-box">
                <label for="observaciones"><i class="fas fa-comment-dots"></i> Observaciones</label>
                <textarea name="observaciones" id="observaciones" rows="8" placeholder="¿Alguna duda?"></textarea>
            </div>
        </div>
        <div class="button-container-1">
            <a href="index.php" class="btn-1">
            <i class="fa-solid fa-circle-xmark"></i>
            </a>
            <button type="submit"> <i class="fa-solid fa-pen-to-square"></i> </button>
        </div>

    </form>
</div>