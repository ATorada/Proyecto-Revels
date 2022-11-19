<?php
require_once('includes/conexion.inc.php');

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

            $id = 1;
            $fecha = date("Y-m-d H:i:s");;
            $consulta->bindParam(1, $id);
            $consulta->bindParam(2, $_POST["titulo"]);
            $consulta->bindParam(3, $fecha);

            try {
                $consulta->execute();
                header('Location: revel.php?id=' . $conexion->lastInsertId());
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
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
        <form action="#" method="post">
            <h1>¡Crea un Revel!📸</h1><br>
            <label for="titulo">Título</label>
            <input type="text" name="titulo" id="titulo" value="<?= $_POST["titulo"] ?? "" ?>">
            <?php echo isset($errores["titulo"]) ? $errores["titulo"] : "" ?>
            <!--
            <label for="imagen">Imagen</label>
            <input type="file" name="imagen" id="imagen"> 
        -->
            <input class="registrar" type="submit" value="Crear Revel">
        </form>
    </div>

</body>

</html>