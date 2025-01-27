<?php
// INICIO DEL PHP


require __DIR__ . '/vendor/autoload.php';

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


session_start();

$showChangePassword = false; 


// parametros get para mostrar la pagina de cambiar contraseña si el correo es valido
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['validat'])&& $_GET['validat'] === 'true') {
       
       try{
        $emailEncrypt = isset($_GET['email']) ? $_GET['email'] : null;
        $email = base64_decode(urldecode($emailEncrypt));
        if (searchEmailInDatabase($email)) {
           
        $showChangePassword = true;
      

        } else {
            echo json_encode(['success' => false, 'message' => 'Correo no valido']);
            exit;
        }

       }
       catch (Exception $e) {
        logOperation("Error: " . $e->getMessage(), "ERROR");
        echo json_encode(['success' => false, 'message' => 'Error en el servidor al validar la contraseña']);
        exit;
       }
    }

// parametro get para comprovar que el correo enviado se ha validado
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['cambiarPassword'])) {
        $validacioParam = $_GET['cambiarPassword'];
        try {
            // comprobar que no este vacio, ya que estaremos esperando una respuesta en js con esta url
            if ($validacioParam === '') {

                echo json_encode(['success' => false, 'message' => 'codigo recibido no es apto']);
                exit;

            }
            // Verificar si el parámetro contiene '_', si no, no es un codigo valido
            if (strpos($validacioParam, '_') !== false) {
                // Separar email y código buscando '_'
                list($encryptedEmail, $encryptedCode) = explode('_', $validacioParam);

                // Desencriptar valores para recuperar email y código
                $email = base64_decode(urldecode($encryptedEmail));
                $code = base64_decode(urldecode($encryptedCode));

                // Verificar si el email y el código coinciden con la base de datos
                if (isEmailAndCodeValid($email, $code)) {
                    // guardar el email en la sesion para mas tarde usarlo al cambiar la contraseña
                    $_SESSION['email']=$email;

                    // aqui cambiaremos el valor del validacion, para mas adelante asegurarnos que 
                    // el usuario desea cambiar la contraseña y verificarlo al actualizar la base de datos
                    changeValidationUser($email);
                    echo json_encode(['success' => true, 'message' => 'codigo de validacion apto']);
                    header('Location: forgot_password.php?validat=true&email=' . $encryptedEmail);
                    exit;



                } else {

                    echo json_encode(['success' => false, 'message' => 'codigo de validacion no apto']);
                    exit;
                }


            } else {
                echo json_encode(['success' => false, 'message' => 'codigo de validacion no apto']);
                exit;
            }
        } catch (Exception $e) {
            logOperation("Error: " . $e->getMessage(), "ERROR");
            echo json_encode(['success' => false, 'message' => 'Error en el servidor al validar']);
            exit;
        }
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    ob_clean(); // Limpia cualquier salida previa para no romper el json
    header('Content-Type: application/json; charset=utf-8');

   

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['endpoint']) && $_POST['endpoint'] === 'forgotPassword') {
        ob_clean(); // Limpia cualquier salida previa para no romper el json
        header('Content-Type: application/json; charset=utf-8');

      

        try {
            $email = isset($_POST['forgot_email']) ? $_POST['forgot_email'] : null;
            session_start();
            $_SESSION['email']=$email;

            if (searchEmailInDatabase($email)) { //cambiar mas asdelante para que compruebe codigo de william
                $name = searchNameInDatabase($email);
                $code = searchCodeInDatabase($email);
                if (sendChangePasswordEmail($email, $name, $code)) {

                    echo json_encode(['success' => true, 'message' => 'Correo enviado exitosamente','email'=>$_SESSION['email']]);
                    exit;

                } else {

                    echo json_encode(['success' => false, 'message' => 'No se pudo enviar el correo']);
                    exit;

                }

            } else {

                echo json_encode(['success' => false, 'message' => 'No se pudo validar el correo con la base de datos']);
                exit;
            }


        } catch (Exception $e) {
            logOperation("Error general al enviar el correo: " . $e->getMessage(), "ERROR");

            exit;

        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['endpoint']) && $_POST['endpoint'] === 'changePassword') {
            // Aseguramos de que la sesión esté iniciada
    session_start();

    // Recuperar el email desde la sesión
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : null;
        try {

            $password = isset($_POST['password']) ? $_POST['password'] : null;
            $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : null;

            // comprobar si las contraseñas son iguales y si tiene permiso para cambiar en la base de datos
            if (canChangePassword($password, $confirmPassword)) {
               
                if (changePasswordInDatabase($password,$email )) {
                    
                    if(ValidationUser($email)){
                        echo json_encode(['success' => true, 'message' => 'Contraseña cambiada exitosamente y usuario validado','email'=>$email]);
                        exit;
                    }else{
                        echo json_encode(['success' => false, 'message' => 'No se pudo validar al usuario']);
                        exit;
                    }
                    
                   
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo cambiar la contraseña','email'=>$email]);
                    exit;
                

            }
            } else {
                echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden o no se ha validado el correo']);
                exit;
            }


        } catch (Exception $e) {
            logOperation("Error general al cambiar la contraseña: " . $e->getMessage(), "ERROR");

            exit;

        }
    }



}



function startPDO()
{
    $hostname = "localhost";
    $dbname = "IETinder";
    $username = "admin";
    $password = "admin123";

    try {
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname;
        charset=utf8", $username, $password, [
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
    $logMessage = "[$timeStamp] [$type] [FORGOT_PASSWORD] $message\n";

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
        return $user !== false;

    } catch (PDOException $e) {
        // En caso de error, mostramos un mensaje y salimos
        logOperation("Error en la conexión: " . $e->getMessage(), "ERROR");
        return false;
    } catch (Exception $e) {
        logOperation("Error general: " . $e->getMessage(), "ERROR");
        return false;
    }
}

function searchNameInDatabase($email)
{

    try {
        // Inicializamos la conexión con la base de datos
        $pdo = startPDO();
        if (!$pdo) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        // Consulta SQL para buscar el correo
        $sql = "SELECT name FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Verificamos si se encontró el correo
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cerramos conexión
        unset($stmt);
        unset($pdo);

        // Si se encontró el nombre, devolvemos false
        // Si se encontró el code, lo devolvemos
        if ($user !== false) {
            return $user['name'];
        } else {
            return false; // Si no se encuentra el correo, devolvemos false
        }

    } catch (PDOException $e) {
        // En caso de error, mostramos un mensaje y salimos
        logOperation("Error en la conexión: " . $e->getMessage(), "ERROR");
        return false;
    } catch (Exception $e) {
        logOperation("Error general: " . $e->getMessage(), "ERROR");
        return false;
    }

}

function searchCodeInDatabase($email)
{

    try {
        // Inicializamos la conexión con la base de datos
        $pdo = startPDO();
        if (!$pdo) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        // Consulta SQL para buscar el correo
        $sql = "SELECT validate_code FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Verificamos si se encontró el correo
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cerramos conexión
        unset($stmt);
        unset($pdo);

        // Si se encontró el code, lo devolvemos
        if ($user !== false) {
            return $user['validate_code'];
        } else {
            return false; // Si no se encuentra el correo, devolvemos false
        }


    } catch (PDOException $e) {
        // En caso de error, mostramos un mensaje y salimos
        logOperation("Error en la conexión: " . $e->getMessage(), "ERROR");
        return false;
    } catch (Exception $e) {
        logOperation("Error general: " . $e->getMessage(), "ERROR");
        return false;
    }

}

// funcion para enviar el correo, donde solo necesitamos pasar el email, el nombre y el cuerpo del mensaje
function sendEmail($email, $subject, $message)
{

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = 0;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   //Enable SMTP authentication
        $mail->Username = 'iesIETinder5@gmail.com';                     //SMTP username
        $mail->Password = 'ncvk zvri tong aqpf';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('iesIETinder5@gmail.com', 'Tinder Contact');
        $mail->addAddress($email, $subject);     //Add a recipient
      


        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Here is the subject';
        $mail->Body = $message;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';



        if ($mail->send()) {

            return true; // Retorna true si el correo se envió correctamente
        } else {
            $_SESSION["ERRORS"][] = "No se pudo enviar el correo.";
            return false;
        }
    } catch (Exception $e) {
        logOperation("Error general al enviar el correo: " . $e->getMessage(), "ERROR");
        return false;
    }
}


function sendChangePasswordEmail($email, $name, $code)
{


    // Encriptar email y código
    $encryptedEmail = urlencode(base64_encode($email)); // Encriptar y codificar el email
    $encryptedCode = urlencode(base64_encode($code));   // Encriptar y codificar el código

    // Construir el parámetro validacio
    $validacioParam = $encryptedEmail . "_" . $encryptedCode;

    // Crear el mensaje como HTML
    $message =
        '
   <!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperació de Contrassenya</title>
</head>
<body style="font-family: \'Montserrat\', sans-serif; line-height: 1.6; color: #333; background: linear-gradient(135deg, #ff6b6b, #cc2faa, #4158D0); background-size: 200% 200%; animation: gradient 15s ease infinite; padding: 20px; display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #fff; border: 1px solid #ddd; border-radius: 10px;">
        <h2 style="color: #FF6B6B; text-align: center; font-size: 2.5rem; font-weight: bold; animation: pulse 2s infinite;">Recuperació de Contrassenya</h2>
        <p>Hola,</p>
        <p>Hem rebut una sol·licitud per restablir la teva contrassenya. Si us plau, utilitza el següent codi per completar el procés de recuperació:</p>
        <div style="text-align: center; margin: 20px 0;">
            <span style="display: inline-block; font-size: 1.5rem; font-weight: bold; background: #f4f4f4; padding: 10px 20px; border-radius: 5px; border: 1px solid #ddd;">
                ' . $validacioParam . '
            </span>
            <p style="margin-top: 20px; font-size: 0.875rem;">Aquest codi és vàlid durant 24 hores.</p>
            <p>Per restablir la contrassenya, fes clic aquí:
                <a href="http://localhost:8080/forgot_password.php?cambiarPassword=' . $validacioParam . '"
                   style="background-color: #FF6B6B; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; font-size: 1rem; font-weight: 600;">
                   Restablir Contrassenya
                </a>
            </p>
        </div>
        <p>Si no has sol·licitat aquest restabliment, pots ignorar aquest missatge.</p>
        <p style="margin-top: 20px; font-size: 0.875rem; color: #718096;">Salutacions,<br><strong>L\'equip d\'IETinder</strong></p>
    </div>
</body>
</html>'; 


    if (sendEmail($email, $name, $message)) {
        return true;
    } else {
        return false;
    }
}

function changeValidationUser($email)
{
    try {
        // Inicializa la conexión PDO
        $pdo = startPDO();

        // Prepara la consulta SQL para actualizar el campo 'validated'
        $sql = "UPDATE users SET validated = 0 WHERE email = :email";
        $stmt = $pdo->prepare($sql);

        // Vincula el parámetro de correo electrónico
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        // Ejecuta la consulta
        $stmt->execute();

        // Verifica si se actualizó alguna fila
        if ($stmt->rowCount() > 0) {
            // Si se actualizó, retorna true
            logOperation("Usuario deshabilitado correctamente.", "INFO");
            return true;
        } else {
            // Si no se actualizó, significa que el correo no existe
            logOperation("error al deahabilitar el usuario.", "WARNING");
            return false;
        }
    } catch (Exception $e) {
        // Maneja cualquier error
        logOperation("error al cambiar la validacion del usuario: " . $e->getMessage(), "ERROR");
        return false;
    }
}

function ValidationUser($email)
{
    try {
        // Inicializa la conexión PDO
        $pdo = startPDO();

        // Prepara la consulta SQL para actualizar el campo 'validated'
        $sql = "UPDATE users SET validated = 1 WHERE email = :email";
        $stmt = $pdo->prepare($sql);

        // Vincula el parámetro de correo electrónico
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        // Ejecuta la consulta
        $stmt->execute();

        // Verifica si se actualizó alguna fila
        if ($stmt->rowCount() > 0) {
            // Si se actualizó, retorna true
            logOperation("Usuario habilitado correctamente.", "INFO");
            return true;
        } else {
            // Si no se actualizó, significa que el correo no existe
            logOperation("error al habilitar el usuario.", "WARNING");
            return false;
        }
    } catch (Exception $e) {
        // Maneja cualquier error
        logOperation("error al cambiar la validacion del usuario: " . $e->getMessage(), "ERROR");
        return false;
    }
}


function canChangePassword($password, $confirmPassword)
{

    if (empty($password) || empty($confirmPassword)) {
        return false;
    }

    if ($password !== $confirmPassword) {
        return false;
    }// aqui iria else if para comprobar la validacion del correo
    else {
        try {
            // Inicializamos la conexión con la base de datos
            $pdo = startPDO();
            if (!$pdo) {
                throw new Exception("No se pudo conectar a la base de datos.");
            }

            // Consulta SQL para buscar el correo
            $sql = "SELECT validated FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            // Verificamos si se encontró el correo
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Cerramos conexión
            unset($stmt);
            unset($pdo);

            // Si se encontró el code, devolvemos false
            if ($user == 0) {
                return true;
            } else {
                return false;
            }



        } catch (PDOException $e) {
            // En caso de error, mostramos un mensaje y salimos
            logOperation("Error en la conexión: " . $e->getMessage(), "ERROR");
            return false;
        } catch (Exception $e) {
            logOperation("Error general: " . $e->getMessage(), "ERROR");
            return false;
        }

    }
}

function changePasswordInDatabase($password, $email)
{
    try {
        // Inicializa la conexión PDO
        $pdo = startPDO();


        // Cifra la contraseña antes de guardarla
        $hashedPassword = hash("sha512",$password);

        // Prepara la consulta SQL para actualizar el campo 'validated'
        $sql = "UPDATE users SET password = :password WHERE email = :email";
        $stmt = $pdo->prepare($sql);

        // Vincula el parámetro de correo electrónico
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

        // Ejecuta la consulta
        $stmt->execute();

        // Verifica si se actualizó alguna fila
        if ($stmt->rowCount() > 0) {
            // Si se actualizó, retorna true
            logOperation("Contraseña cambiada correctamente.", "INFO");
            return true;
        } else {
            // Si no se actualizó, significa que el correo no existe
            logOperation("error al cambiar la contraseña.", "WARNING");
            return false;
        }
    } catch (Exception $e) {
        // Maneja cualquier error
        logOperation("error en la funcion de cambiar changePasswordInDatabase: " . $e->getMessage(), "ERROR");
        return false;
    }

}
// funcion para comprobar si el correo y el codigo son validos para mas adelante dar validacion al usuario
// en la base de datos

function isEmailAndCodeValid($email, $code)
{
    try {
        // Inicializamos la conexión con la base de datos
        $pdo = startPDO();
        if (!$pdo) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        // Consulta SQL para buscar el email y el código de validación
        $sql = "SELECT validate_code FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Verificamos si se encontró el email
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cerramos conexión
        unset($stmt);
        unset($pdo);

        // Si no se encuentra el usuario o el código no coincide, devolvemos false
        if (!$user || $user['validate_code'] !== $code) {
            return false;
        }

        // Si el código es correcto, devolvemos true
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time(); ?>" />
    <script src="forgot_password.js"></script>
</head>

<body class="body_forgot_password">
    <div class="container">
        <div class="card" id="forgot-card">
            <div class="card-header">
                <div class="logo-forgot">IETinder ❤️</div>
                <p class="footer-text">Uneix-te i troba l'amor a l'Institut Esteve Terradas i Illa</p>
            </div>
       
            <main class="forgot-password" id="search_email" >
                <div class="informative_header">
                    <h1>introduce tu correo</h1>
                    <br>
                    <p>
                        siusplau, introdueix el teu correo per rebre un email de verificacio, despres podras cambiar
                        la teva contrasenya
                    </p>
                </div>
                <br>
                <form action="forgot" method="POST" id="search_email_form">

                    <div id="error-message">

                    </div>
                    <div class="input-group">
                        <label for="forgot_email">Email</label>
                        <input type="email" id="forgot_email" name="forgot_email" placeholder="Email">
                    </div>
                    <br>
                    <div class="links-group">
                        <button type="submit" class="primary-button">Enviar</button>
                    </div>
                </form>

            </main>


            <main class="forgot-password" id="change_password" style="display: none" >
                <div class="informative_header">
                    <h1>Cambia la contrasenya</h1>
                    <br>
                    <p>introdueix la contrasenya desitjada y confirma-la per poder cambiarla</p>
                </div>
                <br>
                <form action="" method="POST" id="change_password_form">
                    <div id="error-message-password">

                    </div>
                    <div class="input-group">
                        <label for="password">Contrasenya</label>
                        <input type="password" id="password" name="password" placeholder="Contrasenya">
                    </div>
                    <div class="input-group">
                        <label for="confirm_password">Confirma la contrasenya</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Confirma la contrasenya">
                    </div>
                    <br>
                    <div class="links-group">
                        <button type="submit" class="primary-button">Cambia la contrasenya</button>
                    </div>
                </form>

            </main>


        </div>
    </div>

</body>

</html>


<script src="forgot_password.js"></script>
<script>

    // inincio del js

    document.addEventListener("DOMContentLoaded", () => {

        // capturamos los datos del formulario
        const formElement1 = document.getElementById("search_email_form");


        const formElement2 = document.getElementById("change_password_form");


        formElement1.addEventListener("submit", sendForgotPasswordForm);
        formElement2.addEventListener("submit", sendChangePasswordForm);


    });
</script>