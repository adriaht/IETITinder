<?php

// Init sessión
session_start();

include("functions.php"); /* Loads search from users + logs + startPDO */ 


// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Store loggedUser Object
$loggedUser = searchUserInDatabase("*", "users", $_SESSION['user']);

logOperation("[PHOTOS.PHP] Session started for user ".$_SESSION['user']);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_user_photos') {

    try {
        logOperation("[PHOTOS.PHP] User requested own photos.");
        // Initialize BBDD
        $pdo = startPDO();

        // Gets photos of logged user
        $userID = $loggedUser["user_ID"];
        $sql = "SELECT photo_ID, path FROM photos WHERE user_ID = :loggedUserId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loggedUserId', $userID);
        $stmt->execute();
        logOperation("[PHOTOS.PHP] Query sent to get photos: $sql");

        // Load user photos from the query
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cleans stored space for pdo and the query used. 
        unset($stmt);
        unset($pdo);

        logOperation("[PHOTOS.PHP] Successfully got photos of user in GET method get_user_photos. Returned data to JS");

        // Send successful objects of users
        echo json_encode(['success' => true, 'message' => array_values($photos)]);
        exit;

    } catch (PDOException $e) {

       logOperation("[PHOTOS.PHP] Connection error in GET method get_user_photos: " . $e->getMessage(), "ERROR");
        // catch error and send it to JS
        echo json_encode(['success' => false, 'message' => 'Error en la conexión getUser: ' . $e->getMessage()]);
        exit;
    }
  
}

function deletePhoto($input){

    $photoID = $input["photoID"];
    $path = $input["path"];

    logOperation("[PHOTOS.PHP] Starting photo $photoID deletion");

    // Initialize BBDD
    $pdo = startPDO();

    $sql = "DELETE FROM photos WHERE photo_ID = :photoID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':photoID', $photoID);
    $stmt->execute();

    logOperation("[PHOTOS.PHP] Query sent for photo deletion: $sql");
    
    // Cleans stored space for query and PDO
    unset($stmt);
    unset($pdo);
    
    logOperation("[PHOTOS.PHP] Photo $photoID deleted in BBDD successfully");

    // FILE DELETION
    $file = __DIR__ . $path;
    
    if (file_exists($file)) {

        if (unlink($file)) {

            logOperation("[PHOTOS.PHP] File deleted successfully.");
            echo json_encode(['success' => true, 'message' => "Error deleting the file $file"]);
            exit;

        } else {

            logOperation("[PHOTOS.PHP] Error deleting the file $file", "ERROR");
            echo json_encode(['success' => false, 'message' => "Error deleting the file $file"]);
            exit;

        }

    } else {

        logOperation("[PHOTOS.PHP] File $file does not exist.", "ERROR");
        echo json_encode(['success' => false, 'message' => "File $file does not exist."]);
        exit;

    }

    logOperation("[PHOTOS.PHP] Photo $photoID deleted successfully");
    // Sends response to 
    echo json_encode(['success' => true, 'message' => "Photo deleted successfully"]);
    exit;
}

function handleImageUpload($file, $user_ID){

    if(isset($file)) {

        if (filesize($file["tmp_name"]) <= 0) {
            logOperation("[PHOTOS.PHP] Uploaded file has no contents.", "ERROR");
            echo json_encode(["success" => false, "message" => "Upload error: " . 'Uploaded file has no contents.']);
            exit;
        }

        $image_type = exif_imagetype($file["tmp_name"]);
        if (!$image_type) {
            logOperation("[PHOTOS.PHP] Uploaded file is not an image.", "ERROR");
            echo json_encode(["success" => false, "message" => "Upload error: " . 'Uploaded file is not an image.']);
            exit;
        }

        $image_extension = image_type_to_extension($image_type, true);
        $valid_extensions = [".jpg", ".jpeg", ".png", ".webp"];
        if(!in_array($image_extension, $valid_extensions)){
            logOperation("[PHOTOS.PHP] Invalid extension of file uploaded", "ERROR");
            echo json_encode(["success" => false, "message" => "Upload error: " . 'Invalid extension']);
            exit;
        }

        $image_name = bin2hex(random_bytes(16)) . $image_extension;
        logOperation("[PHOTOS.PHP] Name of received image = $image_name");

        $uploadDir = __DIR__ . "/images//";
        $targetPath = $uploadDir . $image_name;
        logOperation("[PHOTOS.PHP] Path to move file = $targetPath");

        $pathForDatabase = "/images/$image_name";
        logOperation("[PHOTOS.PHP] Path to store in database = $pathForDatabase");

        // Check for errors
        if ($file["error"] === UPLOAD_ERR_OK) {

            // Move the uploaded file
            if (move_uploaded_file($file["tmp_name"], $targetPath)) {

                logOperation("[PHOTOS.PHP] File moved successfully, executing insertPhotoInBBDD function with parameters: $image_extension, $pathForDatabase, $user_ID");
                insertPhotoInBBDD($image_extension, $pathForDatabase, $user_ID);

                echo json_encode(["success" => true, "message" => "File uploaded successfully!", "path" => $targetPath]);
                exit;

            } else {

                logOperation("[PHOTOS.PHP] Failed to move uploaded file.");
                echo json_encode(["success" => false, "message" => "Failed to move uploaded file."]);
                exit;

            }

        } else {

            logOperation("[PHOTOS.PHP] Upload error: " . $file["error"], "ERROR");
            echo json_encode(["success" => false, "message" => "Upload error: " . $file["error"]]);
            exit;

        }

    } else {
        logOperation("[PHOTOS.PHP] No file uploaded in PHP", "ERROR");
        echo json_encode(["success" => false, "message" => "No file uploaded."]);
        exit;

    }

}

function insertPhotoInBBDD($type, $path, $user_ID){

    // Gets the input data: usedID of the user the loggedUser interacted with + the state of the interaction (like or dislike)

    $pdo = startPDO(); // Starts PDO

    $extension = str_replace(".", "", $type);

    // Inserts interaction in database (from loggedUser to the user interacted with and the type (like/dislike))
    $sql = "INSERT INTO photos (user_ID, type, path) VALUES (:loggedUserID, :typeOfFile, :pathOfFile)";
    logOperation("[PHOTOS.PHP] Query sent to insert photo in database: $sql");

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loggedUserID', $user_ID);
    $stmt->bindParam(':typeOfFile', $extension);
    $stmt->bindParam(':pathOfFile', $path);
    $stmt->execute();

    // Cleans space of the query and PDO
    unset($stmt);
    unset($pdo);

}

// THIS WILL GET ALL POST REQUESTS. Each call in JS will have an endpoint as key_value to handle each endpoint request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // Gets input from the request
        if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST; // If FormData is sent, data will be in $_POST
        }

        // Send error if there's no input
        if (!$input) {
            logOperation("[PHOTOS.PHP] Invalid input for POST request.", "ERROR");
            echo json_encode(['success' => false, 'message' => 'Dades invàlides']);
            exit;
        }

        // Checks if there's an endpoint defined
        if (!isset($input['endpoint'])) {
            logOperation("[PHOTOS.PHP] Endpoint not defined for POST request.", "ERROR");
            echo json_encode(['success' => false, 'message' => 'Endpoint no especificat.']);
            exit;
        }

        // Gets endpoint and redirects to function that will handle the AJAX call
        switch ($input['endpoint']){

            case "imageUpload": // Endpoint from FormData request
                handleImageUpload($_FILES["image"], $loggedUser["user_ID"]);
                break;
        
            case "deletePhoto": 
                deletePhoto($input);
                break;

            case "insertLog":
                logOperation($input["logMessage"], $input["type"]);
                echo json_encode(['success' => true, 'message' => "Log inserit correctament en Discover.php"]);
                exit;

            default: // In case of 
                logOperation("[PHOTOS.PHP] Endpoint not found for POST request. Endpoint sent: ".$input["logMessage"], "ERROR");
                echo json_encode(['success' => false, 'message' => 'Endpoint desconegut.']);
                exit;
        }

    } catch (PDOException $e) {
        logOperation("[PHOTOS.PHP] Connection error in POST method: " . $e->getMessage(), "ERROR");
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
    <title>IETinder - Fotos</title>
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time();?>" />
    <script src="/photos.js"></script>
</head>
<body>

    <div class="container">

        <div class="card">
        
            <header id="header">
                <p class="logo">IETinder ❤️</p>
            </header>

            <main id="content" class="content photos">
            

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
