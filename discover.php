<?php

// Init sessión
session_start();

include("functions.php"); /* Loads search from users + logs + startPDO */ 

// $_SESSION['user'] = 7;

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Store loggedUser Object
$loggedUser = searchUserInDatabase("*", "users", $_SESSION['user']);

logOperation("[DISCOVER.PHP] Session started for user ".$_SESSION['user']);


// FUNCTION TO ESTABLISH USER preference. Ex: if heterosexual, then return opposite sex.
// Used in query to get users based on the loggedUser gender sex (sex IN (home, dona...) )
function setUserPreferenceForQuery($userSex, $userOrientation) {

    if ($userOrientation === 'heterosexual') {
        // Return opposite
        if ($userSex === 'home') {
            return 'dona';
        } else if ($userSex === 'dona') {
            return 'home';
        } else if ($userSex === 'no binari') {
            return "'home', 'dona'";
        }

    } else if ($userOrientation === 'homosexual') {

        return $userSex;

    } else if ($userOrientation === 'bisexual') {

        return "'home', 'dona', 'no binari'";

    } else {

        return 'Orientación no válida';

    }
    
}

// GET REQUESTS
// THIS ENDPOINT will return valid users for loggedUser to discover
// Called by fetchUsers() in JS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_users') {

    try {

        // Initialize BBDD
        $pdo = startPDO();

        // Gets user parameteres and filter based on this data
        $userID = $loggedUser["user_ID"];
        $userSex = $loggedUser["sex"];
        $userSexTarget = setUserPreferenceForQuery($loggedUser["sex"], $loggedUser["sexual_orientation"]);
        $userSexualOrientation = $loggedUser["sexual_orientation"];
        $userLatitude = $loggedUser["latitude"];
        $userLongitude = $loggedUser["longitude"];

        // Preferences
        $userMaxDistancePreference = $loggedUser["distance_user_preference"];;
        $userMinAgePreference = $loggedUser["min_age_user_preference"];
        $userMaxAgePreference  = $loggedUser["max_age_user_preference"];

        $parametersForLog = "[DISCOVER.PHP] DATA PASSED TO ALGORITHM: User $userID | User sex: $userSex | Orientation: $userSexualOrientation | Sex target: $userSexTarget | latitude,longitude: $userLatitude , $userLongitude | MAX DISTANCE = $userMaxDistancePreference| MIN AGE = $userMinAgePreference |  MAX AGE =  $userMaxAgePreference |";
        logOperation($parametersForLog);
        
        $sql = "";

        if ($userSex === "no binari" || $userSexualOrientation === "bisexual") {
            
            $sql = "SELECT user_ID, name, alias, TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) AS age, 
            (((SELECT COUNT(`from`)  FROM interactions WHERE `from` = user_ID) * 0.6) + 
            ((SELECT COUNT(`from`)  FROM interactions WHERE `to` = user_ID AND `state` = 'like') * 0.15) +
            ((((SELECT COUNT(`from`)  FROM interactions WHERE `to` = user_ID AND `state` = 'like') / (SELECT COUNT(`from`)  FROM interactions WHERE `to` = user_ID)) * 75) * 0.25)) as ponderation,
            sex, sexual_orientation, last_login_date, creation_date, 
            (6371 * acos(cos(radians(40.7128)) * cos(radians(latitude)) * cos(radians(longitude) - radians(-74.006)) + sin(radians(40.7128)) * sin(radians(latitude)))) AS distance
            FROM users
            WHERE user_ID != :loggedUserId 
            AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN :loggedUserMinAge AND :loggedUserMaxAge
            AND (6371 * acos(cos(radians(:loggedUserLatitude)) 
                        * cos(radians(latitude)) 
                        * cos(radians(longitude) - radians(:loggedUserLongitude)) 
                        + sin(radians(:loggedUserLatitude)) 
                        * sin(radians(latitude)))
            ) <= :loggedUserDistance
            AND user_ID NOT IN (SELECT `to` FROM interactions WHERE `from` = :loggedUserId AND state = 'like')
            AND user_ID NOT IN (SELECT `to` FROM interactions WHERE `from` = :loggedUserId AND state = 'dislike' AND interaction_date >= NOW() - INTERVAL 3 HOUR)
            ORDER BY ponderation DESC, last_login_date DESC, creation_date, distance ASC";

        } else {

        $sql = "SELECT user_ID, name, alias, TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) AS age, 
            (((SELECT COUNT(`from`)  FROM interactions WHERE `from` = user_ID) * 0.6) + 
            ((SELECT COUNT(`from`)  FROM interactions WHERE `to` = user_ID AND `state` = 'like') * 0.15) +
            ((((SELECT COUNT(`from`)  FROM interactions WHERE `to` = user_ID AND `state` = 'like') / (SELECT COUNT(`from`)  FROM interactions WHERE `to` = user_ID)) * 75) * 0.25)) as ponderation,
            sex, sexual_orientation, last_login_date, creation_date, 
            (6371 * acos(cos(radians(40.7128)) * cos(radians(latitude)) * cos(radians(longitude) - radians(-74.006)) + sin(radians(40.7128)) * sin(radians(latitude)))) AS distance
            FROM users
            WHERE user_ID != :loggedUserId 
            AND sex IN (:loggedUserSexTarget)	
            AND sexual_orientation IN (:loggedUserSexualOrientation, 'bisexual')
            AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN :loggedUserMinAge AND :loggedUserMaxAge
            AND (6371 * acos(cos(radians(:loggedUserLatitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:loggedUserLongitude)) + sin(radians(:loggedUserLatitude)) * sin(radians(latitude)))) <= :loggedUserDistance
            AND user_ID NOT IN (SELECT `to` FROM interactions WHERE `from` = :loggedUserId AND state = 'like')
            AND user_ID NOT IN (SELECT `to` FROM interactions WHERE `from` = :loggedUserId AND state = 'dislike' AND interaction_date >= NOW() - INTERVAL 3 HOUR)
            ORDER BY ponderation DESC, last_login_date DESC, creation_date, distance ASC";
        }

        logOperation("[DISCOVER.PHP] Sent query to get users: $sql");

        // Algorithm query

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loggedUserId', $userID);

        if ($userSex != "no binari" && $userSexualOrientation != "bisexual") {

            $stmt->bindParam(':loggedUserSexTarget', $userSexTarget);
            $stmt->bindParam(':loggedUserSexualOrientation', $userSexualOrientation);
        }

        $stmt->bindParam(':loggedUserLatitude', $userLatitude);
        $stmt->bindParam(':loggedUserLongitude', $userLongitude);
        $stmt->bindParam(':loggedUserDistance', $userMaxDistancePreference);
        $stmt->bindParam(':loggedUserMinAge', $userMinAgePreference);
        $stmt->bindParam(':loggedUserMaxAge', $userMaxAgePreference);
        $stmt->execute();

        // Load user info from the query
        $users = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[$row['user_ID']]['info'] = [
                'user_ID' => $row['user_ID'],
                'name' => $row['name'],
                'age' => $row['age'],
                'alias' => $row['alias'],
                'sex' => $row['sex'],
                'sexual_orientation' => $row['sexual_orientation'],
                'last_login_date' => $row['last_login_date'],
                'distance' => $row['distance']
            ];

        }

        logOperation("[DISCOVER.PHP] Got data of ".count($users)." user in query get_users");
        logOperation("[DISCOVER.PHP] Successfully got data of users in GET method get_users");
        
        if (count($users) > 0) {
            // Get keys (those keys are the users_ID)
            $userIDs = array_keys($users);
            // Makes a string of all of those users_ID (querry will be user_ID IN (string of those ID))
            $userIDsString = implode(',', $userIDs);

            // Selects and loads images
            $sql = "SELECT user_ID, path FROM photos WHERE user_ID IN ($userIDsString)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            logOperation("[DISCOVER.PHP] Query sent to get photos: $sql");

            // insert in user["photos"]["photo path"]
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[$row['user_ID']]['photos'][] = $row['path'];
            }

            logOperation("[DISCOVER.PHP] Successfully got photos of users in GET method get_users", "INFO");
        
        } else {
            
            unset($stmt);
            unset($pdo);

            logOperation("[DISCOVER.PHP] No user matched the algorithm in GET method get_users. Returned data to JS", "INFO");

            echo json_encode(['success' => true, 'message' => array_values($users)]);
            exit;
        }
        
        // Cleans stored space for pdo and the query used. 
        unset($stmt);
        unset($pdo);

        logOperation("[DISCOVER.PHP] Successfully got data of users in GET method get_users. Returned data to JS", "INFO");

        // Send successful objects of users
        echo json_encode(['success' => true, 'message' => array_values($users)]);
        exit;
    } catch (PDOException $e) {

       logOperation("[DISCOVER.PHP] Connection error in GET method get_users: " . $e->getMessage(), "ERROR");
        // catch error and send it to JS
        echo json_encode(['success' => false, 'message' => 'Error en la conexión getUser: ' . $e->getMessage()]);
        exit;
    }
  
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_logged_user_preferences') {

    try {

        // Initialize BBDD
        $pdo = startPDO();

        // Se calcular las perferencias y se añaden a la consulta SQL para obtener usuarios
        $userID = $loggedUser["user_ID"];

        logOperation("[DISCOVER.PHP] Started to get user preferences");
      
        $sql = "SELECT user_ID, distance_user_preference, min_age_user_preference, max_age_user_preference
        FROM users
        WHERE user_ID = :loggedUserId";
        
        logOperation("[DISCOVER.PHP] Sent query to get user preferences: $sql");

        // Algorithm query

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loggedUserId', $userID);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cleans stored space for pdo and the query used. 
        unset($stmt);
        unset($pdo);

        logOperation("[DISCOVER.PHP] Successfully got data of user preference in GET method get_logged_user_preferences. Returned data to JS");

        // Send successful objects of users
        echo json_encode(['success' => true, 'message' => $user]);
        exit;

    } catch (PDOException $e) {

        logOperation("[DISCOVER.PHP] Connection error in discover.php in GET method get_logged_user_preferences: " . $e->getMessage(), "ERROR");

        // catch error and send it to JS
        echo json_encode(['success' => false, 'message' => 'Error en la conexión get_logged_user_preferences: ' . $e->getMessage()]);
        exit;
    }
  
}

// POST REQUESTS (functions to handle each endpoint).
// $_SERVER['REQUEST_METHOD'] === 'POST' --> handles endpoints with switch/case

// INSERTS INTERACTION (like / dislike) in BBDD (from = user logged in + to = user interacted with)
function insertInteraction($input, $loggedUserID){

    // Gets the input data: usedID of the user the loggedUser interacted with + the state of the interaction (like or dislike)
    $interactedUserID = $input['interactedUserID'];
    $interactionState = $input['interactionState'];

    $pdo = startPDO(); // Starts PDO

    // Inserts interaction in database (from loggedUser to the user interacted with and the type (like/dislike))
    $sql = "INSERT INTO interactions (`from`, `to`, `state`) VALUES (:loggedUserID, :dislikedUserID, :state)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loggedUserID', $loggedUserID);
    $stmt->bindParam(':dislikedUserID', $interactedUserID);
    $stmt->bindParam(':state', $interactionState);
    $stmt->execute();

    logOperation("[DISCOVER.PHP] Query sent to INSERT INTERACTION: $sql", "INFO");

    // Cleans space of the query and PDO
    unset($stmt);
    unset($pdo);

    // LOG
    logOperation("[DISCOVER.PHP] Interaction INSERTED successful: ".$loggedUserID." gave $interactionState to $interactedUserID", "INFO");
    echo json_encode(['success' => true, 'message' => "Interaction successful: ".$loggedUserID. " gave $interactionState to $interactedUserID"]);
    exit;
}

// Checks if there is a LIKE from the USER the logged user interacted with. If there's a result, then there is a match
function checkMatch($input, $loggedUserID){

    // Gets ID of user interacted
    $interactedUserID = $input['interactedUserID'];

    $pdo = startPDO(); // Starts PDO

    // Selects if from =  interacted user AND to = loggedUser AND state = like (since we register likes and dislikes)
    $sql = "SELECT `from` FROM interactions WHERE `from` = :fromLikedUser AND `to` = :toLoggedUser AND `state` = 'like'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':fromLikedUser', $interactedUserID);
    $stmt->bindParam(':toLoggedUser', $loggedUserID);
    $stmt->execute();

    logOperation("[DISCOVER.PHP] Query sent to CHECK MATCH: $sql", "INFO");

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Both are successful responses, but we have to register if there's match or no 
    // LOG
    if($row) {
        logOperation("[DISCOVER.PHP] CHECK MATCH: YES between user $interactedUserID and user $loggedUserID", "INFO");
        echo json_encode(['success' => true, 'match' => true, 'message' => "MATCH: l'usuari $loggedUserID i l'usuari $interactedUserID"]);
        exit;
    } else {
        logOperation("[DISCOVER.PHP] CHECK MATCH: NO between user $interactedUserID and user $loggedUserID", "INFO");
        echo json_encode(['success' => true, 'match' => false, 'message' => "NO MATCH: hi ha match $loggedUserID y i  $interactedUserID"]);
        exit;
    }
}

// Inserts match if there was one (after check match) --> Handled in JAVASCRIPT
function insertMatch($input, $loggedUserID){

    // Gets ID of user interacted with from input
    $interactedUserID = $input['interactedUserID'];

    // Starts PDO
    $pdo = startPDO();

    // Inserts match
    $sql = "INSERT INTO matches (participant1, participant2) VALUES (:loggedUser, :likedUser)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':likedUser', $loggedUserID);
    $stmt->bindParam(':loggedUser', $interactedUserID);
    $stmt->execute();
    
    logOperation("[DISCOVER.PHP] Query sent to INSERT MATCH: $sql", "INFO");

    // Cleans stored space for query and PDO
    unset($stmt);
    unset($pdo);

    logOperation("[DISCOVER.PHP] Match inserted between user $interactedUserID and user $loggedUserID", "INFO");
    // Sends response to 
    echo json_encode(['success' => true, 'message' => "Match creat entre usuari $interactedUserID i usuari $loggedUserID"]);
    exit;
}

function updateUserPreferences($input, $loggedUserID) {

    // Get inputs of distance, min_age, and max_age
    $distance = $input['distance'];
    $min_age = $input['minAge'];
    $max_age = $input['maxAge'];

    logOperation("[DISCOVER.PHP] User preference validation: inputs are distance($distance), min_age($min_age), max_age($max_age).");

    $age_range = [18, 60];
    $errors = [];

    // CHECKS
    // Distance between valid parameters
    if ($distance < 0 || $distance > 200) {
        $errors[] = "La distància ha d'estar entre 0 i 200 km.";
    }

    // Age between valid parameters
    if ($min_age < $age_range[0] || $min_age > $age_range[1] || $max_age < $age_range[0] || $max_age > $age_range[1]) {
       $errors[] = "La edat ha d'estar entre el rang de 18 i 60 anys.";
    }
     // Min age can't be greater than max age
    if ($min_age > $max_age) {
        $errors[] = "La edat mínima no pot ser superior a la màxima.";
    }

    // If errors, return them to JS
    if(count($errors) > 0) {
        logOperation("[DISCOVER.PHP] Validation for user preferences failed. Inputs don't meet distance( 0 - 200) or age ranges (".$age_range[0]." - ".$age_range[0].")");
        echo json_encode(['success' => true, "updated" => false, 'message' => implode("\n", $errors)]);
        exit;
    }

    // ELSE DATA IS GOOD --> starts update
     // Starts PDO
     $pdo = startPDO();
    
     // Update user preference

     $sql = "UPDATE users SET distance_user_preference = :distance, min_age_user_preference = :min_age, max_age_user_preference = :max_age
     WHERE user_ID = :loggedUser";
     $stmt = $pdo->prepare($sql);
     $stmt->bindParam(':distance', $distance);
     $stmt->bindParam(':min_age', $min_age);
     $stmt->bindParam(':max_age', $max_age);
     $stmt->bindParam(':loggedUser', $loggedUserID);
     $stmt->execute();
     logOperation("[DISCOVER.PHP] Query sent to update user preferences: $sql");

     // Cleans stored space for query and PDO
     unset($stmt);
     unset($pdo);
 
     logOperation("[DISCOVER.PHP] Successfully updated user preferences");
     
     // Sends response to 
     echo json_encode(['success' => true, "updated" => true, 'message' => "Successfully updated user preferences"]);
     exit;

}

// THIS WILL GET ALL POST REQUESTS. Each call in JS will have an endpoint as key_value to handle each endpoint request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {

        // Gets input from the request 
        $input = json_decode(file_get_contents('php://input'), true);

        // Send error if there's no input
        if (!$input) {
            logOperation("[DISCOVER.PHP] Invalid input for POST request.", "ERROR");
            echo json_encode(['success' => false, 'message' => 'Dades invàlides']);
            exit;
        }

        // Checks if there's an endpoint defined
        if (!isset($input['endpoint'])) {
            logOperation("[DISCOVER.PHP] Endpoint not defined for POST request.", "ERROR");
            echo json_encode(['success' => false, 'message' => 'Endpoint no especificat.']);
            exit;
        }

        // Gets endpoint and redirects to function that will handle the AJAX call
        switch ($input['endpoint']){
            case "insertInteraction":
                insertInteraction($input, $loggedUser["user_ID"]);
                break;
            case "checkMatch": 
                checkMatch($input, $loggedUser["user_ID"]);
                break;
            case "insertMatch": 
                insertMatch($input, $loggedUser["user_ID"]);
                break;
            case "updateUserPreferences": 
                updateUserPreferences($input, $loggedUser["user_ID"]);
                break;
            case "insertLog":
                logOperation($input["logMessage"], $input["type"]);
                echo json_encode(['success' => true, 'message' => "Log inserit correctament en Discover.php"]);
                exit;

            default: // In case of 
                logOperation("[DISCOVER.PHP] Endpoint not found for POST request. Endpoint sent: ".$input["logMessage"], "ERROR");
                echo json_encode(['success' => false, 'message' => 'Endpoint desconegut.']);
                exit;
        }

    } catch (PDOException $e) {
        logOperation("[DISCOVER.PHP] Connection error in POST method: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en la conexió: ' . $e->getMessage()]);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Descobrir</title>
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time();?>" />
    <script src="discover.js"></script>
</head>
<body class="discover">

    <div class="container">

        <div class="card">
        <div id="grey-background"></div>
        
            <header id="header">
                <p class="logo">IETinder ❤️</p>
                <button id="submenu-button" class="button-submenu">· · ·</button>
            </header>

            <main id="content" class="discover content">

             

            </main>

            <nav>
                <ul>
                    <li><a id="navDiscover" href="/discover.php">Descobrir</a></li>
                    <li><a id="navMessages" href="/messages.php">Missatges</a></li>
                    <li><a id="navProfile" href="/profile.php">Perfil</a></li>
                </ul>
            </nav>
        
        </div>
        
    </div>

</body>
</html>

