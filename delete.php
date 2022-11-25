<?php
/*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Contiene la lógica para eliminar un revel
*/

//Se comprueba que el usuario esté logueado y se importa la función de conexión a la base de datos
session_start();
require_once('includes/conexion.inc.php');

if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
}

//Borra el revel y sus comentarios 
if ($_GET['id']) {
    $conexion = conectar();
    //Comprueba que el revel sea del usuario
    $consulta = $conexion->prepare("SELECT * FROM revels WHERE id = ? AND userid = ?");
    $consulta->execute([$_GET['id'], $_SESSION['usuario_id']]);
    if ($consulta->rowCount() > 0) {
        //Borra los comentarios del revel
        $consulta = $conexion->prepare("DELETE FROM comments WHERE revelid = ?;");
        $consulta->execute([$_GET['id']]);
        //Borra el revel
        $consulta = $conexion->prepare("DELETE FROM revels WHERE id = ?;");
        $consulta->execute([$_GET['id']]);
        header('Location: list.php');
    }
    //Se cierra la conexión
    unset($consulta);
    unset($conexion);
} else {
    header('Location: list.php');
}
