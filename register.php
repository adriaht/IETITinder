<?php

session_start();

// Initialize database
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
        logOperation("Database error connection in register.php", "ERROR");
        return null;
    }
}

// registro en /logs
function logOperation($message, $type = "INFO") {
    $logDir = __DIR__ . '/logs';

    if (!file_exists($logDir)) {
        mkdir($logDir, 0755);
    }

    $logFile = $logDir . '/' . date('Y-m-d') . '.txt';
    $timeStamp = date('Y-m-d H:i:s');
    $logMessage = "[$timeStamp] [$type] [REGISTER] $message\n";

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

$errors = [];



?>


<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registre - IETinder</title>
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time(); ?>" />
</head>
<body class="body-register">
    <div class="container">
        <div class="card" id="register-card">
            <div class="card-header">
                <div class="logo-register">IETinder ❤️</div>
                <p class="footer-text">Uneix-te i troba l'amor a l'Institut Esteve Terradas i Illa</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars(reset($errors)); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group <?php echo isset($errors['name']) ? 'error' : ''; ?>">
                    <label for="name">Nom</label>
                    <input type="text" id="name" name="name" value="" >
                </div>
                <div class="input-group <?php echo isset($errors['surname']) ? 'error' : ''; ?>">
                    <label for="surname">Cognom</label>
                    <input type="text" id="surname" name="surname" value="" >
                </div>
                
                <div class="input-group <?php echo isset($errors['alias']) ? 'error' : ''; ?>">
                    <label for="alias">Alias</label>
                    <input type="text" id="alias" name="alias" value="" >
                </div>
                <div class="input-group <?php echo isset($errors['birth_date']) ? 'error' : ''; ?>">
                    <label for="birth_date">Data de naixament</label>
                    <input type="date" id="birth_date" name="birth_date" value="" >
                </div>
                <div class="input-group <?php echo isset($errors['email']) ? 'error' : ''; ?>">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="" >
                </div>
                <div class="input-group <?php echo isset($errors['latitude']) ? 'error' : ''; ?>">
                    <label for="latitude">Latitud</label>
                    <input type="number" id="latitude" name="latitude" value="" >
                </div>
                <div class="input-group <?php echo isset($errors['logitude']) ? 'error' : ''; ?>">
                    <label for="logitude">Lolgitud</label>
                    <input type="number" id="logitude" name="logitude" value="" >
                </div>
                <div class="input-group <?php echo isset($errors['email']) ? 'error' : ''; ?>">
                    <label for="sex">Sexe</label>
                            <select id="sexe" name="sex" >
                                <option value="home">Masculí</option>
                                <option value="dona">Femení</option>
                                <option value="no binari">No binari</option>
                            </select>
                </div>
                <div class="input-group <?php echo isset($errors['email']) ? 'error' : ''; ?>">
                    <label for="sexual_orientation">Orientacio sexual</label>
                            <select id="orientacio" name="sexual_orientation" >
                                <option value="heterosexual">Heterosexual</option>
                                <option value="homosexual" >Homosexual</option>
                                <option value="bisexual">Bisexual</option>
                            </select>
                </div>
                <div class="input-group">
                    <label for="password">Contrasenya</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="primary-button">Registrar-se</button>

                <div class="links-group">
                    <a href="login.php" class="secondary-link">Ja tens una compte? Inicia sessió</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
