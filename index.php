<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revels ~ ✨</title>
    <?php
    $registrado = true;
    if ($registrado) {
    ?>
        <link rel="stylesheet" href="css/style.css">
    <?php
    } else {
    ?>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/nuevoUsuario.css">
    <?php
    }
    ?>
</head>

<body>
    <?php
    require_once('includes/cabecera.inc.php');
    require_once('includes/conexion.inc.php');
    ?>

    <?php
    if (!$registrado) {
    ?>
        <div class="formulario">
            <h1>¡Bienvenido a Revels! <br>📸</h1><br>
            <form action="#" method="post">
                <label for="mail"><b>Correo electrónico</b></label>
                <input type="text" name="mail" id="mail" required>

                <label for="contra"><b>Contraseña</b></label>
                <input type="contra" name="contra" id="contra" required>

                <label for="contra-repetir"><b>Repetir Contraseña</b></label>
                <input type="contra-repetir" name="contra-repetir" id="contra-repetir" required>

                <input class="registrar" type="submit" value="Registrar">

            </form>
        </div>
    <?php
    } else {
    ?>
            <div class="publicaciones">
            <h1>Fotillos<br>📸</h1><br>
            <?php
            $conexion = conectar();
            $revels = $conexion->query("SELECT * FROM revels ORDER BY revels.fecha DESC");

            while ($revel = $revels->fetch(PDO::FETCH_ASSOC)) {
                echo '<a class="publicacion" href="https://www.google.com">';
                echo '<h2>' . $revel['texto'] . '</h2>';
                echo '<img class="preview_foto" src="img/placeholder.jpg" alt="revel_foto">';

                $usuario = $conexion->query("SELECT usuario FROM users WHERE id = " . $revel['userid']);
                $usuario = $usuario->fetch(PDO::FETCH_ASSOC);

                $comentarios = $conexion->query("SELECT * FROM comments WHERE revelid = " . $revel['id']);

                echo '<p>Comentarios: '. $comentarios->rowCount() .' Autor: <span class="autor">' . $usuario['usuario'] . '</span></p>';
                echo '</a>';
            }
            unset($revels);
            unset($conexion);
            ?>

        </div>
    <?php
        }
    ?>
</body>

</html>