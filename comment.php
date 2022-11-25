<?php
/*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Comprueba que el comentario recibido esté correcto, en caso de no estarlo muestra un error
*/

//Se comprueba que el usuario esté logueado y se importa la función de conexión a la base de datos
session_start();
require_once('includes/conexion.inc.php');

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
}

//Recibe un comentario por POST y lo guarda en la base de datos si está correcto

if (count($_POST) > 0) {
    $errores = null;

    //Comprueba que el comentario no esté vacío
    foreach ($_POST as $clave => $valor) {
        $valor = trim($valor);

        if (empty($valor)) {
            $errores[$clave] = '<p class="error">' . $clave . ' no puede estar vacío<p>';
        }
    }

    //Comprueba que el comentario no sea demasiado largo ni demasiado corto
    if ((strlen($_POST["comentario"]) < 20 || strlen($_POST["comentario"]) > 255) && !isset($errores["comentario"])) {
        $errores["texto"] = '<p class="error">El comentario solo recibe de 20 a 255 caracteres.</p><br>';
    }

    //Se añade el comentario a la base de datos si no hay errores 
    if (!$errores) {
        $conexion = conectar();

        if (!is_null($conexion)) {
            try {
                $consulta = $conexion->prepare('INSERT INTO comments (revelid, userid, texto, fecha) VALUES (?, ?, ?, ?); ');

                $fecha = date("Y-m-d H:i:s");
                $consulta->bindParam(1, $_POST["id"]);
                $consulta->bindParam(2, $_SESSION["usuario_id"]);
                $consulta->bindParam(3, $_POST["comentario"]);
                $consulta->bindParam(4, $fecha);

                $consulta->execute();
                header('Location: revel.php?id=' . $_POST["id"]);
            } catch (PDOException $e) {
            }
        }
        //Se cierra la conexión
        unset($conexion);
        unset($consulta);
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
    //Importa el header
    require_once('includes/cabecera.inc.php');
    echo '<div class="errores">';
    //Muestra los errores
    if (isset($errores)) {
        foreach ($errores as $error) {
            echo $error;
        }
    }
    //Muestra un enlace a la página anterior
    echo '<a href="revel.php?id=' . $_POST["id"] . '">Volver</a>';
    echo '</div>';
    ?>

</body>

</html>