<?php


// Retrieve the 'endpoint' from the form data
$endpoint = isset($_POST['endpoint']) ? $_POST['endpoint'] : null;
    
// Make sure endpoint is valid
if ($endpoint === 'pollaGorda') {
    echo json_encode(['message' => $endpoint]);
    exit();
}

// Set upload directory
$uploadDir = __DIR__ . "/images//";

// Check if a file is uploaded
if (isset($_FILES["image"])) {

    $file = $_FILES["image"];
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
            
        
