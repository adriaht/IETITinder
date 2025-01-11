<?php

    // logout.php
    session_start();
    session_destroy(); // Destruye sesión
    header('Location: login.html'); // Redirección a login.php
    exit;

?>