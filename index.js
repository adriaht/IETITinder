// Función para mostrar alertas, le has de pasar el nombre de la alerta deseada
//  y el mensaje que quieres transmitir y le adjudicaremos una id para darle estilos en el css
function MostrarAlertas(nameAlerta, missageAlert) {

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
}

document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    insertLog("Changed the visibility of the password", "INFO");

    // Visibilidad de la contraseña
    //togglePassword.addEventListener('click', () => {
    //    const type = passwordInput.type === 'password' ? 'text' : 'password';
    //   passwordInput.type = type;
    //    togglePassword.textContent = type === 'password' ? '👁️' : '🔒';
    //});
});

// JS that makes AJAX call to insert user interaction in BBDD --> hace llamada mediante fetch a logOperation DE PHP
async function insertLog(logMessage, type) {

    try {

        const response = await fetch('discover.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({endpoint: "insertLog", logMessage, type})
        });

        // resultado de JSON a objeto Javascript. PHP devuelve {success: error, message: "abc"}
        const result = await response.json();

        // Segun resultado, pone mensaje de error o no
        if (result.success) { 
            console.log(result.message);
        } else {
            console.log(result.message);
        }

    } catch (error) {
        console.log('Error al comunicarse con el servidor: ' + error)
    }
}

// LAMAR FUNCION
// insertLog(Rendered no users left, "INFO");