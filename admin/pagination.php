<?php
// Init sessión
session_start();

include("../functions.php"); /* Loads search from users + logs + startPDO */ 

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

// Store loggedUser Object
$loggedUser = searchUserInDatabase("*", "users", $_SESSION['user']);

if ($loggedUser['role'] !== "admin") {
    http_response_code(403);
    exit;
}


try {

    // Initialize BBDD
    $pdo = startPDO();
  
    $sql = "SELECT COUNT(name) FROM users";
  
    //logOperation("[DISCOVER.PHP] Sent query to get userww preferences: $sql");

    // Algorithm query

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $quantityUsers = $stmt->fetchColumn();

    $perPage = 25;
    $quantityOfPages = ceil($quantityUsers / $perPage);
    
    $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

    if ($currentPage < 1) {
        $currentPage = 1;
    } 

    if ($currentPage > $quantityOfPages) {
        $currentPage = $quantityOfPages;
    }
    
    $offset = ($currentPage - 1) * $perPage;

    $sql = "SELECT user_ID, name, email, role, last_login_date, creation_date, validated, deactivated FROM users ORDER BY user_ID ASC LIMIT $perPage OFFSET $offset;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
    <table border="1">

    <thead>
        <tr>
            <th>ID usuari</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Última conexió</th>
            <th>Data de creació</th>
            <th>Validat</th>
            <th>Desactivat</th>
            <th></th>
        </tr>
    </thead>

    <tbody>

       

    </tbody>

</table>

<?php

       // PREVIOUS PAGE ( << )
    if ($currentPage > 1) {
        $previousPage = $currentPage - 1;
        echo "<li><a href='?page=$previousPage'>&laquo;</a></li>";
    }

    // NUMBER GENERATION
    $maxShownPages = 5; // Max anchors (max. 5)
    $initialPage = max(1, $currentPage - floor($maxShownPages / 2)); // Dinamic initial paging (HIGHEST OF --> 1 or (currentPage - 2))
    $endingPage = min($quantityOfPages, $initialPage + $maxShownPages - 1); // Dinamic initial paging (LOWEST OF --> total pages or (currentPage + 2))

    // Anchor generation
    for ($i = $initialPage; $i <= $endingPage; $i++) {
        if ($i == $currentPage) {
            echo "<li><a class='active' href='?page=$i'>$i</a></li>"; // Página actual
        } else {
            echo "<li><a href='?page=$i'>$i</a></li>";
        }
    }

    // NEXT PAGE ( >> )
    if ($currentPage < $quantityOfPages) {
        $nextPage = $currentPage + 1;
        echo "<li><a href='?page=$nextPage'>&raquo;</a></li>";
    }

    // Cleans stored space for pdo and the query used. 
    unset($stmt);
    unset($pdo);

    //logOperation("[DISCOVER.PHP] Successfully got data of user preference in GET method get_logged_user_preferences. Returned data to JS");

    // Send successful objects of users
    // echo json_encode(['success' => true, 'message' => $user]);
    // exit;

} catch (PDOException $e) {

    //logOperation("[DISCOVER.PHP] Connection error in discover.php in GET method get_logged_user_preferences: " . $e->getMessage(), "ERROR");

    // catch error and send it to JS
    //echo json_encode(['success' => false, 'message' => 'Error en la conexión get_logged_user_preferences: ' . $e->getMessage()]);
    //exit;
   echo 'Error en la conexión: ' . $e->getMessage();
}

?>

