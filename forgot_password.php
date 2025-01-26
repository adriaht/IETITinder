<?php
// INICIO DEL PHP


 require __DIR__ . '/vendor/autoload.php';

 //Import PHPMailer classes into the global namespace
 //These must be at the top of your script, not inside a function
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\SMTP;


session_start();


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


// funcion para enviar el correo, donde solo necesitamos pasar el email, el nombre y el cuerpo del mensaje
function sendEmail($email, $subject, $message){

    $mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'iesIETinder5@gmail.com';                     //SMTP username
    $mail->Password   = 'ncvk zvri tong aqpf';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('iesIETinder5@gmail.com', 'Tinder Contact');
    $mail->addAddress($email, $subject);     //Add a recipient
    $mail->addCC('adriah.t.22@gmail.com');


    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = $message;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';



    if ($mail->send()) {
        echo json_encode(['success' => true, 'message' => 'Message has been sent']);
        return true; // Retorna true si el correo se envió correctamente
    } else {
        $_SESSION["ERRORS"][] = "No se pudo enviar el correo.";
        return false;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <script src="register.js"></script>
</head>
<body>
    <main class="forgot-password" id="search_email">
            <h1>introduce tu correo</h1>
            <p>
                siusplau, introdueix el teu correo per rebre un email de verificacio, despres podras cambiar
                la teva contrasenya
            </p>
            <form action="">
                <label for="forgot_email">Email</label>
                <input type="email" id="forgot_email" name="forgot_email" placeholder="Email">
                <button type="submit">Enviar</button>
            </form>
        
    </main>

    <main class="forgot-password" id="change_password" >
        <h1>Cambia la contrasenya</h1>
        <p>introdueix la contrasenya desitjada y confirma-la per poder cambiarla</p>
    <form action="">
        <label for="password">Contrasenya</label>
        <input type="password" id="password" name="password" placeholder="Contrasenya">
        <label for="confirm_password">Confirma la contrasenya</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirma la contrasenya">
        <button type="submit" >Cambia la contrasenya</button>
    </form>

    </main>
    
</body>
</html>


<script src="register.js"></script>
<script>

// inincio del js

document.addEventListener("DOMContentLoaded", () => {

// capturamos los datos del formulario
const formElement = document.getElementsByTagName("form")[0];
console.log('primero formulario', formElement);

const formElement = document.getElementsByTagName("form")[1];
console.log('primero formulario', formElement);

formElement.addEventListener("submit", sendRegisterForm);

});
</script>