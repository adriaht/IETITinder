let fetchedUsers = [];

document.addEventListener("DOMContentLoaded", async () => {

    //NAV changer
    document.getElementById("navDiscover").classList.add("navActive");

    // Gets array of fetched users
    fetchedUsers = await fetchUsers();
    // console.log("INITIAL USER FETCH");
    // console.log(fetchedUsers);
    
    // If there is any user to discover
    if (fetchedUsers && fetchedUsers.length > 0) {

        insertLog(`Found users that match preference when first logged`, "INFO");
        renderUserCard(fetchedUsers, 0);

    } else {

        insertLog(`NOT FOUND users that match preference when first logged`, "INFO");
        renderNoUsersLeft();

    }

    /* SUBMENU BUTTON FUNCTIONALITY */ 
    const submenuButton = document.getElementById("submenu-button");
    const greyBackground = document.getElementById("grey-background");
    // Toggle submenu visibility
    submenuButton.addEventListener("click", async () => {

        if (submenuButton.innerText === "· · ·") {
            
            // Gets user preference data to show in fields
            const userPreference = await fetchLoggedUserPreferences();
            //console.log("INITIAL PREFERENCE DATA")
            //console.log(userPreference);

            if (userPreference){
                greyBackground.style.display = "inline";
                renderPreferencesSubmenu(userPreference.distance_user_preference, userPreference.min_age_user_preference, userPreference.max_age_user_preference);
                submenuButton.innerText = "X";
            } else {
                MostrarAlertas("error", "No s'han pogut carregar les preferencies de l'usuari" )
            }

        } else {

            deletePreferencesSubmenu();

        }
    });
    /* END SUBMIT FUNCTIONALITY ----------------------------------------------------------------------------- */ 


});

/* MARK: SUBMIT PREFERENCE FUNCTIONALITY ----------------------------------------------------------------------------- */ 
// Only creates submenu. Grey background and button arehandled in DOMcontentLoaded 
async function renderPreferencesSubmenu(distance, minAge, maxAge) {

    const header = document.getElementById("header");

    // Create submenu
    const submenu = document.createElement("form");
    submenu.id = "submenu";

    // Div for errors
    const divTitle = document.createElement("div");
    divTitle.classList.add("fields");
    const divTitleText = document.createElement("h3");
    divTitleText.innerText = "Preferencies de l'usuari"
    divTitle.appendChild(divTitleText);
    submenu.appendChild(divTitle);
    

    // Div for errors
    const divErrorField = document.createElement("div");
    divErrorField.id = "error-field";
    divErrorField.classList.add("fields");
    submenu.appendChild(divErrorField);


    // Create list items
    const options = [
        { text: "Distància ", for:"distance", value: distance},
        { text: "Edat mínima ", for:"min_age", value: minAge},
        { text: "Edat màxima ", for:"max_age", value: maxAge},
    ];

    options.map(option => {

        const divField = document.createElement("div");
        divField.classList.add("fields");

        const labelField = document.createElement("label");
        labelField.setAttribute("for", option.for);
        labelField.innerText = option.text;
        
        const inputField = document.createElement("input");
        inputField.type = "number";
        inputField.id = option.for;
        inputField.name = option.for;
        inputField.value = option.value;

        divField.appendChild(labelField);
        divField.appendChild(inputField);

        if (option.for === "distance") {
            const labelField = document.createElement("label");
            labelField.innerText = " km";
            divField.appendChild(labelField);
        }

        submenu.appendChild(divField);
        
    });

    // Div submit button
    const divField = document.createElement("div");
    divField.classList.add("send");

    const submitButton = document.createElement("input");
    submitButton.type = "submit";

    divField.appendChild(submitButton);
    submenu.appendChild(divField);
    header.appendChild(submenu);

    submenu.addEventListener("submit", handleSubmittedPreference)

    insertLog(`Opened submenu`, "INFO");
}

// Deletes submenu of preference, restores button to " . . . " and hides grey background 
function deletePreferencesSubmenu() {
    const submenuButton = document.getElementById("submenu-button");
    submenuButton.innerText = "· · ·";

    const greyBackground = document.getElementById("grey-background");
    greyBackground.style.display = "none";

    const submenu = document.getElementById("submenu");
    submenu.remove();
    insertLog(`Closed submenu`, "INFO");
}

// Gets form, checks for errors and show errors or send POST request to update user preferences in BBDD
function handleSubmittedPreference(e){

    // Stops submit to check data
    e.preventDefault();

    const errorDiv = document.getElementById("error-field");
    errorDiv.innerHTML = "";

    const userPreferenceFormElements = document.forms[0].elements;
    const distance = userPreferenceFormElements[0].value;
    const minAge = userPreferenceFormElements[1].value;
    const maxAge = userPreferenceFormElements[2].value;

    const submitButton = userPreferenceFormElements[3];
    submitButton.classList.add("disabled");

    // Change if min/max parameters change
    const rangEdat = [18, 60]

    const errors = [];

    if (distance < 0 || distance > 200) {
        errors.push("La distància ha d'estar entre 0 i 200 km.");
    }

    if (minAge < rangEdat[0] || minAge > rangEdat[1] || maxAge < rangEdat[0] || maxAge > rangEdat[1]) {
        errors.push("La edat ha d'estar entre el rang de 18 i 60 anys.");
    }

    if (minAge > maxAge) {
        errors.push("La edat mínima no pot ser superior a la màxima.");
    }

    if (!errors.length) {

        insertLog(`Data sent by user is correct: DISTANCE ${distance} | MIN_AGE ${minAge} | MAX_AGE ${maxAge}`, "INFO");
        errorDiv.innerHTML = "";
        updateUserPreferences(distance, minAge, maxAge);

    } else {

        insertLog(`Data sent by user DON'T respect limits: DISTANCE ${distance} | MIN_AGE ${minAge} | MAX_AGE ${maxAge}`, "INFO");
        submitButton.classList.remove("disabled");
        showErrorsInSubmittedPreference(errors.join("\n"));
        
    }

}

// Gets div or error inside preference submenu
function showErrorsInSubmittedPreference(message){
    const errorDiv = document.getElementById("error-field");
    const divErrorParagraphField = document.createElement("p");
    divErrorParagraphField.innerText = message;
    errorDiv.appendChild(divErrorParagraphField);
}

// AJAX request to update user preferences --> Data recibed handled inside this request
async function updateUserPreferences(distance, minAge, maxAge) {

    try {

        const response = await fetch('discover.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({endpoint: "updateUserPreferences", distance, minAge, maxAge})
        });

        //
        const result = await response.json();

        // Success but PHP might got wrong input data (change between client-server)
        if (result.success) { 

            // Success and data sent is valid (respects all parameters and limits of age and distance)
            if (result.updated){

                // Info to user
                MostrarAlertas("info", "Preferències guardades correctament")

                // Fetches user data based on new filters and updates card
                fetchedUsers = await fetchUsers();

                //console.log("FETCHED USERS AFTER PREFERENCE CHANGE");
                //console.log(fetchedUsers);

                setTimeout(() => {

                    // Closes submenu
                    deletePreferencesSubmenu()

                     // If there is any user to discover, show card
                    if (fetchedUsers && fetchedUsers.length > 0) {
                        insertLog(`[DISCOVER.PHP] Found users that match the new user preference. Rendering users`, "INFO");
                        renderUserCard(fetchedUsers, 0);

                    } else { // show no users let
                        insertLog(`[DISCOVER.PHP] NOT FOUND users that match the new user preference. Rendering users`, "INFO");
                        renderNoUsersLeft();

                    }
               
                  }, 3000);

               
            // Success in request but data sent is invalid (don't respect parameters)
            } else {

                // restores button functionality in case php detects invalid data sent (don't respect parameters)
                const submitButton = document.forms[0].elements[3];
                submitButton.classList.remove("disabled");

                showErrorsInSubmittedPreference(result.message);

            }

        } else {

            console.log('Error in updateUserPreference: ' +  result.message)

        }

    } catch (error) {

        console.log('Error al comunicarse con el servidor en updateUserPreference: ' + error)

    }

}

/* END SUBMIT PREFERENCE FUNCTIONALITY ----------------------------------------------------------------------------- */ 

// GET method: get users that match user (calling get_users endpoint)
async function fetchLoggedUserPreferences() {

    try {

        const response = await fetch("discover.php?action=get_logged_user_preferences");
        const userPreference = await response.json();

        // IF SUCCESS = returns array of users data | ELSE = returns empty array
        if (userPreference.success){
            return userPreference.message;
        } else {
            return;
        }
        
    } catch (error) {
        console.log(error)
        return;
    }
}

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

// POST: makes AJAX call to insert LOGS in /logs
async function insertLog(logMessage, type) {
    logMessage = "[DISCOVER.JS] " + logMessage; 
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


// MARK: MAIN RENDERIZATION
function renderNoUsersLeft() {

    const container = document.getElementById('content');
    container.innerHTML = "";
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

        insertLog(`Ran out of users left. Showing NO USERS LEFT`, "INFO");
        renderNoUsersLeft();
        return;
    }


    // MARK: CARROUSEL INTEGRATION

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

    // MARK: END PHOTO INTEGRATION
    
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

// NOTIFICATIONS
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
        insertLog(`Hide alert: ${missageAlert}`, "INFO");
    }, 3000); // 3 segundos
    insertLog(`Showed alert: [${typeAlerta}] ${missageAlert}`, "INFO");
}
