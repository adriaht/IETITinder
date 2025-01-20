<?php

// Init sessión
session_start();

$_SESSION["user"] = 1;

include("../functions.php"); /* Loads search from users + logs + startPDO */ 

// $_SESSION['user'] = 7;

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Store loggedUser Object
$loggedUser = searchUserInDatabase("*", "users", $_SESSION['user']);

logOperation("[PROFILE.PHP] Session started");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_user_photos') {

    try {
        logOperation("[PROFILE.PHP] [PHOTOS] User requested own photos.");
        // Initialize BBDD
        $pdo = startPDO();

        // Gets photos of logged user
        $userID = $loggedUser["user_ID"];
        $sql = "SELECT photo_ID, path FROM photos WHERE user_ID = :loggedUserId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loggedUserId', $userID);
        $stmt->execute();
        logOperation("[PROFILE.PHP] [PHOTOS] Query sent: $sql");

        // Load user photos from the query
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cleans stored space for pdo and the query used. 
        unset($stmt);
        unset($pdo);

        logOperation("[PROFILE.PHP] [PHOTOS] Successfully got photos of user in GET method get_user_photos. Returned data to JS");

        // Send successful objects of users
        echo json_encode(['success' => true, 'message' => array_values($photos)]);
        exit;

    } catch (PDOException $e) {

       logOperation("[PROFILE.PHP] [PHOTOS] Connection error in GET method get_user_photos: " . $e->getMessage(), "ERROR");
        // catch error and send it to JS
        echo json_encode(['success' => false, 'message' => 'Error en la conexión getUser: ' . $e->getMessage()]);
        exit;
    }
  
}

function deletePhoto($input){

    $photoID = $input["photoID"];
    $path = $input["path"];

    logOperation("[PROFILE.PHP] [PHOTOS] Starting photo $photoID deletion");

    // Initialize BBDD
    $pdo = startPDO();

    $sql = "DELETE FROM photos WHERE photo_ID = :photoID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':photoID', $photoID);
    $stmt->execute();

    logOperation("[PROFILE.PHP] [PHOTOS] Query sent for photo deletion: $sql");
    
    // Cleans stored space for query and PDO
    unset($stmt);
    unset($pdo);
    
    logOperation("[PROFILE.PHP] [PHOTOS] Photo $photoID deleted in BBDD successfully");

    // FILE DELETION
    $file = __DIR__ . $path;
    
    if (file_exists($file)) {

        if (unlink($file)) {

            logOperation("[PROFILE.PHP] [PHOTOS] File deleted successfully.");
            echo json_encode(['success' => true, 'message' => "Error deleting the file $file"]);
            exit;

        } else {

            logOperation("[PROFILE.PHP] [PHOTOS] Error deleting the file $file", "ERROR");
            echo json_encode(['success' => false, 'message' => "Error deleting the file $file"]);
            exit;

        }

    } else {

        logOperation("[PROFILE.PHP] [PHOTOS] File $file does not exist.", "ERROR");
        echo json_encode(['success' => false, 'message' => "File $file does not exist."]);
        exit;

    }

    // Sends response to 
    echo json_encode(['success' => true, 'message' => "Photo deleted successfully"]);
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

            case "deletePhoto": 
                deletePhoto($input);
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
    <link rel="stylesheet" type="text/css" href="fotos.css?t=<?php echo time();?>" />
    <script src="fotosDin.js"></script>
</head>
<body>

    <div class="container">

        <div class="card">

        
            <header id="header">
                <p class="logo">IETinder ❤️</p>
            </header>

            <main id="content" class="content">
            

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
