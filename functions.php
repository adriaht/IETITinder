<?php

// initialize pdo
function startPDO(){
    $hostname = "localhost";
    $dbname = "IETinder";
    $username = "admin";
    $pw = "admin123";
    return new PDO("mysql:host=$hostname;dbname=$dbname", $username, $pw);
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

?>