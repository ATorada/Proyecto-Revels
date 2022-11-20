<?php
require_once('includes/autologin.inc.php');
require_once('includes/conexion.inc.php');

if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
}

if (count($_POST) > 0) {
    $conexion = conectar();
    if (!is_null($conexion)) {
        if(isset($_POST['amigo_id_seguir'])) {
            $consulta = $conexion->prepare('INSERT INTO follows (userid, userfollowed) VALUE (?, ?);');
            $consulta->bindParam(1, $_SESSION['usuario_id']);
            $consulta->bindParam(2, $_POST['amigo_id_seguir']);
            $consulta->execute();
        } else  {
            $consulta = $conexion->prepare('DELETE FROM follows WHERE userid = ? AND userfollowed = ?;');
            $consulta->bindParam(1, $_SESSION['usuario_id']);
            $consulta->bindParam(2, $_POST['amigo_id_borrar']);
            $consulta->execute();
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
    <title>Resultados</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php
    require_once('includes/cabecera.inc.php');
    echo "<div class='resultados'>";
    echo '<h1>Resultados</h1>';
    //Muestra los usuarios que coinciden con la búsqueda junto a un botón de añadir

    if (isset($_GET['busqueda'])) {
        $conexion = conectar();
        $consulta = $conexion->prepare("SELECT id, usuario FROM users WHERE usuario LIKE ?");
        $consulta->execute(array('%'.$_GET['busqueda'].'%'));
        $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
        if ($resultado) {
            foreach ($resultado as $usuario) {
                //Se comprueba si el usuario ya sigue al usuario que se está mostrando
                $consulta = $conexion->prepare("SELECT * FROM follows WHERE userid = ? AND userfollowed = ?");
                $consulta->execute(array($_SESSION['usuario_id'], $usuario['id']));
                $sigue = $consulta->fetch(PDO::FETCH_ASSOC);
                echo '<div class="usuario">';
                echo "<p>".$usuario['usuario']."</p>";
                echo "<form action='#' method='post'>";
                if ($sigue) {
                    echo "<input type='hidden' name='amigo_id_borrar' value='".$usuario['id']."'>";
                    echo "<input class='boton borrar_cuenta' type='submit' value='Dejar de seguir'>";
                } else {
                    echo "<input type='hidden' name='amigo_id_seguir' value='".$usuario['id']."'>";
                    echo "<input  class='boton' type='submit' value='Seguir'>";
                }
                echo "</form>";
                echo '</div>';
            }
        } else {
            echo "<p>No se han encontrado resultados</p>";
        }
        unset($consulta);
        unset($resultado);
        unset($conexion);
    }
    echo "</div>";
    ?>
</body>

</html>