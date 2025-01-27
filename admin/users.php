<?php
// Init sessión
session_start();

include("../functions.php"); /* Loads search from users + logs + startPDO */ 

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    logOperation("[USERS.PHP] User is not logged in");
    header('Location: /login.php');
    exit;
}

// Store loggedUser Object
$loggedUser = searchUserInDatabase("role", "users", $_SESSION['user']);
if ($loggedUser['role'] !== "admin") {
    logOperation("[USERS.PHP] User is not an admin. Sent error 403.");
    http_response_code(403);
    exit;
}

function getAge($date){
    logOperation("[USERS.PHP] Calculating age from birthday $date.");
    $birthDate = $date; // Expected format: YYYY-MM-DD
    $birthDateObj = new DateTime($birthDate);
    $today = new DateTime();
    return $today->diff($birthDateObj)->y;
}



function getUserInteractionData($userID) {

    try {

        // Initialize BBDD
        $pdo = startPDO();

        // Calculates users stats from database
        logOperation("[USERS.PHP] Started to get user $userID interaction data");

        $sql = "SELECT 
            (SELECT COUNT(*) FROM interactions WHERE `from` = :loggedUserId) AS interactions_done,
            (SELECT COUNT(*) FROM interactions WHERE `from` = :loggedUserId AND `state` = 'like') AS given_likes,
            (SELECT COUNT(*) FROM interactions WHERE `to` = :loggedUserId) AS interactions_received,
            (SELECT COUNT(*) FROM interactions WHERE `to` = :loggedUserId AND `state` = 'like') AS received_likes,
            (SELECT COUNT(*) FROM matches WHERE participant1 = :loggedUserId OR participant2 = :loggedUserId) AS matches_count;";
        
        logOperation("[USERS.PHP] Sent query to get user $userID interaction data: $sql");

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loggedUserId', $userID);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        unset($stmt);
        unset($pdo);

        logOperation("[USERS.PHP] Successfully got data of user interaction.");

        return $user;

    } catch (PDOException $e) {

        logOperation("[USERS.PHP] Connection error getting user $userID interaction data: " . $e->getMessage(), "ERROR");

        return false;

    }
}

function getUserPhotos($userID) {

        try {

            logOperation("[USERS.PHP] Requested photos of user $userID.");

            // Initialize BBDD
            $pdo = startPDO();
    
            // Gets photos of user
            $sql = "SELECT photo_ID, path FROM photos WHERE user_ID = :userId";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':userId', $userID);
            $stmt->execute();

            logOperation("[USERS.PHP] Query sent to get photos: $sql");
    
            // Load user photos from the query
            $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Cleans stored space for pdo and the query used. 
            unset($stmt);
            unset($pdo);
    
            logOperation("[USERS.PHP] Successfully got photos of user $userID");
    
            return array_values($photos);
        
    
        } catch (PDOException $e) {
    
           logOperation("[USERS.PHP] Connection error while getting photos of user $userID: " . $e->getMessage(), "ERROR");
            return false;

        }
}


function userExists($userID) {

    try {

        logOperation("[USERS.PHP] Checking if user $userID exists.");

        // Initialize BBDD
        $pdo = startPDO();

        // Gets photos of user
        $sql = "SELECT user_ID FROM users WHERE user_ID = :userId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':userId', $userID);
        $stmt->execute();

        logOperation("[USERS.PHP] Query sent to see if user exists: $sql");

        // Load user photos from the query
        $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

        unset($stmt);
        unset($pdo);

        if($user){
            logOperation("[USERS.PHP] User $userID exists. Loading data.");
            return true;
        } else {
            logOperation("[USERS.PHP] User $userID doesn't exist. Returned to /admin/users.php");
            return false;
        }

    } catch (PDOException $e) {

       logOperation("[USERS.PHP] Connection error while checking if user exists: " . $e->getMessage(), "ERROR");
    return false;

    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Panell d'Administració</title>
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time();?>" />
    <script src="users.js"></script>
</head>
<body class="admin-panel">

    <div class="dashboard">

        <div id="navigation">

            <header id="header">
                <p class="logo">IETinder ❤️</p>
            </header>

            <nav>
                <ul>
                     <li><a href="/admin/index.php">Principal</a></li>
                    <li class="active"><a href="/admin/users.php">Usuaris</a></li>
                    <li><a href="/admin/logs.php">Registres</a></li>
                    <li><a href="/index.php">Tornar a l'inici</a></li>
                </ul>
            </nav>

        </div>

        <div id="content">

            <?php if(!isset($_GET["id"])) { ?>

                <div id="title-container">
                    <h1>Administració d'usuaris</h1>
                </div>

            <?php

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
           
            <div id="table-container">

                <table class="users">

                    <thead>
                    <tr>
                        <th>ID</th>
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

                    <?php
                    
                        foreach ($users as $user) {
                    
                            echo "<tr>";
                                echo "<td>".$user["user_ID"]."</td>";
                                echo "<td>".$user["name"]."</td>";
                                echo "<td>".$user["email"]."</td>";
                                if ($user["role"] === "user") {
                                    echo "<td>Usuari</td>";
                                } else {
                                    echo "<td>Administrador</td>";
                                }
                                
                                echo "<td>".$user["last_login_date"]."</td>";
                                echo "<td>".$user["creation_date"]."</td>";

                                if ($user["validated"] === 1){
                                    echo "<td>"."SÍ"."</td>";
                                } else {
                                    echo "<td>"."NO"."</td>";
                                }

                                if ($user["deactivated"] === 1){
                                    echo "<td>"."SÍ"."</td>";
                                } else {
                                    echo "<td>"."NO"."</td>";
                                }
                                echo "<td><a href='?id=".$user["user_ID"]."'>Veure detalls</a></td>";

                            echo "</tr>";
                            
                        }

                    ?>
                       
                    </tbody>

                </table>

            </div>

            <div id="pagination">

                <ul>

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
                        echo "<li class='active'><a href='?page=$i'>$i</a></li>"; // Página actual
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
                    logOperation("[USERS.PHP] Connection Error while loading user: ". $e->getMessage(), "ERROR");
                    echo 'Error en la conexión: ' . $e->getMessage();

                    }

                    ?>


                </ul>

            </div>

        <?php } else { ?>

            <?php 
            if(userExists($_GET["id"])) {
            ?>
            <div id="overflow-container">
                
                <div id="title-container">
                        <h1><a href="/admin/users.php">&#8592;</a> Administració de l'usuari <?php echo htmlspecialchars($_GET["id"]); ?></h1>
                </div>

                <div id="user-content">

                    <?php 
                    
                        $selectedUser = searchUserInDatabase("*", "users", $_GET["id"]);
                        $age = getAge($selectedUser["birth_date"]);
                        $userInteractionData = getUserInteractionData($_GET["id"]);
                        $userPhotos = getUserPhotos($_GET["id"]);
                    ?>

                    <script>

                        const userPhotos = <?php echo json_encode($userPhotos); ?>;

                    </script>

                    <div id="user-info">

                        <h2>Informació personal</h2>    
                        <p><strong>Nom: </strong><?php echo htmlspecialchars($selectedUser["name"]); ?></p>
                        <p><strong>Cognom: </strong><?php echo htmlspecialchars($selectedUser["surname"]); ?></p>
                        <p><strong>Alias: </strong><?php echo htmlspecialchars($selectedUser["alias"]); ?></p>
                        <p><strong>Data de naixement: </strong><?php echo htmlspecialchars($selectedUser["birth_date"]); ?></p>
                        <p><strong>Edat: </strong><?php echo htmlspecialchars($age); ?></p>
                        <p><strong>Sexe: </strong><?php echo htmlspecialchars($selectedUser["sex"]); ?></p>
                        <p><strong>Orientació sexual: </strong><?php echo htmlspecialchars($selectedUser["sexual_orientation"]); ?></p>
                        <p><strong>Latitut: </strong><?php echo $selectedUser["latitude"];?></p>
                        <p><strong>Longitut: </strong><?php echo $selectedUser["longitude"];?></p>

                        <h2>Compte</h2>
                        <p><strong>Rol: </strong><?php if($selectedUser["role"] === "user") {echo "Usuari";} else {echo "Administrador";};?></p>
                        <p><strong>Email: </strong><?php echo htmlspecialchars($selectedUser["email"]); ?></p>
                        <p><strong>Data de creació: </strong><?php echo htmlspecialchars($selectedUser["creation_date"]); ?></p>
                        <p><strong>Data d'última conexió: </strong><?php echo htmlspecialchars($selectedUser["last_login_date"]); ?></p>
                        
                        <p><strong>Desactivat: </strong><?php if($selectedUser["deactivated"] === 1) {echo "SÍ";} else {echo "NO";};?></p>
                        <p><strong>Validat: </strong><?php if($selectedUser["validated"] === 1) {echo "SÍ";} else {echo "NO";};?></p>
                        <p><strong>Data d'expiració de la validació: </strong><?php echo htmlspecialchars($selectedUser["expirate_date"]); ?></p>
                        <p><strong>Codi de validació: </strong><?php echo htmlspecialchars($selectedUser["validate_code"]); ?></p>

                        <h2>Preferències</h2>
                        <p><strong>Distància: </strong><?php echo htmlspecialchars($selectedUser["distance_user_preference"]); ?> km</p>
                        <p><strong>Edat mínima: </strong><?php echo htmlspecialchars($selectedUser["min_age_user_preference"]); ?> anys</p>
                        <p><strong>Edat màxima: </strong><?php echo htmlspecialchars($selectedUser["max_age_user_preference"]); ?> anys</p>

                        <h2>Estadístiques</h2>
                        <p><strong>Interaccions realitzades: </strong><?php echo htmlspecialchars($userInteractionData["interactions_done"]); ?></p>
                        <p><strong>Likes donats: </strong><?php echo htmlspecialchars($userInteractionData["given_likes"]); ?></p>
                        <p><strong>Interaccions rebudes: </strong><?php echo htmlspecialchars($userInteractionData["interactions_received"]); ?></p>
                        <p><strong>Likes rebuts: </strong><?php echo htmlspecialchars($userInteractionData["received_likes"]); ?></p>
                        <p><strong>Matches: </strong><?php echo htmlspecialchars($userInteractionData["matches_count"]); ?></p>
                        
                    </div>

                    <div id="user-photos">

                        <img id="user-image">

                    </div>

                </div>

            </div>
            
            <?php } else {
                 header('Location: /admin/users.php'); }
            ?>

        <?php } ?>

        </div>
       
    </div>

</body>
</html>

