<?php
// INICIO DEL PHP
session_start();

// Initialize database
function startPDO()
{
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
function logOperation($message, $type = "INFO")
{
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
// FINAL DEL PHP
?>


<!DOCTYPE html>
<!-- INICIO DEL HTML -->
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registre - IETinder</title>
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time(); ?>" />
    <script src="register.js"></script>
</head>

<body class="body-register">
    <div class="container">
        <div class="card" id="register-card">
            <div class="card-header">
                <div class="logo-register">IETinder ❤️</div>
                <p class="footer-text">Uneix-te i troba l'amor a l'Institut Esteve Terradas i Illa</p>
            </div>

      
                <div class="error-message" id="error-message">
                    <?php echo htmlspecialchars(reset($errors)); ?>
                </div>
         
            <form method="POST" action="">
                <div class="input-group ">
                    <label for="name">Nom</label>
                    <input type="text" id="name" name="name" value="">
                </div>
                <div class="input-group">
                    <label for="surname">Cognom</label>
                    <input type="text" id="surname" name="surname" value="">
                </div>

                <div class="input-group">
                    <label for="alias">Alias</label>
                    <input type="text" id="alias" name="alias" value="">
                </div>
                <div class="input-group">
                    <label for="birth_date">Data de naixament</label>
                    <input type="date" id="birth_date" name="birth_date" value="">
                </div>
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="">
                </div>
                <div class="input-group">
                    <label for="latitude">Latitud</label>
                    <input type="number" id="latitude" name="latitude" value="">
                </div>
                <div class="input-group">
                    <label for="logitude">Lolgitud</label>
                    <input type="number" id="logitude" name="logitude" value="">
                </div>
                <div class="input-group">
                    <label for="sex">Sexe</label>
                    <select id="sexe" name="sex">
                        <option value="home">Masculí</option>
                        <option value="dona">Femení</option>
                        <option value="no binari">No binari</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="sexual_orientation">Orientacio sexual</label>
                    <select id="orientacio" name="sexual_orientation">
                        <option value="heterosexual">Heterosexual</option>
                        <option value="homosexual">Homosexual</option>
                        <option value="bisexual">Bisexual</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="password">Contrasenya</label>
                    <input type="password" id="password" name="password">
                </div>

                <div class="links-group"></div>

                <button type="submit" class="primary-button">Registrar-se</button>

                <a href="login.php" class="secondary-link">Ja tens una compte? Inicia sessió</a>
        </div>
        </form>
    </div>
    </div>
</body>
<!-- FINAL DEL HTML -->

</html>


<script>
    // INICIO DEL JS


    document.addEventListener("DOMContentLoaded", () => {
        // capturamos los datos del formulario
        const formElement = document.getElementsByTagName("form")[0];

        formElement.addEventListener("submit", async (event) => {
            event.preventDefault(); // Evita el recargado de la página

            const data = new FormData(formElement);
            const areErrors = validateData(data); // Valida los datos y devuelve errores
           

            // Selecciona el primer elemento con la clase "error-message"
            const errorDiv = document.getElementById("error-message");


            errorDiv.innerHTML = ''; // Limpiar errores anteriores

            if (areErrors && areErrors.length > 0) {
                console.log('detecta que Hay algun error, hay ' + areErrors.length + ' errores.');
                // Si hay errores, agregarlos al contenedor
                areErrors.forEach(error => {
                    const pElement = document.createElement("p");
                    pElement.textContent = error;
                    errorDiv.appendChild(pElement);
                });
                
            } else {
              

            }
        });

    });

    // FINAL DEL JS
</script>