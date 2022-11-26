<?php
/*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Contiene el formulario y la lógica para crear un nuevo revel
*/

//Se comprueba que el usuario esté logueado y se importa la función de conexión a la base de datos
session_start();
require_once('includes/conexion.inc.php');

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
}


$errores = null;
//En caso de que se hayan enviado datos posteriormente se realizarán estas comprobaciones
if (count($_POST) != 0) {

    //Comprueba todos los datos para que ninguno esté vacío
    foreach ($_POST as $clave => $valor) {
        $valor = trim($valor);

        if (empty($valor)) {
            $errores[$clave] = '<p class="error">' . $clave . ' no puede estar vacío<p>';
        }
    }

    if (!preg_match('/^[A-z0-9\s\ñ]{3,100}$/', $_POST["titulo"]) && !isset($errores["titulo"])) {
        $errores["titulo"] = '<p class="error">El titulo solo recibe de 3 a 100 letras y números.</p><br>';
    }

    $conexion = conectar();


    if (!$errores) {
        try {
            $consulta = $conexion->prepare('INSERT INTO revels (userid, texto, fecha) VALUES (?, ?, ?); ');

            $fecha = date("Y-m-d H:i:s");
            $consulta->bindParam(1, $_SESSION["usuario_id"]);
            $consulta->bindParam(2, $_POST["titulo"]);
            $consulta->bindParam(3, $fecha);
            $consulta->execute();
            $id = $conexion->lastInsertId();
            //Se comprueba si hay una imagen
            if (isset($_FILES["imagen"]) && $_FILES["imagen"]["tmp_name"] != "") {
                //Se comprueba que el archivo sea una imagen, que no haya errores y que no sea mayor de 50MB 
                if ($_FILES["imagen"]["error"] == 0 && $_FILES["imagen"]["size"] < 52428800 && $_FILES["imagen"]["type"] == "image/jpeg") {
                    //Se guarda la imagen original y una copia a mitad de tamaño en la carpeta img revels
                    $imagen = $_FILES["imagen"]["tmp_name"];
                    $nombreImagen = $id . "_" . $_SESSION["usuario"];
                    $ruta = "img/revels/" . $nombreImagen;
                    move_uploaded_file($imagen, $ruta . ".jpg");

                    $imagen = imagecreatefromjpeg($ruta . ".jpg");
                    $ancho = imagesx($imagen);
                    $alto = imagesy($imagen);
                    $anchoFinal = $ancho / 2;
                    $altoFinal = $alto / 2;
                    $imagenFinal = imagecreatetruecolor($anchoFinal, $altoFinal);
                    imagecopyresampled($imagenFinal, $imagen, 0, 0, 0, 0, $anchoFinal, $altoFinal, $ancho, $alto);
                    imagejpeg($imagenFinal, $ruta . "_resized.jpg");
                    imagedestroy($imagen);
                    imagedestroy($imagenFinal);
                } else {
                    $errores["imagen"] = '<p class="error">La imagen debe ser de tipo jpeg y no puede superar los 50MB.</p><br>';
                }
            }
            if (!$errores) {
                //Se cierra la sesión
                unset($consulta);
                unset($conexion);
                //Se redirige a la página del revel creado
                header('Location: revel.php?id=' . $id);
            }
        } catch (\Throwable $th) {
            //Intentará borrar el revel en caso de que se haya creado
            try {
                //Borra la revel si no se ha podido subir la imagen
                $consulta = $conexion->prepare('DELETE FROM revels WHERE id = ?');
                $consulta->bindParam(1, $id);
                $consulta->execute();
            } catch (\Throwable $th) {
                //throw $th;
            }
            //Borra las imagenes si ha habido algún error
            isset($id) ? unlink("img/revels/" . $id . "_" . $_SESSION["usuario"] . ".jpg") : null;
            isset($id) ? unlink("img/revels/" . $id . "_" . $_SESSION["usuario"] . "_resized.jpg") : null;
        }
    }

    //Se cierra la sesión
    unset($consulta);
    unset($conexion);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo revel</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php
    //Se importa el header
    require_once('includes/cabecera.inc.php');
    ?>
    <div class="formulario">
        <form action="#" method="post" enctype="multipart/form-data">
            <h1>¡Crea un Revel!📸</h1><br>
            <label for="titulo">Título</label>
            <input type="text" name="titulo" id="titulo" value="<?= $_POST["titulo"] ?? "" ?>">
            <?php echo isset($errores["titulo"]) ? $errores["titulo"] : "" ?>
            <label for="imagen">Imagen</label>
            <input type="file" name="imagen" id="imagen">
            <?php echo isset($errores["imagen"]) ? $errores["imagen"] : "" ?>
            <input class="boton" type="submit" value="Crear Revel">
        </form>
    </div>

</body>

</html>