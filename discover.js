document.addEventListener("DOMContentLoaded", async () => {

    // Gets array of fetched users
    const fetchedUsers = await fetchUsers();
    console.log(fetchedUsers);
    
    // If there is any user to discover
    if (fetchedUsers && fetchedUsers.length > 0) {

        renderUserCard(fetchedUsers, 0);

    } else {

        renderNoUsersLeft();

    }


});

// GET method: get users that match user (calling get_users endpoint)
async function fetchUsers() {

    try {

        const response = await fetch("discover.php?action=get_users");
        const users = await response.json();

        // IF SUCCESS = returns array of users data | ELSE = returns empty array
        if (users.success){
            return users.message;
        } else {
            return [];
        }
        
    } catch (error) {
        console.log(error);
        return [];
    }
}

// JS that makes AJAX call to insert user interaction in BBDD
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

// JS that makes AJAX call to insert user interaction in BBDD
async function insertInteraction(interactedUserID, interactionState) {

    try {
        
        const response = await fetch('discover.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({endpoint: "insertInteraction", interactedUserID, interactionState})
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

// JS that makes AJAX call to check if there's a match from the user the loggedUser interacted with
// Returs TRUE or FALSE depending if there's a like or not. TRUE = like
// Called in YES BUTTON
async function checkMatch(interactedUserID){

    try {
        
        const response = await fetch('discover.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({endpoint: "checkMatch", interactedUserID})
        });

        // resultado de JSON a objeto Javascript. PHP devuelve {success: error, message: "abc"}
        const result = await response.json();

        // Segun resultado, pone mensaje de error o no
        if (result.success) { 
            return result.match;
        } else {
            console.log(result.message);
        }

    } catch (error) {
        console.log('Error al comunicarse con el servidor: ' + error)
    }

}

// AFTER CHECKING MATCH 
// JS that makes AJAX call to insert match after checking it
// Called in YES BUTTON event
async function insertMatch(interactedUserID) {

    try {
        const response = await fetch('discover.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({endpoint: "insertMatch", interactedUserID})
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

// RENDERIZATION
function renderNoUsersLeft() {

    const container = document.getElementById('content');
    const endMessage = document.createElement('h2');
    endMessage.id = "end-message";
    endMessage.textContent = 'No hi ha perfils disponibles';
    container.appendChild(endMessage);

    insertLog(`Rendered no users left`, "INFO");
}


function renderUserCard(users, index) {

    // Delete all html inside the main content Div
    const container = document.getElementById('content');
    container.innerHTML = ''; 

    // If there are no users left after render, set empty content
    if (index >= users.length) {
        renderNoUsersLeft();
        return;
    }


    // MARK: INTEGRATION

    // Get user from userIndex (not needed for profile and messages)
    const user = users[index];

    // IMAGE 

    const image = document.createElement('img');
    image.src = user.photos[0];
    image.alt =`photo_of_${user.info.alias}`;

    /* IMAGE CARROUSELL*/

    let carrouselContainer = null;

    if (user.photos.length > 1) {

        carrouselContainer = document.createElement('div');
        carrouselContainer.id = "carrousel-container";

        let currentIndex = 0;
        const dots = [];

        user.photos.map((photo , i) => {
            const dot = document.createElement('span');
            dot.classList.add('dot');
            dots.push(dot);
            if (i === 0) dot.classList.add('active');
            carrouselContainer.appendChild(dot);
        })

        console.log(dots);

        carrouselContainer.addEventListener("click", handleCarouselClick) 

        function handleCarouselClick() {
          
            currentIndex += 1;

            if (currentIndex >= user.photos.length) {
                currentIndex = 0;
            }
    
            image.src = user.photos[currentIndex];
    
            dots.map(dot => dot.classList.remove('active'));
            dots[currentIndex].classList.add('active');
        }

    }

    

    /*CONTAINER OF INFO -> search*/
    const infoContainer = document.createElement('div');
    infoContainer.id = "info-container";
    const nameText = document.createElement('h2');
    nameText.innerText = user.info.name;
    const ageText = document.createElement('h3');
    ageText.innerText = user.info.age;
    infoContainer.appendChild(nameText);
    infoContainer.appendChild(ageText);

    // MARK: END INTEGRATION
    
    // BUTTON DIV
    const buttonsContainer = document.createElement('div');
    buttonsContainer.id = "buttons-container";

    // NO BUTTON
    const noButton = document.createElement('button');
    noButton.textContent = 'NOP';
    noButton.id = "no-button";

    // Event
    noButton.addEventListener('click', function() {
        clickedNoButton(user, users, index);
    });

    // YES BUTTON
    const yesButton = document.createElement('button');
    yesButton.textContent = 'YES';
    yesButton.id = "yes-button";

    // Event
    yesButton.addEventListener('click', async () => {

        clickedYesButton(user, users, index)

    });

    // Hover
    yesButton.addEventListener('mouseover', function() {
        yesButton.style.backgroundColor = '#ff6b6b';
    });

    yesButton.addEventListener('mouseout', function() {
        yesButton.style.backgroundColor = '#cc5555';
    });

    buttonsContainer.appendChild(noButton);
    buttonsContainer.appendChild(yesButton);

    /* MARK: INTEGRATION */
    container.appendChild(image);

    if(carrouselContainer) {
        container.appendChild(carrouselContainer);
    }
  
    container.appendChild(infoContainer);
    /* MARK: END INTEGRATION */

    container.appendChild(buttonsContainer);

    insertLog(`Rendered user ${user.info.user_ID} card`, "INFO");

}

function clickedNoButton(user, users, index){

    insertLog(`Clicked NO on user ${user.info.user_ID}`, "INFO");

    MostrarAlertas("info", "Has clickat NOP")

    insertInteraction(user.info.user_ID, 'dislike');

    renderUserCard(users, index + 1);
}

async function clickedYesButton(user, users, index) {

    insertLog(`Clicked YES on user ${user.info.user_ID}`, "INFO");

    MostrarAlertas("info", "Has clickat YES")

    insertInteraction(user.info.user_ID, 'like');

    const isMatch = await checkMatch(user.info.user_ID);

    if (isMatch) {

        insertMatch(user.info.user_ID);

        showMatchOptionBox(user, users, index)

    } else {

        renderUserCard(users, index + 1);

    }
}

function showMatchOptionBox(user, users, index) {

    const container = document.getElementById('content');

    // Disable buttons while selecting users
    const yesButton = document.getElementById('yes-button');
    const noButton = document.getElementById('no-button');
    yesButton.disabled = true;
    noButton.disabled = true;
    yesButton.style.cursor = "default";
    noButton.style.cursor = "default";
    
    const optionBox = document.createElement('div');
    optionBox.id = "option-box";
    
    const matchTitle = document.createElement('h3');
    matchTitle.textContent = "Has fet Match!";

    const optionButtonBox = document.createElement('div');
    optionButtonBox.id = "buttons-box";

    const goToMessageButton = document.createElement('button');
    goToMessageButton.textContent = 'Anar a la conversa';
    goToMessageButton.id = "go-to-conversation";

    goToMessageButton.addEventListener('click', () => {

        insertLog(`Clicked GO TO CONVERSATION on user ${user.info.user_ID}`, "INFO");
        window.location.href = `messages.php?action=go_to_conversation&user=${user.info.alias}`;
   
    });

    const keepDiscoveringButton = document.createElement('button');
    keepDiscoveringButton.textContent = 'Seguir descobrint';
    keepDiscoveringButton.id = "continue-discovering";

    keepDiscoveringButton.addEventListener('click', () => {

        insertLog(`Clicked KEEP DISCOVERING on user ${user.info.user_ID}`, "INFO");

        /* RESET BUTTONS */ 
        yesButton.disabled = false;
        noButton.disabled = false;
        yesButton.style.cursor = "pointer";
        noButton.style.cursor = "pointer";

        renderUserCard(users, index + 1);
        optionBox.remove();

    });

    // PRUEBAS
    // Create the backdrop div
    const backdropDiv = document.createElement('div');
    backdropDiv.id = "backdrop-div";

    // Append the backdrop div to the container
    container.appendChild(backdropDiv);

    optionButtonBox.appendChild(goToMessageButton);
    optionButtonBox.appendChild(keepDiscoveringButton);
    optionBox.appendChild(matchTitle);
    optionBox.appendChild(optionButtonBox);
    container.appendChild(optionBox);
    
}


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

    setTimeout(() => {
        typeAlerta.style.display = "none";
        typeAlerta.remove(); // Elimina el elemento del DOM
    }, 3000); // 3 segundos
    
}
