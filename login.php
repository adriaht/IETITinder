<?php
session_start();

// Función para iniciar la conexión a la base de datos
function startPDO() {
    $hostname = "localhost";
    $dbname = "IETinder";
    $username = "admin";
    $password = "admin123";

    try {
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // LOG (Error en la conexión a la BDD)
        error_log("Error de conexión a la BDD: " . $e->getMessage());
        die(json_encode(['success' => false, 'message' => 'Error de connexió a la base de dades.']));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Dades invàlides']);
        exit;
    }

    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    try {
        $pdo = startPDO();

        // Buscar si el usuario existe
        $stmt = $pdo->prepare("SELECT user_ID, password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch();

        // Si el usuario no existe, mostrar error
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuari y contrasenya incorrectes']);
            exit;
        }

        // Verificar si la contraseña es correcta 
        $stmtpwd = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND password = SHA2(:password, 512)");
        $stmtpwd->bindParam(':email', $email);
        $stmtpwd->bindParam(':password', $password);
        $stmtpwd->execute();
        
        $passwordCorrecta = $stmtpwd->fetchColumn(); // Obtiene 1 si la contraseña es correcta, 0 si no        

        if (!$passwordCorrecta) {
            echo json_encode(['success' => false, 'message' => 'Contrasenya incorrecta']);
            exit;
        }

        // Actualizar el último login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login_date = CURRENT_TIMESTAMP WHERE user_ID = :id");
        $updateStmt->bindParam(':id', $user['user_ID']);
        $updateStmt->execute();
        
        $_SESSION['user'] = $user['user_ID'];
        // LOG (Login correcto)
        echo json_encode(['success' => true, 'message' => $user]);

    } catch (PDOException $e) {
        // LOG (Error en la base de datos)
        error_log('Database error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de connexió. Torna-ho a intentar més tard.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="index.js"></script>
</head>
<body class="body-login">
<div class="container">
        <div class="card" id="login-card">
            <div class="card-header">
                <div class="logo-login">IETinder ❤️</div>
                <p class="footer-text">Troba l'amor a l'Institut Esteve Terradas i Illa</p>
            </div>

            <form id="loginForm">
                <div class="input-group" id="emailGroup">
                    <label for="email">Correu electrònic</label>
                    <input type="email" id="email" placeholder="" autocomplete="off" required>
                </div>

                <div class="input-group" id="passwordGroup">
                    <label for="password">Contrasenya</label>
                    <div class="password-input">
                        <input type="password" id="password" placeholder="" autocomplete="off" required>
                    </div>
                </div>

                <div id="errorMessage" class="error-message"></div>

                <button type="submit" id="submitButton" class="primary-button">Iniciar Sessió</button>

                <div class="links-group">
                    <a href="#" class="secondary-link">¿Has oblidat la contrasenya?</a>
                    <a href="#" class="secondary-link">Crea una compte nova</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>