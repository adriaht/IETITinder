
<!-- INICIO DEL PHP -->

<?php

session_start();
include("functions.php"); /* Loads search from users (not te same as searchInDatabase) + logs + startPDO */ 

$loggedUserId = $_SESSION['user'];

// recoger los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturamos los datos enviados
    $userData = [
        'user_ID' => $_SESSION['user'], // Asegúrate de tener el ID del usuario en sesión
        'name' => $_POST['name'],
        'surname' => $_POST['surname'],
        'alias' => $_POST['alias'],
        'birth_date' => $_POST['birth_date'],
        'latitude' => $_POST['latitude'],
        'longitude' => $_POST['longitude'],
        'sex' => $_POST['sex'],
        'sexual_orientation' => $_POST['sexual_orientation'],
    ];

    $result = updateUserData($userData);
    echo $result;
}

// Función de validación

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

//Funcion de calcular edad
function calculateAge($birthDate) {
    $birthDate = new DateTime($birthDate);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age;
}

// guardamos todos los datos del usuario para mostrarlos en el formulario
$perfilDates = searchInDatabase("*", "users", $loggedUserId);

?>
<!-- FIN DEL php -->


<!-- INICIO DEL JS-->
<script src="profile.js"></script>

    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD-2WRYafkQZpHXNmaMWZnXiWAMbN2ztvs&v=weekly&libraries=marker">
    </script>

<script>

    // funcion para guardar los datos en la base de datos sin recargar la pagina
    document.addEventListener("DOMContentLoaded", async () => {
        // Función para mostrar la tab seleccionada
        window.openTab = function(evt, tabName) {
            var i, tabcontent, tablinks;

            // Ocultar todo el contenido de las tabs
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
                tabcontent[i].classList.remove("activeTab");
            }

            // Desactivar todos los botones
            tablinks = document.getElementsByClassName("profile-tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("activeTab");
            }

            // Mostrar la tab seleccionada
            document.getElementById(tabName).style.display = "block";
            document.getElementById(tabName).classList.add("activeTab");

            // Activar el botón correspondiente
            evt.currentTarget.classList.add("activeTab");
            
        };

    // Abrir la primera tab por defecto
    document.getElementById("defaultOpen").click();


        /* SUBMIT FUNCTIONALITY ----------------------------------------------------------------------------- */ 
        const submenuButton = document.getElementById("submenu-button");
       
        // Toggle submenu visibility
        submenuButton.addEventListener("click", () => {
            toggleSubmenu(submenuButton)
            
        });
         /* SUBMIT FUNCTIONALITY END ----------------------------------------------------------------------------- */ 

        const form = document.querySelector("form");

        form.addEventListener("submit", async (event) => {
            event.preventDefault(); // Evita el recargado de la página

            // Crear un objeto FormData para capturar los datos del formulario
            const formData = new FormData(form);
      
            // Validar los datos antes de enviarlos
            const areErrors = validateData(formData);
        
        if (areErrors.length > 0) {
            // Si hay errores, mostrar los mensajes
            const errorDiv = document.getElementById("showErrors");
            errorDiv.innerHTML = ''; // Limpiar errores anteriores
            areErrors.forEach(error => {
                const pElement = document.createElement("p");
                pElement.textContent = error;
                errorDiv.appendChild(pElement);
            });

        }else{
            try {
             
                const errorDiv = document.getElementById("showErrors");
                errorDiv.innerHTML = '';
            

                // Enviar los datos al servidor mediante fetch
                // con un post para guardar los datos en la base de datos
                const response = await fetch("profile.php", {
                    method: "POST",
                    body: formData,
                });

                // Verificar si la respuesta es exitosa
                if (response.ok) {
                    // Muestra la alerta personalizada 
                    // cuando guarda los datos
                    showAlerts("info", "Dades guardades.");

                } else {
                    showAlerts("error", "Error en enviar les dades."); // errore en la peticion del servidor
                }
            } catch (error) {
                // errores de red, mostrando el error
                showAlerts("error", "Ha ocurregut un error inesperat: " + error + ".");
            }
        }
        });

        // Fetch de las fotos para el perfil
        photosOfUser = await fetchLoggedUserPhotos();
        renderUserPhotos(photosOfUser);

    });



    
   

    // variables para la fincion del mapa
    let map;
    let marker;

    // Inicializa el mapa cuando se carga la página
    window.onload = initMap;


</script>
<!-- FIN DEL JS -->


<!-- INICIO DEL HTML -->

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IETinder - Perfil</title>
    <link rel="stylesheet" type="text/css" href="/styles.css?t=<?php echo time(); ?>" />
    <script src="profile.js"></script>
</head>

<body class="body">

    <div class="container">

        <div class="card">

        <div id="grey-background"></div>

            <header id="header"> <!-- added id to header-->
                <p class="logo">IETinder ❤️</p>
                <button id="submenu-button" class="button-submenu">· · ·</button>
            </header>

            <!-- Botones de las tabs -->
            <div class="profile-tabs">
                <button class="profile-tablinks" onclick="openTab(event, 'profileTabs-edit')"
                    id="defaultOpen">Editar</button>
                <button class="profile-tablinks" onclick="openTab(event, 'profileTabs-view')">Veure</button>
            </div>

            <main id="profileTabs-edit" class="profile content tabcontent">
                <div id="content-profile">
                <div id="showErrors">
                </div>
                    <div id="infoPerfil">
                        <form id="edit-profile-form" action="" method="POST" enctype="multipart/form-data">
                            <!-- Nombre -->
                            <label for="nom">Nom:</label>
                            <input type="text" id="nom" name="name" placeholder="Enter your name"
                                value="<?php echo htmlspecialchars($perfilDates['name']); ?>" >
                            

                            <!-- Apellidos -->
                            <label for="cognoms">Cognoms:</label>
                            <input type="text" id="cognoms" name="surname" placeholder="Enter your last name"
                                value="<?php echo htmlspecialchars($perfilDates['surname']); ?>" >
                            

                            <!-- Alias -->
                            <label for="alias">Alias:</label>
                            <input type="text" id="alias" name="alias" placeholder="Enter your alias"
                                value="<?php echo htmlspecialchars($perfilDates['alias']); ?>" >
                            

                            <!-- Fecha de nacimiento -->
                            <label for="data_naixement">Data de naixement:</label>
                            <input type="date" id="data_naixement" name="birth_date"
                                value="<?php echo htmlspecialchars($perfilDates['birth_date']); ?>" >
                            

                            <!-- Ubicación -->
                            <div id="map"></div>
                            <input hidden type="number" step="any" id="latitud" name="latitude"
                                value="<?php echo htmlspecialchars($perfilDates['latitude']); ?>" required>
                            <input hidden type="number" step="any" id="longitud" name="longitude"
                                value="<?php echo htmlspecialchars($perfilDates['longitude']); ?>" required>
                            

                            <!-- Sexo -->
                            <label for="sexe">Sexe:</label>
                            <select id="sexe" name="sex" >
                                <option value="home" <?php echo $perfilDates['sex'] == 'home' ? 'selected' : ''; ?>>
                                    Masculí</option>
                                <option value="dona" <?php echo $perfilDates['sex'] == 'dona' ? 'selected' : ''; ?>>
                                    Femení</option>
                                <option value="no binari" <?php echo $perfilDates['sex'] == 'no binari' ? 'selected' : ''; ?>>No Binari
                                </option>
                            </select>
                            

                            <!-- Orientación sexual -->
                            <label for="orientacio">Orientació sexual:</label>
                            <select id="orientacio" name="sexual_orientation" >
                                <option value="heterosexual" <?php echo $perfilDates['sexual_orientation'] == 'heterosexual' ? 'selected' : ''; ?>>
                                    Heterosexual</option>
                                <option value="homosexual" <?php echo $perfilDates['sexual_orientation'] == 'homosexual' ? 'selected' : ''; ?>>Homosexual</option>
                                <option value="bisexual" <?php echo $perfilDates['sexual_orientation'] == 'bisexual' ? 'selected' : ''; ?>>Bisexual</option>
                            </select>
                            <br><br>

                    </div>
                    <div id="butonsEditProfile">
                        <!-- Botón de enviar -->
                        <button id="submitEditProfileForm" type="submit">Guardar</button>
                        </form>
                        <a id="linkChangeImagePerfil" href="/photos.php">Modificar les meves fotos</a>
                    </div>
                </div>
            </main>

            <main id="profileTabs-view" class="tabcontent">
                <div id="profile-image-content">
                    <img id="user-image" src="" alt="photoof<?php echo htmlspecialchars($perfilDates['name']); ?>">
                    <div id="info-container">
                        <h2><?php echo htmlspecialchars($perfilDates['name']); ?></h2>
                        <h3><?php echo htmlspecialchars(calculateAge($perfilDates['birth_date'])); ?></h3>
                    </div>
                </div>

            </main>



            <nav>
                <ul>
                    <li><a id="navDiscover" href="/discover.php">Descobrir</a></li>
                    <li><a id="navMessages" href="/messages.php">Missatges</a></li>
                    <li><a id="navProfile" href="/profile.php">Perfil</a></li>
                </ul>
            </nav>

        </div>

    </div>

</body>

</html>

<!-- FIN DEL HTML -->