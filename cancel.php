<?php
/*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Muestra una confirmación para borrar el usuario y en caso de confirmación borra el usuario
*/

//Se comprueba que el usuario esté logueado y que sea el propietario y se importa la función de conexión a la base de datos
session_start();
require_once('includes/conexion.inc.php');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario_id'] != $_GET['id']) {
    header('Location: index.php');
}

//Se comprueba si se ha enviado la confirmación
if (count($_POST) > 0) {
    //Se realiza una segunda comprobación para evitar que se pueda borrar el usuario siendo otro usuario
    if ($_SESSION['usuario_id'] == $_POST['id']) {
        if (isset($_POST['confirmar'])) {
            $conexion = conectar();
            if (!is_null($conexion)) {
                try {
                    //Se inicializa la transacción
                    $conexion->beginTransaction();
                    //Borra los revels y sus comentarios
                    $consulta = $conexion->prepare("DELETE FROM comments WHERE revelid IN (SELECT id FROM revels WHERE userid = ?);");
                    $consulta->execute([$_SESSION['usuario_id']]);
                    $consulta = $conexion->prepare("DELETE FROM revels WHERE userid = ?;");
                    $consulta->execute([$_SESSION['usuario_id']]);
                    //Borra los comentarios del usuario
                    $consulta = $conexion->prepare("DELETE FROM comments WHERE userid = ?;");
                    $consulta->execute([$_SESSION['usuario_id']]);
                    //Borra las personas a las que sigue el usuario y los seguidores del usuario
                    $consulta = $conexion->prepare("DELETE FROM follows WHERE userid = ?;");
                    $consulta->execute([$_SESSION['usuario_id']]);
                    //Borra el usuario
                    $consulta = $conexion->prepare("DELETE FROM users WHERE id = ?;");
                    $consulta->execute([$_POST['id']]);
                    //Se confirma la transacción
                    $conexion->commit();
                    header('Location: logout.php');
                } catch (\Throwable $th) {
                    //Se cancela la transacción en caso de error
                    $conexion->rollBack();
                }
                //Se cierra la conexión
                unset($consulta);
                unset($conexion);
                $error = "<p class='error'>Algo ha salido mal al intentar borrar el usuario</p>";
            }
        } else {
            $error = "<p class='error'>Confirme para realizar esta acción.</p>";
        }
    } else {
        header('Location: index.php');
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
    <link rel="stylesheet" href="css/backend.css">
</head>

<body>
    <?php
    //Se importa el header
    require_once('includes/cabecera.inc.php');
    ?>
    <div class="formulario">
        <?php
        //Se muestran los errores
        if (isset($error)) {
            echo '<br>' . $error;
        }
        ?>
        <h1>¿Estás seguro de que quieres eliminar tu cuenta?</h1>
        <form action="cancel.php?id=<?= $_GET["id"] ?>" method="post">
            <div class="confirmacion">
                <input type="checkbox" name="confirmar" id="confirmar" value="si">
                <label for="confirmar">Confirmación</label>
            </div>
            <input type="hidden" name="id" id="id" value="<?= $_GET["id"] ?>">
            <input type="submit" class="boton borrar_cuenta" value="Eliminar">
        </form>
</body>

</html>