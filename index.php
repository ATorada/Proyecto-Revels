<?php
require_once('includes/autologin.inc.php');

require_once('includes/conexion.inc.php');


if (count($_POST) > 0) {
    $conexion = conectar();
    $consulta = $conexion->prepare("SELECT * FROM users WHERE usuario = ? or email = ?");
    $consulta->execute([$_POST["usuario"], $_POST["email"]]);
    if ($consulta->rowCount() > 0) {
        $errores[] = "<p class='error'>El usuario o el email ya está en uso</p>";
    } else {
        if (!preg_match("/^[A-z]{1}[A-z0-9._]{2,61}@[A-z0-9]{1,251}.[A-z]{2,4}$/", $_POST['email'])) {
            $errores["email"] = "<p class='error'>El email no es válido.</p>";
        }
        if (!preg_match("/^[a-zA-Z0-9]{3,20}$/", $_POST['usuario'])) {
            $errores["usuario"] = "<p class='error'>El usuario no es válido, debe tener entre 3 y 20 letras y números.</p>";
        }
        if (!preg_match("/^[a-zA-Z0-9]{8,255}$/", $_POST['contra'])) {
            $errores["contra"] = "<p class='error'>La contraseña no es válida, debe tener un mínimo de 8 letras y números.</p>";
        }
        //En caso de que no se haya encontrado ningún error se intenta insertar el usuario en la base de datos
        if (!isset($errores)) {
            //Se comprueba que las contraseñas coincidan
            if ($_POST['contra'] == $_POST['contra-repetir']) {
                //Se cifra la contraseña
                $_POST['contra'] = password_hash($_POST['contra'], PASSWORD_DEFAULT);
                try {
                    //Se inserta el usuario en la base de datos
                    $consulta = $conexion->prepare("INSERT INTO users (usuario, email, contrasenya) VALUES (?, ? ,?)");
                    $consulta->execute([$_POST['usuario'], $_POST['email'], $_POST['contra']]);

                    $_SESSION['usuario'] = $_POST['usuario'];
                    $_SESSION['usuario_id'] = $conexion->lastInsertId();

                    unset($consulta);
                    unset($conexion);
                } catch (PDOException $e) {
                    $errores[] = "<p class='error'>Error al registrar el usuario</p>";
                }
            } else {
                $errores[] = "<p class='error'>Las contraseñas no coinciden</p>";
            }
        }
        unset($consulta);
        unset($conexion);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revels ~ ✨</title>
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
    ?>

    <?php
    if (!isset($_SESSION["usuario"])) {
    ?>
        <div class="formulario">
            <h1>¡Bienvenido a Revels! <br>📸</h1><br>
            <form action="#" method="post">

                <label for="usuario"><b>Usuario</b></label>
                <input type="text" name="usuario" id="usuario" value="<?=$_POST['usuario']??''?>" required>
                <?php echo isset($errores["usuario"]) ? $errores["usuario"].'<br>' : ""; ?>

                <label for="email"><b>Correo electrónico</b></label>
                <input type="text" name="email" id="email" value="<?=$_POST['email']??''?>" required>
                <?php echo isset($errores["email"]) ? $errores["usuario"].'<br>' : ""; ?>

                <label for="contra"><b>Contraseña</b></label>
                <input type="password" name="contra" id="contra" value="<?=$_POST['contra']??''?>" required>

                <label for="contra-repetir"><b>Repetir Contraseña</b></label>
                <input type="password" name="contra-repetir" id="contra-repetir" value="<?=$_POST['contra-repetir']??''?>" required>
                <?php echo isset($errores["contra"]) ? $errores["usuario"].'<br>' : ""; ?>

                <input class="boton" type="submit" value="Registrar">

            </form>
        </div>
    <?php
    } else {
    ?>
            <div class="publicaciones">
            <h1>Revels<br>📸</h1><br>
            <?php
            //Muestra los revels del usuario y los revels de los usuarios que sigue
            $conexion = conectar();
            $consulta = $conexion->prepare("SELECT * FROM revels WHERE userid = ? or userid in (SELECT userfollowed FROM follows WHERE userid = ?) ORDER BY fecha DESC");
            $consulta->execute([$_SESSION['usuario_id'], $_SESSION['usuario_id']]);
            $revels = $consulta->fetchAll(PDO::FETCH_ASSOC);
            if ($consulta->rowCount() > 0) {
                foreach ($revels as $revel) {
                        echo '<a class="publicacion" href="revel.php?id='.$revel["id"].'">';
                        echo '<h2>' . $revel['texto'] . '</h2>';
                        //Comprueba si existe una imagen para el revel, sino pone una por defecto
                        if (file_exists('img/revels/'. $revel['id'] ."_". $_SESSION["usuario"]  . '_resized.jpg')) {
                            echo '<img class="preview_foto" src="img/revels/'. $revel['id'] ."_". $_SESSION["usuario"]  . '_resized.jpg" alt="Imagen del revel">';
                        } else {
                            echo '<img class="preview_foto" src="img/placeholder.jpg" alt="revel_foto">';
                        }
        
                        $usuario = $conexion->query("SELECT usuario FROM users WHERE id = " . $revel['userid']);
                        $usuario = $usuario->fetch(PDO::FETCH_ASSOC);
        
                        $comentarios = $conexion->query("SELECT * FROM comments WHERE revelid = " . $revel['id']);
                        echo '<p>Autor: <span class="resaltado">' . $usuario['usuario'] . '</span></p>';
                        echo '<p>Comentarios: <span class="resaltado">'. $comentarios->rowCount() .'</span></p>';
                        echo '</a>';
                }
            unset($consulta);
            unset($conexion);
            } else {
                echo '<p>¡No sigues a nadie!</p>';
            }

            ?>

        </div>
    <?php
        }
    ?>
</body>

</html>