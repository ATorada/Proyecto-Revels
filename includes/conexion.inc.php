<?php
/*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Contiene la función de conexión a la base de datos
*/
function conectar()
{
    //Se prepara y devuelve la conexión y en caso de error se devuelve null
    $dsn = 'mysql:host=localhost;dbname=revels';
    $opciones = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

    try {
        $conexion = new PDO($dsn, 'revel', 'lever', $opciones);
    } catch (PDOException $e) {
        $conexion = null;
    }
    return $conexion;
}
