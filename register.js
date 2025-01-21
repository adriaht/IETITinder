// funcion para validar cada camp del formulario enviando todos los valores registrados
function validateData(formData) {

    // variables de traduccion, ya que el formulario es en ingles y la base de datos també
    const fieldTranslations = {
        user_ID: "Identificador d'usuari",
        email: "Correu electrònic",
        password: "Contrasenya",
        name: "Nom",
        surname: "Cognom",
        alias: "Àlies",
        birth_date: "Data de naixement",
        latitude: "Latitud",
        longitude: "Longitud",
        sex: "Sexe",
        sexual_orientation: "Orientació sexual",
        creation_date: "Data de creació",
        last_login_date: "Data de l'última connexió",
        image: "Imatge"
    };

    let errores = [];
    formData.forEach((value, key) => {
        // Convertir value a string antes de usar trim
        if (!String(value).trim()) {
            const fieldName = fieldTranslations[key] || key; // Traduir el camp si existeix al mapa
            errores.push(`${fieldName} és obligatori.`);
        }
    });


     // Validar que los valores de "latitude" y "longitude" son números válidos
     const latitude = parseFloat(formData.get('latitude'));
     const longitude = parseFloat(formData.get('longitude'));

     if (isNaN(latitude) || latitude === "") {
        errores.push(`La latitud és obligatòria i ha de ser un número vàlid.`);
    }

    if (isNaN(longitude) || longitude === "") {
        errores.push(`La longitud és obligatòria i ha de ser un número vàlid.`);
    }

    return errores;
}
// funcion para registrar valores de latitud y longitud segun la ubicacion del mapa
function initMap() {

    // coger las cordenadas del formulario, dependiendo del usuario que este iniciado
    const latitude =  parseFloat('2');
    const longitude = parseFloat('2');

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


async function sendRegisterForm(event) {
    event.preventDefault(); // Evita el recargado de la página
    const formElement = event.target; // Formulario que disparó el evento
    const dataRegisterForm = new FormData(formElement);
    const areErrors = validateData(dataRegisterForm); // Valida los datos y devuelve errores
    console.log(dataRegisterForm);
    console.log('detecta que Hay algun error, hay ' + areErrors.length + ' errores.');
    console.log(areErrors);
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

        // Desplazar la página hacia el div de errores
        errorDiv.scrollIntoView({ behavior: "smooth", block: "start" });

    } else {
        console.log('No hay errores.');

        // Convertir FormData a JSON para enviarlo en el cuerpo de la solicitud
        const dataObject = {};
        dataRegisterForm.forEach((value, key) => {
            dataObject[key] = value;  // Rellenamos un objeto con los datos del FormData
        });


        try {

            // enviamos el email para comprobar si este existe
            const sendEmail = document.getElementById("email").value

            const response = await fetch('register.php', {
                method: 'POST',

                // coger el email por .value en vez de enviar todo el form
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ endpoint: "register", form: dataObject, email: sendEmail })
                // enviar los datos del formulario para verificar si el usuario existe
            });

            if (response.ok) {
                // Si el usuario no existe, seguimos y enviamos un una solicitud 
                // al servidor para enviar el correo de validacion, y lo añadimos a la base de datos
                const register = await response.json();
                console.log(register.message);



            } else {
                console.log(register.message);
                console.log('error en la respuesta del server');
            }


        } catch (error) {
            console.log('Error al comunicarse con el servidor: ' + error);
        }
    }


}
