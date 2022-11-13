<?php
function conectar()
{
    //Se prepara la conexión
    $dsn = 'mysql:host=localhost;dbname=revels';
    $opciones = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

    try {
        $conexion = new PDO($dsn, 'revel', 'lever', $opciones);
    } catch (PDOException $e) {
        $conexion = null;
    }
    return $conexion;
}