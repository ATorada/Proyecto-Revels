<?php
/*
    Desarrollado por: Ángel Torada

    Este archivo:
    - Contiene la lógica para cerrar la sesión
*/

//Se inicia la sesión
session_start();
//Se destruye la sesión
session_destroy();
//Se redirige a index.php
header("Location: index.php");
