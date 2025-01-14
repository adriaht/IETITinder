<?php

// Init sessión
session_start();

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Store loggedUser Object
$loggedUser = searchUserInDatabase("*", "users", $_SESSION['user']);

logOperation("Session started in discover.php for user ".$_SESSION['user'], "INFO");

function logOperation($message, $type = "INFO") {

    // Get log directory path
    $logDir = __DIR__ . '/logs';

    // Create directory if it doesn't exist
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755);
    }

    // Get log file name (formato YYYY-MM-DD.txt)
    $logFile = $logDir . '/' . date('Y-m-d') . '.txt';

    // Message formatting
    $timeStamp = date('Y-m-d H:i:s');


    $logMessage = "[$timeStamp] [$type] [USER_ID = ".$_SESSION['user']."] $message\n";

   

    // Write log message in logFile
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function searchUserInDatabase($whatYouWant, $whereYouWant, $userYouWant) {

    try {

        $pdo = startPDO();

        // Create and return a new PDO instance

        $sql = "SELECT $whatYouWant FROM $whereYouWant WHERE user_ID = :loggedUserId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loggedUserId', $userYouWant);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {

            logOperation("No data found for user $userYouWant in function searchUserInDatabase in discover.php", "INFO");
            die("Data not found");
        }

        logOperation("Found data for $userYouWant in function searchUserInDatabase in discover.php" , "INFO");
        // Cerramos conexión
        unset($stmt);
        unset($pdo);

        return $data;

    } catch (PDOException $e) {

        logOperation("Connection error in searchUserInDatabase function in discover.php: " . $e->getMessage(), "ERROR");
        die("Connection error: " . $e->getMessage());
    }

    
}

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
            return 'home, dona';
        }

    } else if ($userOrientation === 'homosexual') {

        return $userSex;

    } else if ($userOrientation === 'bisexual') {

        return 'home, dona, no binari';

    } else {

        return 'Orientación no válida';

    }
    
}

// initialize pdo
function startPDO(){
    $hostname = "localhost";
    $dbname = "IETinder";
    $username = "admin";
    $pw = "admin123";
    return new PDO("mysql:host=$hostname;dbname=$dbname", $username, $pw);
}

// GET REQUESTS
// THIS ENDPOINT will return valid users for loggedUser to discover
// Called by fetchUsers() in JS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_users') {

    try {

        // Initialize BBDD
        $pdo = startPDO();

        // Se calcular las perferencias y se añaden a la consulta SQL para obtener usuarios
        $userID = $loggedUser["user_ID"];
        $userSexTarget = setUserPreferenceForQuery($loggedUser["sex"], $loggedUser["sexual_orientation"]);
        $userSexualOrientation = $loggedUser["sexual_orientation"];
        $userLatitude = $loggedUser["latitude"];
        $userLongitude = $loggedUser["longitude"];

        $parametersForLog = "DATA PASSED TO ALGORITHM: User $userID: [Sex target: $userSexTarget] [Orientation: $userSexualOrientation] [latitude,longitude: $userLatitude , $userLongitude]";
        logOperation($parametersForLog, "INFO");
        
        $sql = "SELECT user_ID, alias, birth_date, sex, sexual_orientation, last_login_date, creation_date, 
                (6371 * acos(cos(radians(:loggedUserLatitude)) 
                            * cos(radians(latitude)) 
                            * cos(radians(longitude) - radians(:loggedUserLongitude)) 
                            + sin(radians(:loggedUserLatitude)) 
                            * sin(radians(latitude)))
                ) AS distance
                FROM users
                WHERE user_ID != :loggedUserId 
                AND sex IN (:loggedUserSexTarget)	
                AND sexual_orientation IN (:loggedUserSexualOrientation, 'bisexual')		
                AND user_ID NOT IN (SELECT `to` FROM interactions WHERE `from` = :loggedUserId AND state = 'like')
                ORDER BY last_login_date DESC, creation_date, distance ASC";

        // Algorithm query

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loggedUserId', $userID);
        $stmt->bindParam(':loggedUserSexTarget', $userSexTarget);
        $stmt->bindParam(':loggedUserSexualOrientation', $userSexualOrientation);
        $stmt->bindParam(':loggedUserLatitude', $userLatitude);
        $stmt->bindParam(':loggedUserLongitude', $userLongitude);
        $stmt->execute();

        // Load user info from the query
        $users = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[$row['user_ID']]['info'] = [
                'user_ID' => $row['user_ID'],
                'alias' => $row['alias'],
                'sex' => $row['sex'],
                'sexual_orientation' => $row['sexual_orientation'],
                'last_login_date' => $row['last_login_date'],
                'distance' => $row['distance']
            ];
        }

        logOperation("Successfully got data of users in discover.php in GET method get_users", "INFO");
        
        if (count($users) > 0) {
            // Get keys (those keys are the users_ID)
            $userIDs = array_keys($users);
            // Makes a string of all of those users_ID (querry will be user_ID IN (string of those ID))
            $userIDsString = implode(',', $userIDs);

            // Selects and loads images
            $sql = "SELECT user_ID, path FROM photos WHERE user_ID IN ($userIDsString)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            // insert in user["photos"]["photo path"]
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[$row['user_ID']]['photos'][] = $row['path'];
            }

            logOperation("Successfully got photos of users in discover.php in GET method get_users", "INFO");
        
        } else {
            
            unset($stmt);
            unset($pdo);

            logOperation("No user matched the algorithm in discover.php in GET method get_users. Returned data to JS", "INFO");

            echo json_encode(['success' => true, 'message' => array_values($users)]);
            exit;
        }
        
        // Cleans stored space for pdo and the query used. 
        unset($stmt);
        unset($pdo);

        logOperation("Successfully got data of users in discover.php in GET method get_users. Returned data to JS", "INFO");

        // Send successful objects of users
        echo json_encode(['success' => true, 'message' => array_values($users)]);
        exit;
    } catch (PDOException $e) {

        logOperation("Connection error in discover.php in GET method get_users: " . $e->getMessage(), "ERROR");
        // catch error and send it to JS
        echo json_encode(['success' => false, 'message' => 'Error en la conexión getUser: ' . $e->getMessage()]);
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

    // Cleans space of the query and PDO
    unset($stmt);
    unset($pdo);

    // LOG
    logOperation("Interaction INSERTED successful: ".$loggedUserID." gave $interactionState to $interactedUserID", "INFO");
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
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Both are successful responses, but we have to register if there's match or no 
    // LOG
    if($row) {
        logOperation("CHECK MATCH: YES between user $interactedUserID and user $loggedUserID", "INFO");
        echo json_encode(['success' => true, 'match' => true, 'message' => "MATCH: l'usuari $loggedUserID i l'usuari $interactedUserID"]);
        exit;
    } else {
        logOperation("CHECK MATCH: NO between user $interactedUserID and user $loggedUserID", "INFO");
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
    
    // Cleans stored space for query and PDO
    unset($stmt);
    unset($pdo);

    logOperation("Match inserted between user $interactedUserID and user $loggedUserID", "INFO");
    // Sends response to 
    echo json_encode(['success' => true, 'message' => "Match creat entre usuari $interactedUserID i usuari $loggedUserID"]);
    exit;
}

// THIS WILL GET ALL POST REQUESTS. Each call in JS will have an endpoint as key_value to handle each endpoint request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // Gets input from the request
        $input = json_decode(file_get_contents('php://input'), true);

        // Send error if there's no input
        if (!$input) {
            logOperation("Invalid input for POST request in discover.php.", "ERROR");
            echo json_encode(['success' => false, 'message' => 'Dades invàlides']);
            exit;
        }

        // Checks if there's an endpoint defined
        if (!isset($input['endpoint'])) {
            logOperation("Endpoint not defined for POST request in discover.php.", "ERROR");
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
            case "insertLog":
                logOperation($input["logMessage"], $input["type"]);

                echo json_encode(['success' => true, 'message' => "Log inserit correctament"]);
                exit;
                
                break;
            default: // In case of 
            logOperation("Endpoint not found for POST request in discover.php. Endpoint sended: ".$input["logMessage"], "ERROR");
                echo json_encode(['success' => false, 'message' => 'Endpoint desconegut.']);
                exit;
        }

    } catch (PDOException $e) {
        logOperation("Connection error in discover.php in POST method: " . $e->getMessage(), "ERROR");
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
<body class="body">

    <div class="container">

        <div class="card">

            <header>
                <p class="logo">IETinder ❤️</p>
            </header>

            <main id="content" class="discover content">

             

            </main>

            <nav>
                <ul>
                    <li><a href="/discover.php">Descobrir</a></li>
                    <li><a href="/messages.php">Missatges</a></li>
                    <li><a href="/profile.php">Perfil</a></li>
                </ul>
            </nav>
        
        </div>
        
    </div>

</body>
</html>

