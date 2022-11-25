<?php
/*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Contiene la página con el formulario de inicio de sesión
*/

//Se comprueba que el usuario esté logueado y se importa la función de conexión a la base de datos
session_start();
require_once('includes/conexion.inc.php');

if (isset($_SESSION["usuario"])) {
    header("Location: index.php");
}

//Se comprueba si se ha enviado el formulario
if (count($_POST) > 0) {

    //Se comprueba que el usuario y la contraseña coincidan con los de la base de datos y se inicia sesión
    try {
        $conexion = conectar();

        if (!is_null($conexion)) {
            $consulta = $conexion->prepare('SELECT * FROM users WHERE usuario = ? OR email = ?;');
            $consulta->bindParam(1, $_POST["usuario"]);
            $consulta->bindParam(2, $_POST["usuario"]);
            $consulta->execute();
            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);
            if ($usuario) {
                if (password_verify($_POST["contra"], $usuario["contrasenya"])) {
                    $_SESSION["usuario"] = $usuario["usuario"];
                    $_SESSION["usuario_id"] = $usuario["id"];
                    header('Location: index.php');
                } else {
                    $error = '<p class="error">El usuario o la contraseña no son correctos.</p><br>';
                }
            } else {
                $error = '<p class="error">El usuario o la contraseña no son correctos.</p><br>';
            }
        }
    } catch (\Throwable $th) {
        $error = '<p class="error">Algo ha salido mal con la conexión a la base de datos</p><br>';
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicia Sesión</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/nuevoUsuario.css">
</head>

<body>
    <?php
    //Importamos el header
    require_once('includes/cabecera.inc.php');
    echo '<div class="formulario">';
    ?>
    <h1>¡Inicia sesión! <br>📸</h1><br>
    <form action="login.php" method="post">
        <?php
        //Se muestran los errores
        if (isset($error)) {
            echo $error;
        }
        ?>
        <input type="text" name="usuario" id="usuario" placeholder="Usuario" value="<?= $_POST['usuario'] ?? "" ?>" required>
        <input type="password" name="contra" id="contra" placeholder="Contraseña" value="<?= $_POST['contra'] ?? "" ?>" required>
        <input type="submit" value="Iniciar Sesión" class="boton">
    </form>
    </div>
</body>

</html>