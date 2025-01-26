<?php
// Init sessión
session_start();

include("../functions.php"); /* Loads search from users + logs + startPDO */ 

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

// Store loggedUser Object
$loggedUser = searchUserInDatabase("*", "users", $_SESSION['user']);

if ($loggedUser['role'] !== "admin") {
    header('Location: ../errors/error403.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Panell d'Administració</title>
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time();?>" />
    
</head>
<body class="admin-panel">

    <div class="dashboard">
        <header id="header">
            <p class="logo">IETinder ❤️</p>
        </header>
        <h1>BENVINGUT AL PANELL D'ADMINISTRACIÓ</h1>
    </div>

</body>
</html>

