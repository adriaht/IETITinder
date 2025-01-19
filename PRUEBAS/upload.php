<?php

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
                handleImageUpload($_FILES["image"]);
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

function handleImageUpload($file){

    if(isset($file)){

        $uploadDir = __DIR__ . "/images//";
        $fileName = basename($file["name"]);
        $targetPath = $uploadDir . $fileName;
    
        // Check for errors
        if ($file["error"] === UPLOAD_ERR_OK) {
            // Move the uploaded file
            if (move_uploaded_file($file["tmp_name"], $targetPath)) {
                echo json_encode(["success" => true, "message" => "File uploaded successfully!", "path" => $targetPath]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to move uploaded file."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Upload error: " . $file["error"]]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No file uploaded."]);
    }

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


           
            <?php
             /*
            // Get reference to uploaded image
            $image_file = $_FILES["image"];

            // Exit if no file uploaded
            if (!isset($image_file)) {
                die('No file uploaded.');
            }

            // Exit if image file is zero bytes
            if (filesize($image_file["tmp_name"]) <= 0) {
                die('Uploaded file has no contents.');
            }

            // Exit if is not a valid image file
            $image_type = exif_imagetype($image_file["tmp_name"]);
            if (!$image_type) {
                die('Uploaded file is not an image.');
            }

            // Get file extension based on file type, to prepend a dot we pass true as the second parameter
            $image_extension = image_type_to_extension($image_type, true);

            // Create a unique image name
            $image_name = bin2hex(random_bytes(16)) . $image_extension;

            // Move the temp image file to the images directory
            move_uploaded_file(
                // Temp image location
                $image_file["tmp_name"],

                // New image location
                __DIR__ . "/images/" . $image_name
            );*/
            ?>
            
        
