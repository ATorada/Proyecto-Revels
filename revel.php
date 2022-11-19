<?php
require_once('includes/conexion.inc.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
    require_once('includes/cabecera.inc.php');
    $registrado = true;
    
    if ($registrado) {
        if(isset($_GET['id'])){
            //Muestra el revel recibido por GET
            $conexion = conectar();
            $revels = $conexion->prepare("SELECT * FROM revels WHERE id = ?");
            $revels->execute([$_GET['id']]);
            $revel = $revels->fetch(PDO::FETCH_ASSOC);
            echo "<div class='revel'>";
            echo '<h2>' . $revel['texto'] . '</h2>';
            echo '<img class="preview_foto" src="img/placeholder.jpg" alt="revel_foto">';

            $usuario = $conexion->query("SELECT usuario FROM users WHERE id = " . $revel['userid']);
            $usuario = $usuario->fetch(PDO::FETCH_ASSOC);

            echo '<p>Autor: <span class="resaltado">' . $usuario['usuario'] . '</span></p>';
            echo '<p>Fecha: <span class="resaltado">' . $revel['fecha'] . '</span></p>';

            //Muestra los comentarios del revel
            $comentarios = $conexion->prepare("SELECT * FROM comments WHERE revelid = ?");
            $comentarios->execute([$_GET['id']]);
            while ($comentario = $comentarios->fetch(PDO::FETCH_ASSOC)) {
                $nombreUsuario = $conexion->query("SELECT usuario FROM users WHERE id = " . $comentario['userid']);
                echo '<div class="comentario">';
                echo '<p><span class="resaltado">' . $nombreUsuario->fetch(PDO::FETCH_ASSOC)['usuario'] . '</span> dice:</p>';
                echo '<p>' . $comentario['texto'] . '</p>';
                echo '</div>';
            }
            echo "</div>";
            unset($comentarios);
            unset($usuario);
            unset($revels);
            unset($conexion);
        }
    ?>
    <div class="formulario">
        <h1>¡Deja tu comentario! <br>📸</h1><br>
        <form action="comment.php" method="post">
            <label for="comentario"><b>Comentario</b></label>
            <input type="text" name="comentario" id="comentario" required>

            <input class="registrar" type="submit" value="Comentar">
        </form>
    </div>
    <?php
    } else {
        echo "<h1>Debes estar registrado para ver los revels</h1>";
    }
    ?>
</body>
</html>