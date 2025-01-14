document.addEventListener("DOMContentLoaded", async () => {

    // Gets array of fetched users
    const fetchedUsers = await fetchUsers();

    // If there is any user to discover
    if (fetchedUsers && fetchedUsers.length > 0) {

        console.log(fetchedUsers);
        renderUserCard(fetchedUsers, 0);

        // LOG

    } else {

        // LOG
        console.log("No users left")
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
            console.log(users.message);
            return [];
        }
        
    } catch (error) {
        console.error("Error al cargar usuaris:", error);
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
            console.log(`Consulta correcta: ES MATCH? ${result.match}`);
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
    endMessage.textContent = 'No hi ha perfils disponibles';
    endMessage.style.textAlign = "center";
    endMessage.style.marginTop = "45%";
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

    // Get user from userIndex
    const user = users[index];

    // IMAGE 

    const image = document.createElement('img');
    image.src = user.photos[0];
    image.alt =`photo_of_${user.info.alias}`;
    image.style.width = "100%";
    image.style.height = "92%";
    image.style.objectFit = "cover";
    image.style.objectPosition = "center";

    // BUTTON DIV
    const buttonsContainer = document.createElement('div');
    buttonsContainer.style.position = "absolute";
    buttonsContainer.style.top = "85%";
    buttonsContainer.style.left = "50%";
    buttonsContainer.style.transform = "translateX(-50%)";
    buttonsContainer.style.display = "flex";
    buttonsContainer.style.justifyContent = "center";
    buttonsContainer.style.gap = "40px";

    // NO BUTTON
    const noButton = document.createElement('button');
    noButton.textContent = 'NOP';
    noButton.id = "no-button";
    noButton.style.width = '90px';
    noButton.style.height = '90px';
    noButton.style.borderRadius = '50%';
    noButton.style.color = 'white';
    noButton.style.fontWeight = 'bold';
    noButton.style.fontSize = '1.25rem';
    noButton.style.cursor = 'pointer';
    noButton.style.display = 'flex';
    noButton.style.alignItems = 'center';
    noButton.style.justifyContent = 'center';
    // Difference with yes
    noButton.style.border = '8px solid darkred';
    noButton.style.backgroundColor = 'red';
    // Event
    noButton.addEventListener('click', function() {
        clickedNoButton(user, users, index);
    });

    // YES BUTTON
    const yesButton = document.createElement('button');
    yesButton.textContent = 'YES';
    yesButton.id = "yes-button";
    yesButton.style.width = '90px';
    yesButton.style.height = '90px';
    yesButton.style.borderRadius = '50%';
    yesButton.style.color = 'white';
    yesButton.style.fontWeight = 'bold';
    yesButton.style.fontSize = '1.25rem';
    yesButton.style.cursor = 'pointer';
    yesButton.style.display = 'flex';
    yesButton.style.alignItems = 'center';
    yesButton.style.justifyContent = 'center';
    // Difference with no
    yesButton.style.border = '8px solid darkgreen';
    yesButton.style.backgroundColor = 'green';
    // Event
    yesButton.addEventListener('click', async () => {

        clickedYesButton(user, users, index)

    });

    buttonsContainer.appendChild(noButton);
    buttonsContainer.appendChild(yesButton);
    container.appendChild(image);
    container.appendChild(buttonsContainer);

}

function clickedNoButton(user, users, index){

    insertLog(`Clicked NO on user ${user.info.user_ID}`, "INFO");

    insertInteraction(user.info.user_ID, 'dislike');
   
    renderUserCard(users, index + 1);

}

async function clickedYesButton(user, users, index) {

    insertLog(`Clicked YES on user ${user.info.user_ID}`, "INFO");

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
    optionBox.style.position = 'absolute';
    optionBox.style.top = '45%';
    optionBox.style.left = '50%';
    optionBox.style.transform = 'translate(-50%, -50%)';
    optionBox.style.backgroundColor = 'white';
    optionBox.style.borderRadius = '8px';
    optionBox.style.padding = '20px';
    optionBox.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.5)';
    optionBox.style.textAlign = 'center';

    const matchTitle = document.createElement('h3');
    matchTitle.textContent = "Has fet Match!";

    const optionButtonBox = document.createElement('div');
    optionButtonBox.style.display = 'flex';
    optionButtonBox.style.gap = '10px';
    optionButtonBox.style.marginTop = '15px';
    optionButtonBox.style.flexDirection = 'column';

    const goToMessageButton = document.createElement('button');
    goToMessageButton.textContent = 'Anar a la conversa';
    goToMessageButton.style.padding = '10px 20px';
    goToMessageButton.style.border = 'none';
    goToMessageButton.style.borderRadius = '4px';
    goToMessageButton.style.cursor = 'pointer';
    goToMessageButton.style.fontSize = '16px';
    goToMessageButton.style.backgroundColor = "#FF6B6B";
    goToMessageButton.style.color = "#fff";
    goToMessageButton.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';

    goToMessageButton.addEventListener('click', () => {
        insertLog(`Clicked GO TO CONVERSATION on user ${user.info.user_ID}`, "INFO");
        window.location.href = `messages.php?action=go_to_conversation&user=${user.info.alias}`;
    });

    const keepDiscoveringButton = document.createElement('button');
    keepDiscoveringButton.textContent = 'Seguir descobrint';
    keepDiscoveringButton.style.padding = '10px 20px';
    keepDiscoveringButton.style.border = 'none';
    keepDiscoveringButton.style.borderRadius = '4px';
    keepDiscoveringButton.style.cursor = 'pointer';
    keepDiscoveringButton.style.fontSize = '16px';
    keepDiscoveringButton.style.backgroundColor = "#e0e0e0";
    keepDiscoveringButton.style.color = "#333";
    keepDiscoveringButton.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';

    keepDiscoveringButton.addEventListener('click', () => {

        insertLog(`Clicked KEEP DISCOVERING on user ${user.info.user_ID}`, "INFO");

        yesButton.disabled = false;
        noButton.disabled = false;
        yesButton.style.cursor = "pointer";
        noButton.style.cursor = "pointer";

        renderUserCard(users, index + 1);
        optionBox.remove();
        
    });

    optionButtonBox.appendChild(goToMessageButton);
    optionButtonBox.appendChild(keepDiscoveringButton);
    optionBox.appendChild(matchTitle);
    optionBox.appendChild(optionButtonBox);
    container.appendChild(optionBox);
    
}

