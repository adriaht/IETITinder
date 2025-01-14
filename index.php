<?php
// Init sessión
session_start();

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
} else {
    header("Location: discover.php");
    exit;
}

?>