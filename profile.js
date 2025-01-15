// Función para mostrar alertas, le has de pasar el nombre de la alerta deseada
//  y el mensaje que quieres transmitir y le adjudicaremos una id para darle estilos en el css
function showAlerts(nameAlerta, missageAlert) {

    // variables para crear el elemento div y introducirlo en el dom en forma de alerta
    let typeAlerta;
    let elementI;

    if (nameAlerta === "info") {
        typeAlerta = document.createElement('div');
        typeAlerta.id = 'infoAlert';
        typeAlerta.classList.add('alert');
        document.body.appendChild(typeAlerta);
        elementI = document.createElement('i');
        typeAlerta.appendChild(elementI);
        const textAlert = document.createTextNode(missageAlert);
        typeAlerta.appendChild(textAlert);
        typeAlerta.style.display = 'block';
    }

    if (nameAlerta === "error") {
        typeAlerta = document.createElement('div');
        typeAlerta.id = 'errorAlert';
        typeAlerta.classList.add('alert');
        document.body.appendChild(typeAlerta);
        elementI = document.createElement('i');
        typeAlerta.appendChild(elementI);
        const textAlert = document.createTextNode(missageAlert);
        typeAlerta.appendChild(textAlert);
        typeAlerta.style.display = 'block';
    }

    if (nameAlerta === "warning") {
        typeAlerta = document.createElement('div');
        typeAlerta.id = 'warningAlert';
        typeAlerta.classList.add('alert');
        document.body.appendChild(typeAlerta);
        elementI = document.createElement('i');
        typeAlerta.appendChild(elementI);
        const textAlert = document.createTextNode(missageAlert);
        typeAlerta.appendChild(textAlert);
        typeAlerta.style.display = 'block';
    }

    setTimeout(() => {
        typeAlerta.style.display = "none";
        typeAlerta.remove(); // Elimina el elemento del DOM
    }, 3000); // 3 segundos
}


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

document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    // Visibilidad de la contraseña
    //togglePassword.addEventListener('click', () => {
    //    const type = passwordInput.type === 'password' ? 'text' : 'password';
    //   passwordInput.type = type;
    //    togglePassword.textContent = type === 'password' ? '👁️' : '🔒';
    //});
});
