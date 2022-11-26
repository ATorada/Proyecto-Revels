<?php
/*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Contiene el apartado y la lógica de registro de la página para el usuario no registrado
    - Contiene la lista de revels del usuario registrado 
*/

//Se comprueba que el usuario esté logueado y se importa la función de conexión a la base de datos
session_start();
require_once('includes/conexion.inc.php');


if (count($_POST) > 0) {
    $conexion = conectar();
    try {
        //Se comprueba que el usuario o el correo no exista
        $consulta = $conexion->prepare("SELECT * FROM users WHERE usuario = ? or email = ?");
        $consulta->execute([$_POST["usuario"], $_POST["email"]]);
        if ($consulta->rowCount() > 0) {
            $errores[] = "<p class='error'>El usuario o el email ya está en uso</p>";
        } else {
            //Se comprueban los datos introducidos por el usuario
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
        }
    } catch (\Throwable $th) {
        $errores[] = "<p class='error'>Algo ha salido mal con la conexión a la base de datos</p>";
    }
    //Se cierra la conexión
    unset($consulta);
    unset($conexion);
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
    echo '<link rel="stylesheet" href="css/style.css">';
    //Se importa el CSS del nuevo usuario
    if (!isset($_SESSION["usuario"])) {
        echo '<link rel="stylesheet" href="css/nuevoUsuario.css">';
    }
    ?>
</head>

<body>
    <?php
    //Se importa el header
    require_once('includes/cabecera.inc.php');
    ?>

    <?php
    //Si el usuario no está logueado se muestra el formulario de registro
    if (!isset($_SESSION["usuario"])) {
    ?>
        <div class="formulario">
            <h1>¡Bienvenido a Revels! <br>📸</h1><br>
            <form action="#" method="post">
                <?php
                if (isset($errores)) {
                    foreach ($errores as $error) {
                        echo $error;
                    }
                }
                ?>
                <input type="text" name="usuario" id="usuario" value="<?= $_POST['usuario'] ?? '' ?>" placeholder="Usuario" required>

                <input type="text" name="email" id="email" value="<?= $_POST['email'] ?? '' ?>" placeholder="Correo Electrónico" required>

                <input type="password" name="contra" id="contra" value="<?= $_POST['contra'] ?? '' ?>" placeholder="Contraseña" required>

                <input type="password" name="contra-repetir" id="contra-repetir" value="<?= $_POST['contra-repetir'] ?? '' ?>" placeholder="Repetir Contraseña" required>

                <input class="boton" type="submit" value="Registrar">

            </form>
        </div>
    <?php
    } else {
        //Si el usuario está logueado se muestran los revels
    ?>
        <div class="publicaciones">
            <h1>Revels<br>📸</h1><br>
            <?php
            //Muestra el título, el autor y la cantidad de comentarios de los revels del usuario y los revels de los usuarios que sigue
            try {
                $conexion = conectar();
                $consulta = $conexion->prepare("SELECT revels.id, revels.texto, users.usuario, COUNT(comments.id) AS num_comentarios FROM revels
                INNER JOIN users on revels.userid = users.id
                LEFT JOIN comments ON revels.id = comments.revelid
                WHERE revels.userid = ? or revels.userid in (SELECT userfollowed FROM follows WHERE userid = ?)
                GROUP BY revels.id ORDER BY revels.fecha DESC");
                $consulta->execute([$_SESSION['usuario_id'], $_SESSION['usuario_id']]);
                $revels = $consulta->fetchAll(PDO::FETCH_ASSOC);
                if ($consulta->rowCount() > 0) {
                    foreach ($revels as $revel) {
                        echo '<a class="publicacion" href="revel.php?id=' . $revel["id"] . '">';
                        echo '<h2>' . $revel['texto'] . '</h2>';
                        //Comprueba si existe una imagen para el revel, sino pone una por defecto
                        if (file_exists('img/revels/' . $revel['id'] . "_" . $_SESSION["usuario"]  . '_resized.jpg')) {
                            echo '<img class="preview_foto" src="img/revels/' . $revel['id'] . "_" . $_SESSION["usuario"]  . '_resized.jpg" alt="Imagen del revel">';
                        }
                        /* En caso de que se quiera enfocar como que todos los revels tienen foto, se puede poner una imagen por defecto
                        else {
                            echo '<img class="preview_foto" src="img/placeholder.jpg" alt="revel_foto">';
                        } */

                        echo '<p>Autor: <span class="resaltado">' . $revel['usuario'] . '</span></p>';
                        echo '<p>Comentarios: <span class="resaltado">' . $revel['num_comentarios'] . '</span></p>';
                        echo '</a>';
                    }
                } else {
                    echo '<p>¡No sigues a nadie o tus seguidos no tienen revels!</p>';
                }
                //Se cierra la conexión
                unset($consulta);
                unset($conexion);
            } catch (\Throwable $th) {
                echo '<p class="error">Algo ha salido mal con la conexión a la base de datos</p>';
            }
            ?>

        </div>
    <?php
    }
    ?>
</body>

</html>