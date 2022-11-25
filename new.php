<?php
session_start();
require_once('includes/conexion.inc.php');

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
}

//Se establecen las expresiones regulares en variables para posteriormente entender mejor el código
$exprTitulo = '/^[A-z0-9\s\ñ]{3,15}$/';
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

    if (!preg_match($exprTitulo, $_POST["titulo"]) && !isset($errores["titulo"])) {
        $errores["titulo"] = '<p class="error">El titulo solo recibe de 3 a 15 letras y números.</p><br>';
    }

    if (!$errores) {
        $conexion = conectar();

        if (!is_null($conexion)) {
            $consulta = $conexion->prepare('INSERT INTO revels (userid, texto, fecha) VALUES (?, ?, ?); ');

            $fecha = date("Y-m-d H:i:s");
            $consulta->bindParam(1, $_SESSION["usuario_id"]);
            $consulta->bindParam(2, $_POST["titulo"]);
            $consulta->bindParam(3, $fecha);
            try {
                $consulta->execute();
                //Comprueba si la imagen existe
                if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
                    //Se guarda la imagen original y una copia a mitad de tamaño en la carpeta img revels
                    $imagen = $_FILES["imagen"]["tmp_name"];
                    $nombreImagen = $conexion->lastInsertId() . "_" . $_SESSION["usuario"];
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
                    $errores["imagen"] = '<p class="error">No se ha podido subir la imagen.</p>';
                }
            header('Location: revel.php?id=' . $conexion->lastInsertId());
            } catch (PDOException $e) {
                $id = $conexion->lastInsertId();
                //Borra la revel si no se ha podido subir la imagen
                $consulta = $conexion->prepare('DELETE FROM revels WHERE id = ?');
                $consulta->bindParam(1, $id);
                $consulta->execute();
                //Borra la imagen si no se ha podido subir
                $nombreFoto = "img/revels/" . $id. "_" . $_SESSION["usuario"] . ".jpg";
                if (is_file($nombreFoto)) {
                    unlink($nombreFoto);
                }
                $nombreFoto = "img/revels/" . $id. "_" . $_SESSION["usuario"] . "_resized.jpg";
                if (is_file($nombreFoto)) {
                    unlink($nombreFoto);
                }
            }
            unset($consulta);
            unset($conexion);
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
    <title>Nuevo revel</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php
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