<?php
require_once('includes/autologin.inc.php');
require_once('includes/conexion.inc.php');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario_id'] != $_GET['id']) {
    header('Location: index.php');
}

if(count($_POST)>0){
    $conexion = conectar();
    if(!is_null($conexion)){
        $consulta = $conexion->prepare("SELECT * FROM users WHERE id <> ? AND  (usuario = ? or email = ?);");
        $consulta->execute([$_SESSION['usuario_id'], $_POST["usuario"], $_POST["email"]]);
        if ($consulta->rowCount() > 0) {
            $errores["usado"] = "<p class='error'>El usuario o el email ya está en uso</p>";
        } else {
            if (!preg_match("/^[A-z]{1}[A-z0-9._]{2,61}@[A-z0-9]{1,251}.[A-z]{2,4}$/", $_POST['email'])) {
                $errores["email"] = "<p class='error'>El email no es válido.</p>";
            }
            if (!preg_match("/^[a-zA-Z0-9]{3,20}$/", $_POST['usuario'])) {
                $errores["usuario"] = "<p class='error'>El usuario no es válido, debe tener entre 3 y 20 letras y números.</p>";
            }
            if (isset($_POST['contraNueva']) && $_POST['contraNueva'] != "") {
                if (!preg_match("/^[a-zA-Z0-9]{8,255}$/", $_POST['contraNueva'])) {
                    $errores["contra"] = "<p class='error'>La contraseña no es válida, debe tener un mínimo de 8 letras y números.</p>";
                }
                if ($_POST['contraNueva'] != $_POST['contraNueva2'] && !isset($errores["contra"])) {
                    $errores["contra"] = "<p class='error'>Las contraseñas no coinciden.</p>";
                }
            }

            if (!isset($errores)) {
                $consulta = $conexion->prepare("UPDATE users SET usuario = ?, email = ? WHERE id = ?");
                $consulta->execute([$_POST["usuario"], $_POST["email"], $_GET["id"]]);
                if (isset($_POST['contraNueva'])){
                    $consulta = $conexion->prepare("UPDATE users SET contrasenya = ? WHERE id = ?");
                    $consulta->execute([password_hash($_POST['contraNueva'], PASSWORD_DEFAULT), $_GET["id"]]);
                    $errores["correcto"] = "<p class='correcto'>Las modificaciones se han realizado correctamente.</p>";
                }

            }
        }
        unset($consulta);
        unset($conexion);
    }
} else {
    $conexion = conectar();
    if(!is_null($conexion)){
        $consulta = $conexion->prepare('SELECT * FROM users WHERE id = ?;');
        $consulta->execute([$_GET['id']]);
        $usuario = $consulta->fetch(PDO::FETCH_ASSOC);
        $_POST['usuario'] = $usuario['usuario'];
        $_POST['email'] = $usuario['email'];
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
</head>
<body>
    <?php
    require_once('includes/cabecera.inc.php');
    ?>
    <div class="formulario">
    <h1>¡Cambia tus datos!</h1>
    <?php
        if (isset($errores)) {
            foreach ($errores as $error) {
                echo $error."<br>";
            }
        }
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

</body>
</html>