<?php
session_start();
// estoy forzandon la sesion usando el id del usuario
$_SESSION['user_ID'] = 2;

$loggedUserId = $_SESSION['user_ID'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturamos los datos enviados
    $userData = [
        'user_ID' => $_SESSION['user_ID'], // Asegúrate de tener el ID del usuario en sesión
        'name' => $_POST['name'],
        'surname' => $_POST['surname'],
        'alias' => $_POST['alias'],
        'birth_date' => $_POST['birth_date'],
        'latitude' => $_POST['latitude'],
        'longitude' => $_POST['longitude'],
        'sex' => $_POST['sex'],
        'sexual_orientation' => $_POST['sexual_orientation'],
    ];

    // Llamamos a la función para actualizar los datos
    $result = updateUserData($userData);

    // Mostramos el resultado
    echo $result;
}

function startPDO()
{
    $hostname = "localhost";
    $dbname = "IETinder";
    $username = "admin";
    $pw = "1234";
    return new PDO("mysql:host=$hostname;dbname=$dbname", $username, $pw);
}


function searchInDatabase($whatYouWant, $whereYouWant, $userYouWant)
{

    try {
        $pdo = startPDO();

        // Create and return a new PDO instance

        $sql = "SELECT $whatYouWant FROM $whereYouWant WHERE user_ID = :loggedUserId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':loggedUserId', $userYouWant);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            die("Usuario no encontrado");
        }

        // Cerramos conexión
        unset($stmt);
        unset($pdo);

    } catch (PDOException $e) {
        die("Error en la conexión: " . $e->getMessage());
    }

    return $user;
}



function updateUserData($userData)
{
    try {
        // Conexión a la base de datos
        $pdo = startPDO();

        // Construcción dinámica de la consulta UPDATE
        $setClause = [];
        foreach ($userData as $column => $value) {
            if ($column !== 'user_ID') { // Excluimos el 'user_ID' de la cláusula SET
                $setClause[] = "$column = :$column";
            }
        }
        $setClauseString = implode(", ", $setClause);

        // SQL con los marcadores de posición
        $sql = "UPDATE users SET $setClauseString WHERE user_ID = :user_ID";

        $stmt = $pdo->prepare($sql);

        // Enlazar todos los valores
        foreach ($userData as $column => $value) {
            $stmt->bindValue(":$column", $value);
        }

        // Ejecutar la consulta
        $stmt->execute();

        // Validar si se actualizó al menos una fila
        if ($stmt->rowCount() > 0) {
            return "Datos actualizados correctamente.";
        } else {
            return "No se realizaron cambios (posiblemente los valores ya son los mismos).";
        }
    } catch (PDOException $e) {
        die("Error en la conexión o consulta: " . $e->getMessage());
    }
}



// guardamos todos los datos del usuario para mostrarlos en el formulario
$perfilDates = searchInDatabase("*", "users", $loggedUserId);

?>

<script src="index.js"></script>


<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD-2WRYafkQZpHXNmaMWZnXiWAMbN2ztvs&v=weekly&libraries=marker"></script>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="styles.css">

</head>

<body id="edit-profile-body">

    <header id="edit-profile-header">
        <h1>Editar Perfil</h1>
     
    </header>
    <div id="content-perfil">
        <img src="images/profile.jpg" id="edit-profile-image" alt="imagen de perfil">
        <div id="infoPerfil">
        <form id="edit-profile-form" action="" method="POST" enctype="multipart/form-data">
            <!-- Nombre -->
            <label for="nom">Nombre:</label>
            <input type="text" id="nom" name="name" placeholder="Enter your name"
                value="<?php echo htmlspecialchars($perfilDates['name']); ?>" required>
            <br><br>

            <!-- Apellidos -->
            <label for="cognoms">Apellidos:</label>
            <input type="text" id="cognoms" name="surname" placeholder="Enter your last name"
                value="<?php echo htmlspecialchars($perfilDates['surname']); ?>" required>
            <br><br>

            <!-- Alias -->
            <label for="alias">Alias:</label>
            <input type="text" id="alias" name="alias" placeholder="Enter your alias"
                value="<?php echo htmlspecialchars($perfilDates['alias']); ?>" required>
            <br><br>

            <!-- Fecha de nacimiento -->
            <label for="data_naixement">Fecha de nacimiento:</label>
            <input type="date" id="data_naixement" name="birth_date"
                value="<?php echo htmlspecialchars($perfilDates['birth_date']); ?>" required>
            <br><br>

            <!-- Ubicación -->
            <div id="map"></div>
            <input hidden type="number" step="any" id="latitud" name="latitude"
                value="<?php echo htmlspecialchars($perfilDates['latitude']); ?>" required>
            <input hidden type="number" step="any" id="longitud" name="longitude"
                value="<?php echo htmlspecialchars($perfilDates['longitude']); ?>" required>
            <br><br>

            <!-- Sexo -->
            <label for="sexe">Sexo:</label>
            <select id="sexe" name="sex" required>
                <option value="home" <?php echo $perfilDates['sex'] == 'home' ? 'selected' : ''; ?>>Masculino</option>
                <option value="dona" <?php echo $perfilDates['sex'] == 'dona' ? 'selected' : ''; ?>>Femenino</option>
                <option value="no binari" <?php echo $perfilDates['sex'] == 'no binari' ? 'selected' : ''; ?>>No Binario
                </option>
            </select>
            <br><br>

            <!-- Orientación sexual -->
            <label for="orientacio">Orientación sexual:</label>
            <select id="orientacio" name="sexual_orientation" required>
                <option value="heterosexual" <?php echo $perfilDates['sexual_orientation'] == 'heterosexual' ? 'selected' : ''; ?>>Heterosexual</option>
                <option value="homosexual" <?php echo $perfilDates['sexual_orientation'] == 'homosexual' ? 'selected' : ''; ?>>Homosexual</option>
                <option value="bisexual" <?php echo $perfilDates['sexual_orientation'] == 'bisexual' ? 'selected' : ''; ?>>Bisexual</option>
            </select>
            <br><br>

            </div>
            <div id="butonsEditProfile">
            <!-- Botón de enviar -->
            <button id="submitEditProfileForm" type="submit">Guardar Cambios</button>
        </form>
        <a id="linkChangeImagePerfil" href="about:blank" target="_blank">Cambiar imágenes de perfil</a>
        </div>
    </div>
    <script>


        // funcion para guardar los datos en la base de datos sin recargar la pagina
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector("form");

            form.addEventListener("submit", async (event) => {
                event.preventDefault(); // Evita el recargado de la página

                // Crear un objeto FormData para capturar los datos del formulario
                const formData = new FormData(form);

                try {
                    // Enviar los datos al servidor mediante fetch
                    // con un post para guardar los datos en la base de datos
                    const response = await fetch("", {
                        method: "POST",
                        body: formData,
                    });

                    // Verificar si la respuesta es exitosa
                    if (response.ok) {
                        // Muestra la alerta personalizada 
                        // cuando guarda los datos
                        MostrarAlertas("info", "Datos guardados");

                    } else {
                        MostrarAlertas("error", "Error al enviar los datos."); // errore en la peticion del servidor
                    }
                } catch (error) {
                    // errores de red, mostrando el error
                    MostrarAlertas("error", "Ocurrió un error inesperado: " + error + ".");
                }
            });
        });



        // variables para la fincion del mapa
        let map;
        let marker;

        function initMap() {

            // coger las cordenadas del formulario, dependiendo del usuario que este iniciado
            const latitude = parseFloat(document.getElementById("latitud").value);
            const longitude = parseFloat(document.getElementById("longitud").value);

            // Coordenadas iniciales
            const initialPosition = { lat: latitude, lng: longitude };

            // Crea el mapa centrado en las coordenadas iniciales
            map = new google.maps.Map(document.getElementById("map"), {
                mapId: "a8783a817b7ddb3e",
                center: initialPosition,
                zoom: 14,
                disableDefaultUI: true,   // deshabilitza todo el UI predeterminado
                zoomControl: true,         // Habilitar solo el control de zoom
            });

            // Crear un AdvancedMarkerElement y posicionarlo
            marker = new google.maps.marker.AdvancedMarkerElement({
                map: map,
                position: initialPosition,
            });

            // Agregar evento 'click' al mapa
            map.addListener("click", (event) => {
                const newPosition = {
                    lat: event.latLng.lat(),
                    lng: event.latLng.lng(),
                };

                // Cambiar la posición del marcador
                marker.position = newPosition;

                // Guardar las coordenadas en los campos ocultos del formulario, para 
                // posteriormente enviarlas al servidor y guardarlos en la base de datos
                document.getElementById("latitud").value = newPosition.lat;
                document.getElementById("longitud").value = newPosition.lng;


            });

        }

        // Inicializa el mapa cuando se carga la página
        window.onload = initMap;

        async function fetchLoggedUser() {
            try {
                const response = await fetch("dashboard.php?action=get_user");
                if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
                const users = await response.json();
                if (users.success) {
                    console.log("Usuario cargado:", users.message);
                } else {
                    console.error("Error desde el servidor:", users.message);
                }
            } catch (error) {
                console.error("Error al cargar usuarios:", error);
            }
        }
    </script>


</body>

</html>