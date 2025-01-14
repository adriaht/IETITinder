<?php

// Init sessión
session_start();

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}

logOperation("Session started in messages.php for user ".$_SESSION['user'], $input["type"]);

// Store loggedUser Object
$loggedUser = searchUserInDatabase("*", "users", $_SESSION['user']);

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
    $logMessage = "[$timeStamp] [$type] $message\n";

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

            logOperation("No data found for user $userYouWant in function searchUserInDatabase in messages.php", "INFO");
            die("Data not found");
        }

        logOperation("Found data for $userYouWant in function searchUserInDatabase in messages.php" , "INFO");

        // Cerramos conexión
        unset($stmt);
        unset($pdo);

        return $data;

    } catch (PDOException $e) {

        logOperation("Connection error in searchUserInDatabase function in messages.php: " . $e->getMessage(), "ERROR");
        die("Connection error: " . $e->getMessage());
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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_matches') {

    try {

          // Initialize BBDD
        $pdo = startPDO();

        // FIRST SELECTS ALL MATCHS and the user data (match id, userID, alias, path of 1 picture)
        $sql = "SELECT m.match_ID,
                    CASE 
                        WHEN m.participant1 = :loggedUserId THEN u2.user_ID
                        ELSE u1.user_ID
                    END AS user_ID,
                    CASE 
                        WHEN m.participant1 = :loggedUserId THEN u2.alias
                        ELSE u1.alias
                    END AS alias,
                    p.path AS picture_path
                FROM matches m
                LEFT JOIN users u1 ON m.participant1 = u1.user_ID
                LEFT JOIN users u2 ON m.participant2 = u2.user_ID
                LEFT JOIN (
                    SELECT photo_ID, user_ID, path
                    FROM photos
                    WHERE (user_ID, photo_ID) IN (
                        SELECT user_ID, MAX(photo_ID) AS max_photo_ID
                        FROM photos
                        GROUP BY user_ID
                    )
                ) p ON p.user_ID = CASE 
                                    WHEN m.participant1 = :loggedUserId THEN u2.user_ID
                                    ELSE u1.user_ID
                                END
                WHERE 
                    m.participant1 = :loggedUserId OR m.participant2 = :loggedUserId";



        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loggedUserId', $loggedUser["user_ID"]);
        $stmt->execute();
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // For each match, checks if there is an initiated conversation between them. 
        // If it has, hasMessage = true, ELSE = False and appends it to match and user data
        // If there's content, returns last lane (Order BY desc + LIMIT 1) - ELSE = null
        foreach ($matches as $key => $match) { // Use the key to update $matches directly
            $sql = "SELECT content 
                    FROM conversations 
                    WHERE match_ID = :matchID 
                    ORDER BY creation_date DESC 
                    LIMIT 1";
        
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':matchID', $match['match_ID']);
            $stmt->execute();
            $lastMessage = $stmt->fetch(PDO::FETCH_ASSOC);
        
            // Directly update $matches using the key
            $matches[$key]['hasMessage'] = $lastMessage ? true : false;
            $matches[$key]['lastMessage'] = $lastMessage['content'] ?? null;
        }

        unset($stmt);
        unset($pdo);

        logOperation("Successfully got matches of user ".$loggedUser["user_ID"]." messages.php in GET method get_matches: " . $e->getMessage(), "INFO");
        echo json_encode(['success' => true, 'message' => array_values($matches)]);
        exit;

    } catch (PDOException $e) {

        logOperation("Connection error in messages.php for user ".$loggedUser["user_ID"]." in GET method get_matches: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en la conexión: ' . $e->getMessage()]);
        exit;

    }
}    


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // Gets input from the request
        $input = json_decode(file_get_contents('php://input'), true);

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Missatges</title>
    <link rel="stylesheet" type="text/css" href="styles.css?t=<?php echo time();?>" />
    <script src="messages.js"></script>
</head>
<body class="body">

    <div class="container">

        <div class="card">

            <header>
                <p class="logo">IETinder ❤️</p>
                <p>Cercar</p>
            </header>

            <main id="content" class="content messages">
                <div id="container-without-messages">
                    <h2>Els meus matches</h2>
                </div>
                <div id="container-with-messages">
                    <h2>Missatges</h2>
                </div>
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

