<?php
session_start();

// Handle AJAX login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Dades invàlides']);
        exit;
    }

    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    try {
        $hostname = "localhost";
        $dbname = "IETinder";
        $username = "admin";
        $pw = "holacaracola";
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", "$username", "$pw");
        
        // Busca el usuario
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuari no trobat']);
            exit;
        }
        
        if ($password !== $user['password']) {            
            echo json_encode(['success' => false, 'message' => 'Contrasenya incorrecta']); //Cambiar a password_verify() cuando las contraseñas esten encriptadas
            exit;
        }
        
        
        // Actualizar ultimo login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login_date = CURRENT_TIMESTAMP WHERE user_ID = :id");
        $updateStmt->bindParam(':id', $user['user_ID']);
        $updateStmt->execute();
        
        // Session
        $_SESSION['user'] = [
            'id' => $user['user_ID'],
            'email' => $user['email'],
            'name' => $user['name']
        ];
        
        
        echo json_encode([
            'success' => true,
            'message' => [
                'id' => $user['user_ID'],
                'email' => $user['email'],
                'name' => $user['name']
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de connexió. Torna-ho a intentar més tard.']);
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="index.js"></script>
</head>
<body class="body-login">
    <div class="container">
        <div class="login-card">
            <div class="logo-login">IETinder ❤️</div>
            
            <form id="loginForm">
                <div class="input-group">
                    <label for="email">Correu electrònic</label>
                    <input type="email" id="email" placeholder="alumne@ieti.site" required>
                </div>

                <div class="input-group">
                    <label for="password">Contrasenya</label>
                    <div class="password-input">
                        <input type="password" id="password" placeholder="••••••••" required>
                        <!--<button type="button" id="togglePassword" class="toggle-password">👁️</button>-->
                    </div>
                </div>

                <div id="errorMessage" class="error-message"></div>

                <button type="submit" id="submitButton" class="primary-button">Iniciar Sessió</button>

                <div class="button-group">
                    <button type="button" class="secondary-button">Recuperar Compte</button>
                    <button type="button" class="secondary-button">Crear Compte</button>
                </div>
            </form>

            <p class="footer-text">Troba l'amor a l'Institut Esteve Terradas i Illa</p>
        </div>
    </div>
</body>
</html>