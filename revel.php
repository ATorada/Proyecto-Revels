<?php
require_once('includes/autologin.inc.php');
require_once('includes/conexion.inc.php');

if (!isset($_GET['id'])) {
    header('Location: index.php');
}

//Comprueba que el revel exista
$conexion = conectar();
$consulta = $conexion->prepare("SELECT * FROM revels WHERE id = ?");
$consulta->execute([$_GET['id']]);
if ($consulta->rowCount() <= 0) {
    header('Location: index.php');
}
unset($consulta);
unset($conexion);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revel</title>
    <?php
    if (isset($_SESSION["usuario"])) {
        echo '<link rel="stylesheet" href="css/style.css">';
    } else {
        echo '<link rel="stylesheet" href="css/style.css">';
        echo '<link rel="stylesheet" href="css/nuevoUsuario.css">';
    }
    ?>
</head>

<body>
    <?php
    require_once('includes/cabecera.inc.php');


    if (isset($_GET['id'])) {
        //Muestra el revel recibido por GET
        $conexion = conectar();
        $revels = $conexion->prepare("SELECT * FROM revels WHERE id = ?");
        $revels->execute([$_GET['id']]);
        $revel = $revels->fetch(PDO::FETCH_ASSOC);
        echo "<div class='revel'>";
        echo '<h2>' . $revel['texto'] . '</h2>';
        //Comprueba si existe una imagen para el revel, sino pone una por defecto
        if (isset($_SESSION["usuario"]) && file_exists('img/revels/' . $revel['id'] . "_" . $_SESSION["usuario"]  . '_resized.jpg')) {
            echo '<img class="preview_foto" src="img/revels/' . $revel['id'] . "_" . $_SESSION["usuario"]  . '.jpg" alt="Imagen del revel">';
        } else {
            echo '<img class="preview_foto" src="img/placeholder.jpg" alt="revel_foto">';
        }

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
        <?php
        if (isset($_SESSION["usuario"])) {
        ?>

            <h1>¡Deja tu comentario! <br>📸</h1><br>
            <form action="comment.php" method="post">
                <label for="comentario"><b>Comentario</b></label>
                <input type="text" name="comentario" id="comentario" required>
                <input type="hidden" value="<?= $_GET['id'] ?>" name="id" id="id">
                <input class="boton" type="submit" value="Comentar">
            </form>
        <?php
        } else {
        ?>
            <h1>¡Regístrate para comentar! <br>📸</h1><br>
        <?php
        }
        ?>
    </div>
</body>

</html>