<?php
session_start();

// Check if session is active. Otherwise, get to login
 if (isset($_SESSION['user'])) {
     header('Location: discover.php');
    exit;
 }


// Initialize database
function startPDO() {
    $hostname = "localhost";
    $dbname = "IETinder";
    $username = "adminTinder";
    $password = "admin123";

    try {
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        logOperation("Database error conection in login.php" , "ERROR");
        return null;
    }
}

// Create log and update it
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


    $logMessage = "[$timeStamp] [$type] [LOGIN] $message\n";



    // Write log message in logFile
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$errors = [];


// Managment of POST requests (login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $pdo = startPDO();
        
        if (!$pdo) {
            logOperation("Error conection with database" , "ERROR");
            $errors['db'] = 'Error de connexió. Torna-ho a intentar més tard.';
        } else {
            // Verify email
            $stmt = $pdo->prepare("SELECT user_ID, password FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch();

            if (!$user) {

                logOperation("Email or password incorrect" , "ERROR");
                $errors['email'] = 'Correu electrònic o contrasenya incorrecte';
                
            } else {
                // Verify password
                $stmtpwd = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND password = SHA2(:password, 512)");
                $stmtpwd->bindParam(':email', $email);
                $stmtpwd->bindParam(':password', $password);
                $stmtpwd->execute();
                
                $passwordCorrecta = $stmtpwd->fetchColumn();

                if (!$passwordCorrecta) {

                    logOperation("Password incorrect in $email" , "ERROR");
                    $errors['password'] = 'Contrasenya incorrecta';
                    
                } else {

                    $stmtvalid= $pdo->prepare("SELECT validated FROM users WHERE email = :email");
                    $stmtvalid->bindParam(':email', $email);
                    $stmtvalid->execute();
                    
                    $validated = $stmtvalid->fetchColumn();

                    if (!$validated) {

                        logOperation("User $email not validated" , "ERROR");
                        $errors['validation'] = 'Usuari no validat';

                    } else {

                        logOperation("Login $email successful" , "INFO");
                        $updateStmt = $pdo->prepare("UPDATE users SET last_login_date = CURRENT_TIMESTAMP WHERE user_ID = :id");
                        $updateStmt->bindParam(':id', $user['user_ID']);
                        $updateStmt->execute();
                        
                        $_SESSION['user'] = $user['user_ID'];
                        
                        logOperation("User " . $user['user_ID'] . " logged in", "INFO");
                        header('Location: discover.php');
                        exit;
                    }

                   
                }
            }
        }
    } catch (PDOException $e) {
        logOperation("Database error in login.php", "ERROR");
        $errors['db'] = 'Error de connexió. Torna-ho a intentar més tard.';
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Login</title>
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time();?>" />
</head>
<body class="body-login">
    <div class="container">
        <div class="card" id="login-card">
            <div class="card-header">
                <div class="logo-login">IETinder ❤️</div>
                <p class="footer-text">Troba l'amor a l'Institut Esteve Terradas i Illa</p>
            </div>

            <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php 
                        if (isset($errors['email'])) {
                            echo htmlspecialchars($errors['email']);
                        } elseif (isset($errors['password'])) {
                            echo htmlspecialchars($errors['password']);
                        } elseif (isset($errors['db'])) {
                            echo htmlspecialchars($errors['db']);
                        }  elseif (isset($errors['validation'])) {
                            echo htmlspecialchars($errors['validation']);
                        }
                        ?>
                    </div>
                <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group <?php echo isset($errors['email']) ? 'error' : ''; ?>" id="emailGroup">
                    <label for="email">Correu electrònic</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="input-group <?php echo isset($errors['email']) || isset($errors['password']) ? 'error' : ''; ?>">
                    <label for="password">Contrasenya</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required>
                        <!-- <button type="button" id="togglePassword" class="toggle-password">👁️</button> -->
                    </div>
                </div>
                <button type="submit" class="primary-button">Iniciar Sessió</button>

                <div class="links-group">
                    <a href="#" class="secondary-link">¿Has oblidat la contrasenya?</a>
                    <a href="register.php" class="secondary-link">Crea una compte nova</a>
                </div>
            </form>

        </div>
    </div>

</body>
</html>
