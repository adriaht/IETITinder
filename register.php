<?php
// INICIO DEL PHP
session_start();
// Gets input from the request 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    header('Content-Type: application/json; charset=utf-8');
$input = json_decode(file_get_contents('php://input'), true);

// declaramos email como variable fuera del bloque para tener registrado el valor,
//  ya que lo vamos a necesitar varias veces
 $email = isset($input['email']) ? $input['email'] : null;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['endpoint']) && $input['endpoint'] === 'register') {
    try {
        header('Content-Type: application/json; charset=utf-8');
       

        // Validar que los datos requeridos no sean nulos o vacíos
        if (!$email) {
            echo json_encode(['success' => false, 'message' => 
            'El correo electrónico es requerido y no recibido en el servidor']);
            exit;
        }

        // comprobamos si el correo ya esta registrado
        if (searchEmailInDatabase($email)) {
            // si no esta registrado, llamamos a la funcion de enviar el correo de validacion
            $verificationCode = generateValidationCode();
            $validateEmail = sendValidateEmail($email,$verificationCode);
            if($validateEmail){
                // se envia el correo de validacion correctamente
                echo json_encode(['success' => true, 'message' => 'Correo de validacion enviado']);
                exit;
                
            }else{ //error al enviar el correo de validacion
                echo json_encode(['success' => false, 'message' => 'Error al enviar el correo de validacion']);
                exit;
            }
        
        }else{
            // el correo existe y escapamos
            echo json_encode(['success' => false, 'message' => 'El correo electrónico ya esta registrado']);
            exit;
        }

    } catch (PDOException $e) {
        logOperation("Database error: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en el servidor']);
        exit;

    }
}



}




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


// funcion para buscar el correo en la base de datos, debuelve true si no lo encuentra y false si existe algun correo registrado
function searchEmailInDatabase($email)
{
    try {
        // Inicializamos la conexión con la base de datos
        $pdo = startPDO();
        if (!$pdo) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        // Consulta SQL para buscar el correo
        $sql = "SELECT email FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Verificamos si se encontró el correo
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cerramos conexión
        unset($stmt);
        unset($pdo);

        // Si se encontró el correo, devolvemos false
        if ($user) {
            return false;
        }

        // Si no se encontró el correo, devolvemos true
        return true;

    } catch (PDOException $e) {
        // En caso de error, mostramos un mensaje y salimos
        logOperation("Error en la conexión: " . $e->getMessage(), "ERROR");
        return false;
    } catch (Exception $e) {
        logOperation("Error general: " . $e->getMessage(), "ERROR");
        return false;
    }
}



function sendValidateEmail($email,$code) {
    if (!isset($_SESSION["ERRORS"])) {
        $_SESSION["ERRORS"] = [];
    }

    // Validar el email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["ERRORS"][] = "El correo 'to' está vacío o no es válido.";
        return false; // Retorna inmediatamente si el email no es válido
    }

    // Parámetros del correo
    $from = "super@ahernandeztorredemer.ieti.site";
    $to = $email;
    $subject = 'Aquest és un codi de validació';
    $mensaje = 'porfavor, introduzca el siguiente numero de validacion: '.$code ;

    // Cabeceras
    $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
    $cabeceras .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
    $cabeceras .= 'From: ' . $from . "\r\n";
    $cabeceras .= 'Reply-To: ' . $from . "\r\n";

    // Intentar enviar el correo
    if (mail($to, $subject, $mensaje, $cabeceras)) {
        return true; // Retorna true si el correo se envió correctamente
    } else {
        $_SESSION["ERRORS"][] = "No se pudo enviar el correo.";
        return false;
    }
}

// funcion para insertar el usuario en la base de datos
function insertUserInDatabase($email, $password, $name, $surname, $alias, $birth_date, $latitude, $longitude, $sex, $sexual_orientation)
{
    try {
        // Inicializamos la conexión con la base de datos
        $pdo = startPDO();
        if (!$pdo) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        // Hash de la contraseña
        $hashedPassword = hash('sha512', $password);

        // Consulta SQL para insertar los datos en la base de datos
        $sql = "INSERT INTO users (email, password, name, surname, alias, birth_date, latitude, longitude, sex, sexual_orientation, creation_date)
                VALUES (:email, :password, :name, :surname, :alias, :birth_date, :latitude, :longitude, :sex, :sexual_orientation, CURRENT_TIMESTAMP)";
        $stmt = $pdo->prepare($sql);

        // Vinculamos los parámetros
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
        $stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
        $stmt->bindParam(':birth_date', $birth_date, PDO::PARAM_STR);
        $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindParam(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->bindParam(':sex', $sex, PDO::PARAM_STR);
        $stmt->bindParam(':sexual_orientation', $sexual_orientation, PDO::PARAM_STR);

        // Ejecutamos la consulta
        $stmt->execute();

        // Cerramos la conexión
        unset($stmt);
        unset($pdo);

        // Si todo ha ido bien, devolvemos true
        return true;

    } catch (PDOException $e) {
        // En caso de error con la base de datos, registramos el error y devolvemos false
        logOperation("Error al insertar usuario en la base de datos: " . $e->getMessage(), "ERROR");
        return false;
    } catch (Exception $e) {
        // En caso de error general, registramos el error y devolvemos false
        logOperation("Error general: " . $e->getMessage(), "ERROR");
        return false;
    }
}

//  '0' es el caracter que añadiremos,STR_PAD_LEFT: Añade los ceros a la izquierda.
function generateValidationCode() {
    return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
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

            const dataRegisterForm = new FormData(formElement);
            const areErrors = validateData(dataRegisterForm); // Valida los datos y devuelve errores


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
                console.log('No hay errores.');

                try {


                    const sendEmail = document.getElementById("email").value;
                    console.log(sendEmail);

                    const response = await fetch('register.php', {
                        method: 'POST',

                        // coger el email por .value en vez de enviar todo el form
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ endpoint: "register", email: sendEmail })
                        // enviar los datos del formulario para verificar si el usuario existe
                    });
                    
                    if (response.ok) {
                        // Si el usuario no existe, seguimos y enviamos un una solicitud al servidor para enviar el correo de validacion
                        const register = await response.json();
                        console.log(register.message);

                        if (register.message === "Correo de validacion enviado") {
                            const validation = await fetch('register.php', {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                }

                            });
                            
                        }
                        
                    } else {
                        console.log('error en la respuesta del server');
                    }
                    

                } catch (error) {
                    console.log('Error al comunicarse con el servidor: ' + error);
                }
            }



        });
    });



    // FINAL DEL JS
</script>