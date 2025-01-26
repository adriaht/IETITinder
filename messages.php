<?php

// Init sessión
session_start();
include("functions.php"); /* Loads search from users + logs + startPDO */ 

// Check if session is active. Otherwise, get to login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

logOperation("[MESSAGES.PHP] Session started in messages.php for user ".$_SESSION['user'], "INFO");

// Store loggedUser Object
$loggedUser = searchUserInDatabase("*", "users", $_SESSION['user']);

//GET MATCH ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['getMatchID'])) {
    try {
        $alias = $_GET['getMatchID'];

        // Initialize BBDD
        $pdo = startPDO();

        // SQL query
        $sql = "SELECT m.match_ID
                FROM matches m
                JOIN users u1 ON (m.participant1 = u1.user_ID OR m.participant2 = u1.user_ID)
                JOIN users u2 ON (m.participant1 = u2.user_ID OR m.participant2 = u2.user_ID)
                WHERE u1.user_ID = :user
                AND u2.alias = :alias;";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user', $loggedUser["user_ID"], PDO::PARAM_INT);
        $stmt->bindValue(':alias', $alias, PDO::PARAM_STR);
        $stmt->execute();
        $match = $stmt->fetchColumn(); //Devuelve el match_ID si existe

        // Verifica si se encontró un match
        if ($match === false) {
            echo json_encode(['success' => false, 'message' => 'No se encontraron resultados.']);
        } else {
            logOperation("[MESSAGES.PHP] Successfully got matchID from user_ID: ".$loggedUser["user_ID"]." and alias: ".$alias." in GET method getMatchID" , "INFO");
            echo json_encode(['success' => true, 'match_ID' => $match]);
        }
        exit;

    } catch (PDOException $e) {
        logOperation("[MESSAGES.PHP] Connection error in messages.php for user_ID: ".$loggedUser["user_ID"]." and alias: ".$alias." in GET method getMatchID: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en la conexión: ' . $e->getMessage()]);
        exit;
    }
}

// GET LOGGED USER
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getLoggedUserID') {
    try {
        logOperation("[MESSAGES.PHP] Successfully got UserID from logged user ".$loggedUser["user_ID"]." in GET method getLoggedUserID" , "INFO");
        echo json_encode(['success' => true, 'message' => array_values($loggedUser)]);
        exit;

    } catch (PDOException $e) {

        logOperation("[MESSAGES.PHP] Connection error for user ".$loggedUser["user_ID"]." in GET method getLoggedUserID: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en la conexión: ' . $e->getMessage()]);
        exit;

    }
}    

//GET MATCHES
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
                        SELECT user_ID, MIN(photo_ID) AS max_photo_ID
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

        logOperation("[MESSAGES.PHP] Successfully got matches from user_ID: ".$loggedUser["user_ID"]." in GET method get_matches" , "INFO");
        echo json_encode(['success' => true, 'message' => array_values($matches)]);
        exit;

    } catch (PDOException $e) {

        logOperation("[MESSAGES.PHP] Connection error for user_ID: ".$loggedUser["user_ID"]." in GET method get_matches: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en la conexión: ' . $e->getMessage()]);
        exit;

    }
}    

//GET CONVERSATION
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['getConversation'])) {
    
    try {
        $alias = $_GET['getConversation'];
        $user = $loggedUser["user_ID"];

        $pdo = startPDO();
        $sql = "SELECT c.* 
                FROM conversations c
                INNER JOIN matches m ON c.match_ID = m.match_ID
                WHERE m.match_ID = (
                    SELECT match_ID
                    FROM matches
                    WHERE (participant1 = :user AND participant2 = (
                        SELECT user_ID 
                        FROM users 
                        WHERE alias = :alias
                    )) OR (participant1 = (
                        SELECT user_ID 
                        FROM users 
                        WHERE alias = :alias
                    ) AND participant2 = :user)
                )
            ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':alias', $alias);
        $stmt->bindParam(':user', $user);
        $stmt->execute();
        $conversation = $stmt->fetchAll(PDO::FETCH_ASSOC);

        unset($stmt);
        unset($pdo);

        logOperation("[MESSAGES.PHP] Successfully got conversation from matchID: " . $matchID . " in GET method getConversation", "INFO");
        echo json_encode(['success' => true, 'message' => array_values($conversation)]);
        exit;

    } catch (PDOException $e) {

        logOperation("[MESSAGES.PHP] Connection error from matchID: " . $matchID . " in GET method getConversation: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en la conexión: ' . $e->getMessage()]);
        exit;

    }
}

//GET USER NAME AND IMAGE
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['getUserNameAndImage'])) {
    
    try {
        $alias = $_GET['getUserNameAndImage'];

        $pdo = startPDO();
        $sql = "SELECT u.name, p.path, TIMESTAMPDIFF(YEAR, u.birth_date, CURDATE()) AS age
                FROM users u 
                LEFT JOIN photos p ON u.user_ID = p.user_ID 
                WHERE u.alias = :alias LIMIT 1;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':alias', $alias);
        $stmt->execute();
        $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

        unset($stmt);
        unset($pdo);

        logOperation("[MESSAGES.PHP] Successfully got UserName and Image from user alias: " . $alias . " in GET method getUserNameAndImage", "INFO");
        echo json_encode(['success' => true, 'message' => array_values($user)]);
        exit;

    } catch (PDOException $e) {

        logOperation("[MESSAGES.PHP] Connection error for user alias: " . $alias . " in GET method getUserNameAndImage: " . $e->getMessage(), "ERROR");
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
            logOperation("[MESSAGES.PHP] Invalid input for POST request in messages.php.", "ERROR");
            echo json_encode(['success' => false, 'message' => 'Dades invàlides']);
            exit;
        }

        // Checks if there's an endpoint defined
        if (!isset($input['endpoint'])) {
            logOperation("[MESSAGES.PHP] Endpoint not defined for POST request in messages.php.", "ERROR");
            echo json_encode(['success' => false, 'message' => 'Endpoint no especificat.']);
            exit;
        }

        // Gets endpoint and redirects to function that will handle the AJAX call
        switch ($input['endpoint']){
            case "insertMessage":
                $matchID = $input['matchID'] ?? null;
                $senderID = $input['senderID'] ?? null;
                $messageContent = $input['messageContent'] ?? null;
                insertMessage($matchID, $senderID, $messageContent);
                break;
            case "insertLog":
                logOperation($input["logMessage"], $input["type"]);
                logOperation("[MESSAGES.PHP] Successfully inserted log from client: ".$input["logMessage"], $input["type"]);
                echo json_encode(['success' => true, 'message' => "Log inserit correctament"]);
                exit;
                
            default: // In case of 
                logOperation("[MESSAGES.PHP] Endpoint not found for POST request in messages.php. Endpoint sended: ".$input["logMessage"], "ERROR");
                echo json_encode(['success' => false, 'message' => 'Endpoint desconegut.']);
                exit;
        }

    } catch (PDOException $e) {
        logOperation("[MESSAGES.PHP] Connection error in messages.php in POST method: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en la conexió: ' . $e->getMessage()]);
        exit;
    }
}



//Funcion para insertar mensajes en la BBDD
function insertMessage($matchID, $senderID, $messageContent){
       
    try {
        $pdo = startPDO();

        $sql = "INSERT INTO conversations (match_ID, sender_id, content) VALUES (:matchID, :senderID, :messageContent)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':matchID', $matchID);
        $stmt->bindParam(':senderID', $senderID);
        $stmt->bindParam(':messageContent', $messageContent);
        $stmt->execute();
        
        unset($stmt);
        unset($pdo);

        logOperation("[MESSAGES.PHP] Message inserted in matchID: $matchID conversation, sender is: $senderID , content is: $messageContent , in FUNCTION insertMessage", "INFO");
        echo json_encode(['success' => true, 'message' => "Missatge enviat per usuari $senderID , en el chat del match $matchID , amb el contingut $messageContent"]);
        exit;

    } catch (PDOException $e) {
        logOperation("[MESSAGES.PHP] Connection error in messages.php for matchID: $matchID conversation, sender is: $senderID , content is: $messageContent in FUNCTION insertMessage: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en la conexión: ' . $e->getMessage()]);
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
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time();?>" />
    <script src="messages.js"></script>
</head>
<body class="body">

    <div class="container">

        <div class="card">

            <header>
                <p class="logo">IETinder ❤️</p>
                <p class="find">Cercar</p>
            </header>

            <!-- CONTENIDO MESSAGES -->
            <main id="content" class="content messages">
                <div id="container-without-messages">
                    <h2>Els meus matches</h2>
                </div>
                <div id="container-with-messages">
                    <h2>Missatges</h2>
                </div>
            </main>

            <!--CONTENIDO CHAT -->
            <main id="chat-page" style="display:none" >

                <!-- Menú de chat-->
                <div class="chat-header">
                    <div class="chat-header-left">
                        <a id="goBackToMessages" href="messages.php">🡄</a>
                        <img id="chat-image" src="/images/user1_photo1.jpg">
                        <h3 id="chat-name"></h3>
                    </div>
                    <div class="chat-header-right">
                        <button id="submenu-button" class="button-submenu">· · ·</button>
                    </div>

                </div>
                <!-- Botones de las tabs -->
                <div class="chat-tabs">
                    <button class="chat-tablinks" onclick="openTab(event, 'chatTabs-chat')"
                        id="defaultOpen">Conversa</button>
                    <button class="chat-tablinks" onclick="openTab(event, 'chatTabs-profile')">Perfil</button>
                </div>

                <!-- TAB de CHAT -->
                <div id="chatTabs-chat" class="tabcontent">
                    <!-- Div para los mensajes -->
                    <div id="chat-messages-container">
                    </div>
                    <!-- Div para escribir -->
                    <div id="chat-input-container">
                        <input type="text" id="chat-text-input">
                        <button id="chat-send-button">⮞</button>
                    </div>
                </div>

                <!-- TAB de PERFIL -->
                <div id="chatTabs-profile" class="tabcontent">
                    <img id="profileTab-img" src="" alt="profile picture">
                    <div id="profileTab-info">
                        <h2 id="profileTab-name"></h2>
                        <h3 id="profileTab-age"></h3>
                    </div>

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

