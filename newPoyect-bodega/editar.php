<?php

require_once 'config/database.php';

$db = new Database();
$con = $db->conectar();

$id = $_GET['id'];

$query = $con->prepare("SELECT NOMBRE, RUC, DV, DIRECCION, TELEFONO,CORREO, OBSERVACIONES,ESTADO	
FROM t_proveedores WHERE ID_PROVEEDOR=:id");
$query->execute(['id'=> $id]);
$row = $query->fetch(PDO::FETCH_ASSOC);


?>



        <div class="container-form">
            <form action="guarda.php" method="POST" autocomplete="off">
                <input type="hidden" id="id" name="id" value="<?php echo $id ?>">
                <h2>Nuevo Proveedores</h2>
                <div class="content">
                    <div class="input-box">
                        <label for="nombre"><i class="fas fa-user"></i> Nombre</label>
                        <input type="text" placeholder="Introduzca su nombre" id="nombre" name="nombre" value="<?php echo $row['NOMBRE']; ?>" required autofocus>
                    </div>
                    <div class="input-box">
                        <label for="ruc"><i class="fas fa-id-card"></i> RUC</label>
                        <input type="text" placeholder="Introduzca el RUC" id="ruc" name="ruc" value="<?php echo $row['RUC']; ?>" required>
                    </div>
                    <div class="input-box">
                        <label for="dv"><i class="fas fa-id-badge"></i> DV</label>
                        <input type="text" placeholder="Introduzca el DV" id="dv" name="dv" value="<?php echo $row['DV']; ?>" required>
                    </div>
                    <div class="input-box">
                        <label for="correo"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                        <input type="email" placeholder="Introduzca el correo" id="correo" name="correo" value="<?php echo $row['CORREO']; ?>" required>
                    </div>
                    <div class="input-box">
                        <label for="telefono"><i class="fas fa-phone"></i> Teléfono</label>
                        <input type="tel" placeholder="Introduzca el telefono" id="telefono" name="telefono" value="<?php echo $row['TELEFONO']; ?>" required>
                    </div>        
                    <div class="gender-category">
                    <span class="gender-title"><i class="fas fa-toggle-on"></i> Estado</span>
                    <select name="estado">
                        <option value="activo" <?php echo ($row['ESTADO'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo ($row['ESTADO'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                     </select>
                    </div>
                    <div class="input-box">
                       <label for="direccion"><i class="fas fa-map-marker-alt"></i> Dirección</label>
                       <textarea name="direccion" id="direccion" rows="8" placeholder="Introduzca su dirección" required><?php echo $row['DIRECCION']; ?></textarea>
                    </div>
                    <div class="input-box">
                        <label for="observaciones"><i class="fas fa-comment-dots"></i> Observaciones</label>
                        <textarea name="observaciones" id="observaciones" rows="8" placeholder="¿Alguna duda?"><?php echo $row['OBSERVACIONES']; ?></textarea>
                    </div>
                </div>
                <div class="button-container-1">
                    <a href="index.php" class="btn-1"> 
                    <i class="fa-solid fa-circle-xmark"></i>
                    </a>
                    <button type="submit"> <i class="fa-solid fa-plus"></i> </button>
                </div>

            </form>
        </div>
    

