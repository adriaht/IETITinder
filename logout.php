<?php

    // logout.php
    session_start();
    session_destroy(); // Destruye sesión
    header('Location: login.php'); // Redirección a login.php
    exit;

?>