<?php
require_once('includes/autologin.inc.php');

require_once('includes/conexion.inc.php');

if(!isset($_SESSION["usuario"])){
    header("Location: index.php");
}

//Recibe un comentario por POST y lo guarda en la base de datos si está correcto

if(count($_POST) > 0) {
    $errores = null;

    //Comprueba todos los datos para que ninguno esté vacío
    foreach ($_POST as $clave => $valor) {
        $valor = trim($valor);

        if (empty($valor)) {
            $errores[$clave] = '<p class="error">' . $clave . ' no puede estar vacío<p>';
        }
    }

    if ((strlen($_POST["comentario"]) < 20 || strlen($_POST["comentario"]) > 255) && !isset($errores["comentario"])) {
        $errores["texto"] = '<p class="error">El comentario solo recibe de 20 a 255 caracteres.</p><br>';
    }


    if (!$errores) {
        $conexion = conectar();

        if (!is_null($conexion)) {
            $consulta = $conexion->prepare('INSERT INTO comments (revelid, userid, texto, fecha) VALUES (?, ?, ?, ?); ');
            
            $fecha = date("Y-m-d H:i:s");
            $consulta->bindParam(1, $_POST["id"]);
            $consulta->bindParam(2, $_SESSION["usuario_id"]);
            $consulta->bindParam(3, $_POST["comentario"]);
            $consulta->bindParam(4, $fecha);

            try {
                $consulta->execute();
                header('Location: revel.php?id=' . $_POST["id"]);
            } catch (PDOException $e) {
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
    require_once('includes/cabecera.inc.php');
    echo '<div class="errores">';

    if (isset($errores)) {
        foreach ($errores as $error) {
            echo $error;
        }
    }
    echo '<a href="revel.php?id=' . $_POST["id"] . '">Volver</a>';
    echo '</div>';
    ?>

</body>
</html>