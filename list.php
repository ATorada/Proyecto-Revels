<?php
/*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Contiene un listado de las revels del usuario junto a un botón para borrarlo
*/

//Se comprueba que el usuario esté logueado y se importa la función de conexión a la base de datos
session_start();
require_once('includes/conexion.inc.php');

if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revelaciones</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/backend.css">
</head>

<body>
    <?php
    require_once('includes/cabecera.inc.php');
    echo '<div class="listarRevelaciones">';
    echo '<h1>Tus revelaciones</h1>';
    echo '<div class="revelaciones">';
    //Se muestan las revels del usuario
    try {
        $conexion = conectar();
        $consulta = $conexion->prepare("SELECT * FROM revels WHERE userid = ?");
        $consulta->execute([$_SESSION['usuario_id']]);
        if ($consulta->rowCount() > 0) {
            while ($revel = $consulta->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="revel">';
                echo '<h2><a href="revel.php?id=' . $revel['id'] . '">' . $revel['texto'] . '</a></h2>';
                echo '<p><span class="resaltado">' . $revel['fecha'] . '</span></p>';
                echo '<a href="delete.php?id=' . $revel['id'] . '">Eliminar</a>';
                echo '</div>';
            }
        } else {
            echo '<p>No tienes revelaciones</p>';
        }
    } catch (\Throwable $th) {
        echo '<p class="error">Algo ha salido mal</p>';
    }
    echo '</div>';
    echo '</div>';
    ?>

</body>

</html>