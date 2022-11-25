<?php
/*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Muestra y modifica la información del usuario
    - Muestra una opción para borrar la cuenta del usuario
    - Muestra una opción para listar los revels del usuario
*/

//Se comprueba que el usuario esté logueado y que sea el propietario y se importa la función de conexión a la base de datos
session_start();
require_once('includes/conexion.inc.php');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario_id'] != $_GET['id']) {
    header('Location: index.php');
}

//Se comprueba si se ha enviado el formulario
if (count($_POST) > 0) {
    $conexion = conectar();
    //Se comprueba que el usuario/correo no esté en uso
    if (!is_null($conexion)) {
        $consulta = $conexion->prepare("SELECT * FROM users WHERE id <> ? AND  (usuario = ? or email = ?);");
        $consulta->execute([$_SESSION['usuario_id'], $_POST["usuario"], $_POST["email"]]);
        if ($consulta->rowCount() > 0) {
            $errores["usado"] = "<p class='error'>El usuario o el email ya está en uso</p>";
        } else {
            //Se comprueban los datos del email y el usuario
            if (!preg_match("/^[A-z]{1}[A-z0-9._]{2,61}@[A-z0-9]{1,251}.[A-z]{2,4}$/", $_POST['email'])) {
                $errores["email"] = "<p class='error'>El email no es válido.</p>";
            }
            if (!preg_match("/^[a-zA-Z0-9]{3,20}$/", $_POST['usuario'])) {
                $errores["usuario"] = "<p class='error'>El usuario no es válido, debe tener entre 3 y 20 letras y números.</p>";
            }
            //En caso de recibir una nueva se comprueba que sea válida
            if (isset($_POST['contraNueva']) && $_POST['contraNueva'] != "") {
                if (!preg_match("/^[a-zA-Z0-9]{8,255}$/", $_POST['contraNueva'])) {
                    $errores["contra"] = "<p class='error'>La contraseña no es válida, debe tener un mínimo de 8 letras y números.</p>";
                }
                if ($_POST['contraNueva'] != $_POST['contraNueva2'] && !isset($errores["contra"])) {
                    $errores["contra"] = "<p class='error'>Las contraseñas no coinciden.</p>";
                }
            }

            //Si no hay errores se actualiza la información
            if (!isset($errores)) {
                try {
                    $consulta = $conexion->prepare("UPDATE users SET usuario = ?, email = ? WHERE id = ?");
                    $consulta->execute([$_POST["usuario"], $_POST["email"], $_GET["id"]]);
                    $errores["correcto"] = "<p class='correcto'>Las modificaciones se han realizado correctamente.</p>";
                } catch (\Throwable $th) {
                    $errores["error"] = "<p class='error'>Ha ocurrido un error modificando los datos del usuario, inténtelo de nuevo más tarde.</p>";
                }
                if (isset($_POST['contraNueva'])) {
                    try {
                        $consulta = $conexion->prepare("UPDATE users SET contrasenya = ? WHERE id = ?");
                        $consulta->execute([password_hash($_POST['contraNueva'], PASSWORD_DEFAULT), $_GET["id"]]);
                        $errores["correcto"] = "<p class='correcto'>Las modificaciones se han realizado correctamente.</p>";
                    } catch (\Throwable $th) {
                        $errores["error"] = "<p class='error'>Ha ocurrido un error modificando los datos del usuario, inténtelo de nuevo más tarde.</p>";
                    }
                }
            }
        }
        //Se cierra la conexión
        unset($consulta);
        unset($conexion);
    }
} else {
    //En caso de no recibir el formulario se obtienen los datos del usuario y se guardan en la variable POST
    $conexion = conectar();
    if (!is_null($conexion)) {
        try {
            $consulta = $conexion->prepare('SELECT * FROM users WHERE id = ?;');
            $consulta->execute([$_GET['id']]);
            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);
            $_POST['usuario'] = $usuario['usuario'];
            $_POST['email'] = $usuario['email'];
        } catch (\Throwable $th) {
            $errores["error"] = "<p class='error'>Ha ocurrido un error cargando los datos del usuario, inténtelo de nuevo más tarde.</p>";
        }
        unset($consulta);
        unset($conexion);
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/backend.css">
</head>

<body>
    <?php
    //Se importa el header
    require_once('includes/cabecera.inc.php');
    ?>
    <div class="formulario">
        <h1>¡Cambia tus datos!</h1>
        <?php
        //Se muestran los errores
        if (isset($errores)) {
            foreach ($errores as $error) {
                echo $error . "<br>";
            }
        }
        //Se muestra el formulario
        echo '<form action="account.php?id=' . $_GET['id'] . '" method="post">';
        echo '<label for="usuario">Usuario</label>';
        echo '<input type="text" name="usuario" id="usuario" value="' . $_POST['usuario'] . '">';
        echo '<label for="email">Email</label>';
        echo '<input type="email" name="email" id="email" value="' . $_POST['email'] . '">';
        echo '<label for="contra">Contraseña nueva</label>';
        echo '<input type="password" name="contraNueva" id="contraNueva" value="">';
        echo '<label for="contra2">Repite la contraseña nueva</label>';
        echo '<input type="password" name="contraNueva2" id="contraNueva2" value="">';
        echo '<input type="submit" value="Modificar" class="boton">';
        echo '</form>';
        ?>
    </div>

    <div class="eliminaCuenta">
        <h1>¿Quieres eliminar tu cuenta?</h1>
        <form action="cancel.php?id=<?php echo $_GET['id']; ?>" method="post">
            <input type="submit" value="Eliminar" class="boton borrar_cuenta">
        </form>
    </div>

    <div class="verRevelaciones">
        <h1>¿Quieres ver tus revelaciones?</h1>
        <a href="list.php" class="boton">Ver</a><br><br>
    </div>

</body>

</html>