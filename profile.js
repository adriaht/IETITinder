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


// funcion donde pasaremos el formulario 
function validateData(formData) {

    // variables de traduccion, ya que el formulario es en ingles y la base de datos tambien
    // me da algunos problemas al cambiar de ingles a catalan, con esto hacemos que el mensaje de error
    // sea en catalan
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
        last_login_date: "Data de l'última connexió"
    };

    let errores = [];
    formData.forEach((value, key) => {
        if (!value.trim()) {
            const fieldName = fieldTranslations[key] || key; // Traduir el camp si existeix al mapa
            errores.push(`${fieldName} és obligatori.`);
        }
    });
    return errores;
}

async function fetchLoggedUserPhotos() {

    try {

        const response = await fetch("photos.php?action=get_user_photos");
        const userPhotos = await response.json();

        // IF SUCCESS = returns array of users data | ELSE = returns empty array
        if (userPhotos.success){
            return userPhotos.message;
        } else {
            return;
        }

    } catch (error) {
        //console.log(error)
        return;
    }
}

function renderUserPhotos(userPhotos){

    // Delete all html inside the main content Div
    const container = document.getElementById('profile-image-content');
    //container.innerHTML = ''; 

    // IMAGE 
    // TE FALTARÁ EL USER O DONDE SEA QUE TENGAS LA IMAGEN
    const image = document.getElementById('user-image');
    image.src = userPhotos[0].path;

    /* IMAGE CARROUSELL*/

    /* LOS BOTONCITOS*/ 
    let carrouselContainer = null;
    if (userPhotos.length > 1) {

        carrouselContainer = document.createElement('div');
        carrouselContainer.id = "carrousel-container";

        let currentIndex = 0;
        const dots = [];

        userPhotos.map((photo , i) => {
            const dot = document.createElement('span');
            dot.classList.add('dot');
            dots.push(dot);
            if (i === 0) dot.classList.add('active');
            carrouselContainer.appendChild(dot);
        })

       // console.log(dots);

        carrouselContainer.addEventListener("click", handleCarouselClick) 

        function handleCarouselClick() {
            
            currentIndex += 1;

            if (currentIndex >= userPhotos.length) {
                currentIndex = 0;
            }

            image.src = userPhotos[currentIndex].path;

            dots.map(dot => dot.classList.remove('active'));
            dots[currentIndex].classList.add('active');
        }

    }

    if(carrouselContainer) {
        container.insertBefore(carrouselContainer, container.lastElementChild);
    }

}


 /* SUBMIT FUNCTIONALITY ----------------------------------------------------------------------------- */ 
 function renderSubmenu() {

    const header = document.getElementById("header");

    // Create submenu
    const submenu = document.createElement("ul");
    submenu.id = "submenu";

    // Create list items
    const options = [
        { text: "Tancar sessió", href: "/logout.php" },
        { text: "Modificar contrasenya", href: "#" },
        { text: "Eliminar compte", href: "#" },
    ];

    options.map(option => {

        console.log(option);
        const a = document.createElement("a");
        const li = document.createElement("li");
        li.textContent = option.text;
        a.href = option.href;
        a.appendChild(li);

        if(option.text === "Eliminar compte"){
            a.addEventListener("click", renderDeleteConfirmation);
        }

        submenu.appendChild(a);
    });

    header.appendChild(submenu);
}

function deleteSubmenu() {
    const submenu = document.getElementById("submenu");
    submenu.remove();
}  

function renderDeleteConfirmation(e) {

    e.preventDefault();

    // Maintain grey backgroud with zIndex superior to submenu button -> when clicking submenu-button, return to zIndex of 10
    const greyBackground = document.getElementById("grey-background");
    greyBackground.style.zIndex = 15;
    const submenuButton = document.getElementById("submenu-button");
    submenuButton.innerText =  "· · ·";
    deleteSubmenu();

    const container = submenuButton.parentNode.parentNode;

    const mainDiv = document.createElement("div");
    mainDiv.id = "delete-account";

        const attentionTitle = document.createElement("h1");
        attentionTitle.innerText = "ATENCIÓ!";

        const firstParagraph = document.createElement("p");
        firstParagraph.innerText = "Si esborres el teu compte, no podràs tornar a entrar amb el mateix compte!";

        const secondParagraph = document.createElement("p");
            const boldItalicText = document.createElement("strong");
            const italicText = document.createElement("i");
            italicText.innerText = "ESBORRAR";
            boldItalicText.appendChild(italicText);
        secondParagraph.appendChild(document.createTextNode("Escriu "));
        secondParagraph.appendChild(boldItalicText);
        secondParagraph.appendChild(document.createTextNode(" per confirmar la eliminació"))

        const form = document.createElement("form");
        form.method = "POST";
        form.action = "delete-account.php";
        
            const textInput = document.createElement("input");
            textInput.type = "text";
            textInput.name = "pass";

            const errorDiv = document.createElement("div");
            errorDiv.id = "delete-errors";
                const errorParagraph = document.createElement("p");
                errorDiv.appendChild(errorParagraph)

            const buttonDiv = document.createElement("div");
            buttonDiv.id = "button-container";

                const cancelButton = document.createElement("input");
                cancelButton.type = "reset";
                cancelButton.value = "Cancelar";
                cancelButton.id = "cancel";

                cancelButton.addEventListener("click", () => {
                    greyBackground.style.zIndex = 10;
                    greyBackground.style.display = "none";
                    mainDiv.remove();
                })

                const confirmButton = document.createElement("input");
                confirmButton.type = "submit";
                confirmButton.value = "Confirmar";
                confirmButton.id = "confirm";

            buttonDiv.appendChild(cancelButton);
            buttonDiv.appendChild(confirmButton);

        form.appendChild(textInput);
        form.appendChild(errorDiv);
        form.appendChild(buttonDiv);

        form.addEventListener("submit", (e) => {
            errorParagraph.innerText = "";
            handleDeleteAccount(e, form, errorParagraph, mainDiv);
        })

    mainDiv.appendChild(attentionTitle);
    mainDiv.appendChild(firstParagraph);
    mainDiv.appendChild(secondParagraph);
    mainDiv.appendChild(form);
    container.appendChild(mainDiv);

}   

function handleDeleteAccount(e, form, errorPTag, containerContent) {

    e.preventDefault();

    const userInput = form.elements[0].value;

    if(userInput != "ESBORRAR") {
        
        errorPTag.innerText = "Has d'escriure 'ESBORRAR' en el camp superior";

    } else {
        containerContent.style.display = "none";
        showAlerts("info", "Has eliminat el teu compte");

        setTimeout(() =>{
            form.submit();
        }, 3000)
      

    }

}

function toggleSubmenu(button){

    const greyBackground = document.getElementById("grey-background");
    greyBackground.style.zIndex = 10;

    if (button.innerText === "· · ·") {

        greyBackground.style.display = "inline";
        renderSubmenu();
        button.innerText = "X";

    } else {

        greyBackground.style.display = "none";
        deleteSubmenu();
        button.innerText = "· · ·";

    }
}
/* SUBMIT FUNCTIONALITY END ---------------------------------------------------------------------- */ 


document.addEventListener("DOMContentLoaded", async () => {
    //NAV changer
    document.getElementById("navProfile").classList.add("navActive");
})