<?php
session_start();

/*
$current_page = basename($_SERVER['PHP_SELF']);
echo $current_page;
*/

if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'profile.php') === false) {
    header('Location:/errors/error403.php');
    exit;
}

if (!isset($_SESSION['user'])) {
    header('Location:/login.php');
    exit;
}

include("functions.php");


try {

    // Initialize BBDD
    $pdo = startPDO();

    // Se calcular las perferencias y se añaden a la consulta SQL para obtener usuarios
    $userID = $_SESSION['user'];

    logOperation("[DELETE-ACCOUNT.PHP] Started to deactivate user");
  
    $sql = "UPDATE users SET deactivated = 1 WHERE user_ID = $userID"; 
    
    logOperation("[DISCOVER.PHP] Sent query to deactivate user: $sql");

    // Algorithm query

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Cleans stored space for pdo and the query used. 
    unset($stmt);
    unset($pdo);

    logOperation("[DISCOVER.PHP] Successfully deactivated user");
    
    session_destroy();

    header('Location:/login.php');
    // Send successful objects of users
    exit;

} catch (PDOException $e) {

    logOperation("[DELETE-ACCOUNT.PHP] Connection error while deactivating account: " . $e->getMessage(), "ERROR");
    exit;
}



?>