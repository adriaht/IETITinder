<?php

function startPDO(){
    $hostname = "localhost";
    $dbname = "IETinder";
    $username = "adminTinder";
    $pw = "admin123";
    return new PDO("mysql:host=$hostname;dbname=$dbname", $username, $pw);
}

$loggedUser = 1;

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
            logOperation("Invalid input for POST request in messages.php.", "ERROR");
            echo json_encode(['success' => false, 'message' => 'Dades invàlides']);
            exit;
        }

        // Checks if there's an endpoint defined
        if (!isset($input['endpoint'])) {
            logOperation("Endpoint not defined for POST request in messages.php.", "ERROR");
            echo json_encode(['success' => false, 'message' => 'Endpoint no especificat.']);
            exit;
        }

        // Gets endpoint and redirects to function that will handle the AJAX call
        switch ($input['endpoint']){

            case "imageUpload": // Endpoint from FormData request
                handleImageUpload($_FILES["image"], $loggedUser);
                break;
        
            case "insertLog":
                logOperation($input["logMessage"], $input["type"]);
                logOperation("Successfully inserted log from client: ".$input["logMessage"], $input["type"]);
                echo json_encode(['success' => true, 'message' => "Log inserit correctament"]);
                exit;
                
                break;
            default: // In case of 
                logOperation("Endpoint not found for POST request in messages.php. Endpoint sended: ".$input["logMessage"], "ERROR");
                echo json_encode(['success' => false, 'message' => 'Endpoint desconegut.']);
                exit;
        }

    } catch (PDOException $e) {
        logOperation("Connection error in messages.php in POST method: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en la conexió: ' . $e->getMessage()]);
        exit;
    }
}

function handleImageUpload($file, $user_ID){

    if(isset($file)) {

        if (filesize($file["tmp_name"]) <= 0) {
            logOperation("Uploaded file has no contents.", "ERROR");
            echo json_encode(["success" => false, "message" => "Upload error: " . 'Uploaded file has no contents.']);
            exit;
        }

        $image_type = exif_imagetype($file["tmp_name"]);
        if (!$image_type) {
            logOperation("Uploaded file is not an image.", "ERROR");
            echo json_encode(["success" => false, "message" => "Upload error: " . 'Uploaded file is not an image.']);
            exit;
        }

        $image_extension = image_type_to_extension($image_type, true);
        $valid_extensions = [".jpg", ".jpeg", ".png", ".webp"];
        if(!in_array($image_extension, $valid_extensions)){
            logOperation("Invalid extension of file uploaded", "ERROR");
            echo json_encode(["success" => false, "message" => "Upload error: " . 'Invalid extension']);
            exit;
        }

        $image_name = bin2hex(random_bytes(16)) . $image_extension;
        logOperation("Name of received image = $image_name");

        $uploadDir = __DIR__ . "/images//";
        $targetPath = $uploadDir . $image_name;
        logOperation("Path to move file = $targetPath");

        $pathForDatabase = "/images/$image_name";
        logOperation("Path to store in database = $pathForDatabase");

        // Check for errors
        if ($file["error"] === UPLOAD_ERR_OK) {

            // Move the uploaded file
            if (move_uploaded_file($file["tmp_name"], $targetPath)) {

                logOperation("File moved successfully, executing insertPhotoInBBDD function with parameters: $image_extension, $pathForDatabase, $user_ID");
                insertPhotoInBBDD($image_extension, $pathForDatabase, $user_ID);

                echo json_encode(["success" => true, "message" => "File uploaded successfully!", "path" => $targetPath]);
                exit;

            } else {

                logOperation("Failed to move uploaded file.");
                echo json_encode(["success" => false, "message" => "Failed to move uploaded file."]);
                exit;

            }

        } else {

            logOperation("Upload error: " . $file["error"], "ERROR");
            echo json_encode(["success" => false, "message" => "Upload error: " . $file["error"]]);
            exit;

        }

    } else {
        logOperation("No file uploaded in PHP", "ERROR");
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
    logOperation($sql);

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loggedUserID', $user_ID);
    $stmt->bindParam(':typeOfFile', $extension);
    $stmt->bindParam(':pathOfFile', $path);
    $stmt->execute();

    // Cleans space of the query and PDO
    unset($stmt);
    unset($pdo);

}

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

?>
