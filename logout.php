<?php
//Se inicia la sesión
session_start();
//Se destruye la sesión
session_destroy();
//Se redirige a index.php
header("Location: index.php");
