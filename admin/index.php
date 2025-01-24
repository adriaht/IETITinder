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

        <div id="navigation">

            <header id="header">
                <p class="logo">IETinder ❤️</p>
            </header>

            <nav>
                <ul>
                    <li class="active"><a href="/admin/index.php">Principal</a></li>
                    <li><a href="/admin/users.php">Usuaris</a></li>
                    <li><a href="/admin/logs.php">Registres</a></li>
                    <li><a href="/index.php">Tornar a l'inici</a></li>
                </ul>
            </nav>

        </div>

        <div id="content">

           
        </div>
       
    </div>

</body>
</html>

