<?php
require_once('includes/autologin.inc.php');
require_once('includes/conexion.inc.php');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario_id'] != $_GET['id']) {
    header('Location: index.php');
}

if (count($_POST) > 0) {
    if ($_SESSION['usuario_id'] == $_POST['id']) {
        if (isset($_POST['confirmar'])) {
            $conexion = conectar();
            if (!is_null($conexion)) {
                //Borra los revels y sus comentarios
                $consulta = $conexion->prepare("DELETE FROM comments WHERE revelid IN (SELECT id FROM revels WHERE userid = ?);");
                $consulta->execute([$_SESSION['usuario_id']]);
                $consulta = $conexion->prepare("DELETE FROM revels WHERE userid = ?;");
                $consulta->execute([$_SESSION['usuario_id']]);
                //Borra los comentarios del usuario
                $consulta = $conexion->prepare("DELETE FROM comments WHERE userid = ?;");
                $consulta->execute([$_SESSION['usuario_id']]);
                //Borra el usuario
                $consulta = $conexion->prepare("DELETE FROM users WHERE id = ?;");
                $consulta->execute([$_POST['id']]);
                unset($consulta);
                unset($conexion);
                header('Location: logout.php');
            }
        } else {
            $error = "<p class='error'>Confirme para realizar esta acción.</p>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar cuenta</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
    require_once('includes/cabecera.inc.php');
    ?>
    <div class="formulario">
        <?php
            if (isset($error)) {
                echo '<br>'.$error;
            }
        ?>
        <h1>¿Estás seguro de que quieres eliminar tu cuenta?</h1>
        <form action="cancel.php?id=<?=$_GET["id"]?>" method="post">
            <div class="confirmacion">
            <input type="checkbox" name="confirmar" id="confirmar" value="si">
            <label for="confirmar">Confirmación</label>
            </div>
            <input type="hidden" name="id" id="id" value="<?=$_GET["id"]?>">
            <input type="submit" class="boton borrar_cuenta" value="Eliminar">
        </form>
</body>
</html>