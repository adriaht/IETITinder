<?php
// INICIO DEL PHP


 require __DIR__ . '/vendor/autoload.php';

 //Import PHPMailer classes into the global namespace
 //These must be at the top of your script, not inside a function
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\SMTP;


session_start();
// Gets input from the request 


// PEDIR EL EMAIL Y EL CODIGO DE VALIDACION, 
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['validacio'])) {
    $validacioParam = $_GET['validacio'];
    try {
        // comprobar que no este vacio, ya que estaremos esperando una respuesta en js con esta url
        if ($validacioParam === '') {

            echo json_encode(['success' => false, 'message' => 'codigo de validacion no apto']);
            exit;

        }
        // Verificar si el parámetro contiene '_', si no, no es un codigo valido
        if (strpos($validacioParam, '_') !== false) {
            // Separar email y código buscando '_'
            list($encryptedEmail, $encryptedCode) = explode('_', $validacioParam);

            // Desencriptar valores para recuperar email y código
            $email = base64_decode(urldecode($encryptedEmail));
            $code = base64_decode(urldecode($encryptedCode));
            var_dump($email, $code) ;

            // Verificar si el email y el código coinciden con la base de datos
            if (isEmailAndCodeValid($email, $code)) {

                if (setEmailValidated($email)) {
                    // html para mostrar que el email ha sido validado y redirigir a login
                    header('Location: login.php');
                } else {
                    echo json_encode(['success' => false, 'message' => 'error al validar el email']);
                    exit;

                    // html para mostrar que el email no ha sido validado y redirigir a register
                   
                                    }


                // // dar validacion al usuaro en la base de datos
                // echo "   " . "El email y el código son válidos.";
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    header('Content-Type: application/json; charset=utf-8');
   

    // declaramos email como variable fuera del bloque para tener registrado el valor,
//  ya que lo vamos a necesitar varias veces
    $email = isset($_POST['email']) ? $_POST['email'] : null;



    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['endpoint']) && $_POST['endpoint'] === 'register') {
        try {
            header('Content-Type: application/json; charset=utf-8');

            // una vez validado todos los datos, guardamos todos los datos del formulario

            $email = isset($_POST['email']) ? $_POST['email'] : null;
            $name = isset($_POST['name']) ? $_POST['name'] : null;
            $surname = isset($_POST['surname']) ? $_POST['surname'] : null;
            $alias = isset($_POST['alias']) ? $_POST['alias'] : null;
            $birth_date = isset($_POST['birth_date']) ? $_POST['birth_date'] : null;
            $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
            $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;
            $sex = isset($_POST['sex']) ? $_POST['sex'] : null;
            $sexual_orientation = isset($_POST['sexual_orientation']) ? $_POST['sexual_orientation'] : null;
            $image = isset($_FILES['image']) ? $_FILES['image'] : null;
            $password = isset($_POST['password']) ? $_POST['password'] : null;
            
           
            // Validar que los datos requeridos no sean nulos o vacíos
            if (!$email || !$name || !$surname || !$alias || !$birth_date || !$latitude || !$longitude || !$sex || !$sexual_orientation || !$password) {
                echo json_encode(['success' => false, 'message' =>'Hay algun error y se ha recibido algun campo erroneo en el servidor']);
                exit;
            }

            // comprobamos si el correo ya esta registrado
            if (searchEmailInDatabase($email)) {
                // si no esta registrado, llamamos a la funcion de enviar el correo de validacion
                $verificationCode = generateValidationCode();
                $validateEmail = sendValidateEmail($email, $verificationCode,$name);
                if ($validateEmail) {
                    // se envia el correo de validacion correctamente

                    
                    // añadimos al usuario a la base de datos aunque no estara admitido para login
                    $insertInDatabase = addUserToDatabase(
                        $email,
                        $password,
                        $name,
                        $surname,
                        $alias,
                        $birth_date,
                        $latitude,
                        $longitude,
                        $sex,
                        $sexual_orientation,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                        0,
                        date('Y-m-d H:i:s', strtotime('+48 hours')),
                        $verificationCode
                    );

                    if ($insertInDatabase) {

                        // si se registra el usuario en la base de datos. llamamos a la funcion para guardar la imagen
                        // en la base de datos
                        $user_ID = serchUserID($email);
                        // añadimos la imagen a la carpeta de imagenes, y devolvemos la ruta con su nombre
                        $pathImage = uploadImage($image,$user_ID);

                        


                        echo json_encode(['success' => true, 'message' => 'AÑADIDO EN LA BASE DE DATOS']);
                        exit;

                    } else {
                        echo json_encode(['success' => false, 'message' => 'error al insertar a la base de dades']);
                        exit;

                    }


                    // guardamos al usuario en la base de datos con un atrubuto para diferenciar 
                    // a la gente que ha validado el correo, y una fecha limite, sumando 48 horas a la fecha del 
                    // registro, la borraremos los datos si este correo no se ha validado dentro de esa fecha limite



                } else { //error al enviar el correo de validacion
                    echo json_encode(['success' => false, 'message' => 'Error al enviar el correo de validació']);
                    exit;
                }

            } else {
                // el correo existe y escapamos
                echo json_encode(['success' => false, 'message' => 'El correo electrónic ja está registrat']);
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
        $sql = "SELECT email FROM users WHERE email = :email AND deactivated=1";
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



// funcion para generar el codigo de validacion, despues lo pasaremos al email
//  '0' es el caracter que añadiremos,STR_PAD_LEFT: Añade los ceros a la izquierda.
function generateValidationCode()
{
    return str_pad(rand(0, 9999), 3, '0', STR_PAD_LEFT);
}


// funcion para enviar el correo, donde solo necesitamos pasar el email, el nombre y el cuerpo del mensaje
function sendEmail($email, $subject, $message){

    $mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
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
        logOperation("Correo enviado correctamente", "INFO");
        return true; // Retorna true si el correo se envió correctamente
    } else {
        $_SESSION["ERRORS"][] = "No se pudo enviar el correo.";
        return false;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
}



// funcion para enviar el correo de validacion, hay que cambiar el from por el usuario del servidor
function sendValidateEmail($email, $code, $name)
{
    if (!isset($_SESSION["ERRORS"])) {
        $_SESSION["ERRORS"] = [];
    }

    // Validar el email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["ERRORS"][] = "El correo 'to' está vacío o no es válido.";
        return false; // Retorna inmediatamente si el email no es válido
    }

    // Encriptar email y código
    $encryptedEmail = urlencode(base64_encode($email)); // Encriptar y codificar el email
    $encryptedCode = urlencode(base64_encode($code));   // Encriptar y codificar el código

    // Construir el parámetro validacio
    $validacioParam = $encryptedEmail . "_" . $encryptedCode;



    // Crear el mensaje como HTML
    $mensaje = '
    <!DOCTYPE html>
    <html lang="ca">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Codi de Validació</title>
    </head>
    <body style="font-family: \'Montserrat\', sans-serif; line-height: 1.6; color: #333; background: linear-gradient(135deg, #ff6b6b, #cc2faa, #4158D0); background-size: 200% 200%; animation: gradient 15s ease infinite; padding: 20px; display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #fff; border: 1px solid #ddd; border-radius: 10px;">
            <h2 style="color: #FF6B6B; text-align: center; font-size: 2.5rem; font-weight: bold; animation: pulse 2s infinite;">Validació de Correu Electrònic</h2>
            <p>Hola,</p>
            <p>Gràcies per registrar-te al nostre lloc. Si us plau, utilitza el següent codi per completar el teu procés de validació:</p>
            <div style="text-align: center; margin: 20px 0;">
                <span style="display: inline-block; font-size: 1.5rem; font-weight: bold; background: #f4f4f4; padding: 10px 20px; border-radius: 5px; border: 1px solid #ddd;">
                    ' . htmlspecialchars($validacioParam) . '
                </span>
                <p style="margin-top: 20px; font-size: 0.875rem;">Aquest codi és vàlid durant 48 hores.</p>
                <p>Per confirmar, fes clic aquí:
                    <a href="https://tinder5.ieti.site/register.php?validacio=' . $validacioParam . '"
                       style="background-color: #FF6B6B; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; font-size: 1rem; font-weight: 600;">
                       Confirmar
                    </a>
                </p>
            </div>
            <p>Si no has sol·licitat aquest codi, pots ignorar aquest missatge.</p>
            <p style="margin-top: 20px; font-size: 0.875rem; color: #718096;">Salutacions,<br><strong>L\'equip d\'IETinder</strong></p>
        </div>
    </body>
    </html>
';


if(sendEmail($email,$name, $mensaje)){
    return true;
}else{
    return false;
}


}


// funcion para agregar la imagen en la base de datos, la cual la llamaremos dentro de otra funcion

function insertPhotoInBBDD($type, $path, $user_ID){

    
    logOperation('entra en insert image in database');

    // Gets the input data: usedID of the user the loggedUser interacted with + the state of the interaction (like or dislike)

    $pdo = startPDO(); // Starts PDO

    $extension = str_replace(".", "", $type);

    // Inserts interaction in database (from loggedUser to the user interacted with and the type (like/dislike))
    $sql = "INSERT INTO photos (user_ID, type, path) VALUES (:loggedUserID, :typeOfFile, :pathOfFile)";
    logOperation($sql);

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loggedUserID', $user_ID);
    $stmt->bindParam(':typeOfFile', $extension);
    $stmt->bindParam(':pathOfFile', $path);
    $stmt->execute();

    // Cleans space of the query and PDO
    unset($stmt);
    unset($pdo);

}


// funcion para subir la imagen a la carpeta de imagenes, devolveremos la ruta donde esta el archivo 
// junto a su nombre, para asi poder guardarlo en la base de datos
// esta la ejecutaremos justo antes de llamar a la funcion de adregar el usuario a la base de datos
function uploadImage($file, $user_ID)
{

    $image_file=$file;
    logOperation('file: '.$file);
    logOperation('image_file: '.$image_file);
    logOperation('USER ID: '.$user_ID);

    logOperation('entra en upload image');
    // Exit if no file uploaded
    if (!isset($file)) {
        return 'No file uploaded.';
    }

    // Exit if image file is zero bytes
    if (filesize($file["tmp_name"]) <= 0) {
        return 'Uploaded file has no contents.';
    }

    // Exit if it is not a valid image file
    $image_type = exif_imagetype($file["tmp_name"]);
    if (!$image_type) {
        return 'Uploaded file is not an image.';
    }

    // Get file extension based on file type, to prepend a dot we pass true as the second parameter
    $image_extension = image_type_to_extension($image_type, true);

    // Create a unique image name
    $image_name = bin2hex(random_bytes(16)) . $image_extension;

    // Move the temp image file to the images directory
    $uploadDir = __DIR__ . "/images/";
    $target_path =$uploadDir . $image_name;
    $pathToDatabase = "/images/" . $image_name;

    logOperation('target path: '.$target_path);
    logOperation('file tmpname: '.$file["tmp_name"]);

    if (move_uploaded_file($file["tmp_name"], $target_path)) {
        
        logOperation('va a entrar en insert image');
        insertPhotoInBBDD($image_extension,$pathToDatabase,$user_ID);
        return $target_path; // Return the name of the path file
    } else { 
        logOperation('no acepta move_uploaded'.$file["tmp_name"]. ' '.$target_path);
        return 'Error al guardar la imagen.';
    }
}

// funcion para insertar el usuario en la base de datos, pasamos todos los valores, incluyendo la fecha de 
// expiracion, la validacion y el codigo de validacion, que se envia por correo
function addUserToDatabase($email, $password, $name, $surname, $alias, $birth_date, $latitude, $longitude, $sex, $sexual_orientation, $last_login_date, $creation_date, $validated, $expirate_date, $validate_code)
{
    try {
        // Inicializamos la conexión con la base de datos
        $pdo = startPDO();
        if (!$pdo) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        // Consulta SQL para insertar el usuario
        $sql = "INSERT INTO users (email, password, name, surname, alias, birth_date, latitude, longitude, sex, sexual_orientation, last_login_date, creation_date, distance_user_preference, min_age_user_preference, max_age_user_preference, validated, expirate_date, validate_code)
                VALUES (:email, SHA2(:password, 512), :name, :surname, :alias, :birth_date, :latitude, :longitude, :sex, :sexual_orientation, :last_login_date, :creation_date, DEFAULT, DEFAULT, DEFAULT, :validated, :expirate_date, :validate_code)";

        $stmt = $pdo->prepare($sql);

        // Vinculamos los parámetros
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
        $stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
        $stmt->bindParam(':birth_date', $birth_date, PDO::PARAM_STR);
        $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindParam(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->bindParam(':sex', $sex, PDO::PARAM_STR);
        $stmt->bindParam(':sexual_orientation', $sexual_orientation, PDO::PARAM_STR);
        $stmt->bindParam(':last_login_date', $last_login_date, PDO::PARAM_STR);
        $stmt->bindParam(':creation_date', $creation_date, PDO::PARAM_STR);
        $stmt->bindParam(':validated', $validated, PDO::PARAM_INT);
        $stmt->bindParam(':expirate_date', $expirate_date, PDO::PARAM_STR);
        $stmt->bindParam(':validate_code', $validate_code, PDO::PARAM_STR);

        // Ejecutamos la consulta
        $stmt->execute();

        // Cerramos conexión
        unset($stmt);
        unset($pdo);

        // Retornamos true si se insertó correctamente
        return true;

    } catch (PDOException $e) {
        // En caso de error, mostramos un mensaje y lo registramos
        logOperation("Error en la consulta: " . $e->getMessage(), "ERROR");
        return false;
    } catch (Exception $e) {
        logOperation("Error general: " . $e->getMessage(), "ERROR");
        return false;
    }
}



// busca la id del usuario mediante el email para despues guardar la imagen en la base de datos
function serchUserID($email){
    

    try {
        // Inicializamos la conexión con la base de datos
        $pdo = startPDO();
        if (!$pdo) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        // Consulta SQL para buscar el email y el código de validación
        $sql = "SELECT user_ID FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Verificamos si se encontró el email
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cerramos conexión
        unset($stmt);
        unset($pdo);

        return $user['user_ID'];



    }catch (PDOException $e) {

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


// funcion para cambiar la validacion el la base de datos y permitir al usuario hacer login
function setEmailValidated($email)
{
    try {
        // Inicializa la conexión PDO
        $pdo = startPDO();

        // Prepara la consulta SQL para actualizar el campo 'validated'
        $sql = "UPDATE users SET validated = 1, deactivated = 0 WHERE email = :email";
        $stmt = $pdo->prepare($sql);

        // Vincula el parámetro de correo electrónico
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        // Ejecuta la consulta
        $stmt->execute();

        // Verifica si se actualizó alguna fila
        if ($stmt->rowCount() > 0) {
            // Si se actualizó, retorna true
            logOperation("El email '$email' ha sido validado correctamente.", "INFO");
            return true;
        } else {
            // Si no se actualizó, significa que el correo no existe
            logOperation("No se encontró el email '$email' para validar.", "WARNING");
            return false;
        }
    } catch (Exception $e) {
        // Maneja cualquier error
        logOperation("Error al validar el email '$email': " . $e->getMessage(), "ERROR");
        return false;
    }
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
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD-2WRYafkQZpHXNmaMWZnXiWAMbN2ztvs&v=weekly&libraries=marker">
        </script>

</head>

<body class="body-register">
    <div class="container">
        <div class="card" id="register-card">
            <div class="card-header">
                <div class="logo-register">IETinder ❤️</div>
                <p class="footer-text">Uneix-te i troba l'amor a l'Institut Esteve Terradas i Illa</p>
            </div>




            <form method="POST" action="" enctype="multipart/form-data">
                <div id="content-register">
                    <div class="error-message" id="error-message">

                    </div>
                    <div class="input-group ">
                        <label for="name">Nom</label>
                        <input type="text" id="name" name="name" value="">
                    </div>
                    <br>
                    <div class="input-group">
                        <label for="surname">Cognom</label>
                        <input type="text" id="surname" name="surname" value="">
                    </div>
                    <br>
                    <div class="input-group">
                        <label for="alias">Alias</label>
                        <input type="text" id="alias" name="alias" value="">
                    </div>
                    <br>
                    <div class="input-group">
                        <label for="birth_date">Data de naixament</label>
                        <input type="date" id="birth_date" name="birth_date" value="">
                    </div>
                    <br>
                    <div class="input-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="">
                    </div>
                    <br>
                    <label for="map">Ubicació: </label>
                    <div id="map"></div>
                    <div class="input-group">
                        <input type="number" id="latitud" name="latitude" value="" step="any">
                        <input type="number" id="longitud" name="longitude" value="" step="any">
                    </div>
                    <br>
                    <div class="input-group">
                        <label for="sex">Sexe</label>
                        <select id="sexe" name="sex">
                            <option value="home">Masculí</option>
                            <option value="dona">Femení</option>
                            <option value="no binari">No binari</option>
                        </select>
                    </div>
                    <br>
                    <div class="input-group">
                        <label for="sexual_orientation">Orientacio sexual</label>
                        <select id="orientacio" name="sexual_orientation">
                            <option value="heterosexual">Heterosexual</option>
                            <option value="homosexual">Homosexual</option>
                            <option value="bisexual">Bisexual</option>
                        </select>
                    </div>
                    <br>
                    <div class="input-group">
                        <label for="image">Selecciona una imatge:</label>
                        <input type="file" name="image" id="image" accept="image/*">
                    </div>
                    <br>
                    <div class="input-group">
                        <label for="password">Contrasenya</label>
                        <input type="password" id="password" name="password">

                    </div>
                </div>
                <br>
                <div class="links-group">

                    <button type="submit" class="primary-button">Registrar-se</button>

                    <a href="login.php" class="secondary-link">Ja tens una compte? Inicia sessió</a>
                </div>
            </form>
            <br>



        </div>
    </div>
</body>
<!-- FINAL DEL HTML -->

</html>

<script src="register.js"></script>
<script>
    // INICIO DEL JS

    // Inicializa el mapa cuando se carga la página


    document.addEventListener("DOMContentLoaded", () => {

        window.onload = initMap;



        // capturamos los datos del formulario
        const formElement = document.getElementsByTagName("form")[0];
        console.log(formElement);
        formElement.addEventListener("submit", sendRegisterForm);

    });





    // FINAL DEL JS
</script>