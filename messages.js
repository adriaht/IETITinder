const matchesWithoutMessages = [];
const matchesWithMessages = [];
let user = [];
let actualMatch = 0;
let oldconversation = {};


document.addEventListener("DOMContentLoaded", async () => {
    // Get the match_id and user_id from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');
    const userAlias = urlParams.get('user');

    user = await fetchLoggedUserID(); //recogemos userID
    user_ID=user[0];

    // GO TO CONVERSATION
    if (action === "go_to_conversation" && userAlias) {
        try {
            generateConversation(userAlias);
            insertLog(`Loaded conversation in messages.js for user ${userAlias}`, "INFO");
        } catch (error) {
            insertLog(`Failed while loading conversation in messages.js for user ${userAlias}`, "INFO");
        }


    // GO TO MATCHES
    } else {
        insertLog("Loaded main page in messages.js", "INFO");
        // Makes AJAX call to get matches
        const arrMatchedUsers = await fetchMatches();
        try {
            // inserts matches in each array
            classifyMatches(arrMatchedUsers);
            if (matchesWithoutMessages.length > 0) {
                insertLog("User got matches without messages. Generating content", "INFO");
                const contentContainer = document.createElement("div");
                contentContainer.id = 'container-without-messages-content';
                matchesWithoutMessages.map(match => {
                    contentContainer.append(generateWithoutMessageCard(match));
                })
                const container = document.getElementById("container-without-messages");
                container.appendChild(contentContainer);
                insertLog("Successfully rendered matches without messages", "INFO");
            } 
            else {
                insertLog("Initiating renderization that there are no matches without messages", "INFO");
                renderNoMatchWithoutConversation();
                insertLog("Successfully rendered there are no matches without messages", "INFO");
            }

            if (matchesWithMessages.length > 0) {
                insertLog("User got matches with messages. Generating content", "INFO");
                const contentContainer = document.createElement("div");
                contentContainer.id = 'container-with-messages-content';

                matchesWithMessages.map(match => {
                    contentContainer.append(generateWithMessageCard(match));
                })

                const container = document.getElementById("container-with-messages");
                container.appendChild(contentContainer);

            } else {
                insertLog("Initiating renderization that there are no matches with messages", "INFO");
                renderNoMatchWithConversation();
                insertLog("Successfully rendered there are no matches with messages", "INFO");
            }

        } catch (error) {
            insertLog(`Failed while loading matches in messages.js: ${error}`, "ERROR");
        }
    }


    //FUNCIONALIDADES DEL CHAT ----------------------------------------------------------------------------------------------

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
        tablinks = document.getElementsByClassName("tablinks");
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

    //SEND MESSAGE BUTTON
    const sendMessageButton = document.getElementById("chat-send-button");

    sendMessageButton.addEventListener('click', async (e) => {
        let messageText = document.getElementById("chat-text-input").value;
        let match_id = document.getElementById("chat-name").getAttribute("match_ID"); //recogemos el match ID
        //si no está vacío lo mandamos
        if (messageText !=""){
            insertMessage(match_id, user_ID, messageText);
            renderTextMessage(user_ID, Date.now(), user_ID, messageText);
        }
        document.getElementById("chat-text-input").value = "";//vaciamos input
    });

    const goBackButton = document.getElementById("goBackToMessages");
        goBackButton.addEventListener('click', async (e) => {
        document.getElementById("chat-page").style.display = "none";
        document.getElementById("content").style.display = "block";
        
    });

});

//FUNCIONES MESSAGES --------------------------------------------------------------------

function generateWithoutMessageCard(match) {

    // Create the container div for the match
    const withoutMessageContent = document.createElement("div");
    withoutMessageContent.className = "without-message-content";

    // Create the image element
    const img = document.createElement("img");
    img.src = match.picture_path;
    img.alt = `${match.alias} photo`;

    // Create the paragraph element with the match's name (assuming `match` has a `name` property)
    const p = document.createElement("p");
    p.textContent = match.alias || "Error"; // Use match.name or fallback to "Jaime" if not available

    // Append the img and p elements to the withoutMessageContent div
    withoutMessageContent.appendChild(img);
    withoutMessageContent.appendChild(p);

    withoutMessageContent.addEventListener("click", () => {
        generateConversation(match.alias);
    });

    // Append the new content to the contentContainer
    return withoutMessageContent;
}

function generateWithMessageCard(match) {

    // Create the main div container
    const withMessageContent = document.createElement('div');
    withMessageContent.classList.add('with-message-content');

    // Create the image element
    const img = document.createElement('img');
    img.src = match.picture_path;
    img.alt = `${match.alias} photo`;

    // Create the user info div container
    const userInfoWithMessageContent = document.createElement('div');
    userInfoWithMessageContent.classList.add('user-info-with-message-content');

    // Create the name paragraph
    const alias = document.createElement('p');
    alias.classList.add('name');
    alias.textContent = match.alias || "Error";

    // Create the last message paragraph
    const lastMessage = document.createElement('p');
    lastMessage.classList.add('last-message');
    lastMessage.textContent = match.lastMessage;

    // Append elements in the correct hierarchy
    userInfoWithMessageContent.appendChild(alias);
    userInfoWithMessageContent.appendChild(lastMessage);


    withMessageContent.appendChild(img);
    withMessageContent.appendChild(userInfoWithMessageContent);

    withMessageContent.addEventListener("click", () => {
        generateConversation(match.alias);
    });

    // Append the created div to the body or any other parent element
    return withMessageContent;
}

async function generateConversation(alias) {
    //CHAT TAB ------------
    document.getElementById("chat-page").style.display = "block";
    document.getElementById("content").style.display = "none";

    let userData = await fetchUserNameAndImage(alias); //recogemos nombre y foto de la persona
    let match_ID = await fetchMatchID(alias); //recogemos el matchID

    console.log("USER DATA AQUI: ", userData)

    document.getElementById("chat-image").src = userData[0].path;
    document.getElementById("chat-name").textContent = userData[0].name;

    document.getElementById("chat-name").setAttribute("match_ID", match_ID); //ponemos el match_ID en el nombre
   
    //definimos funcion de Ejecutar Render
    const executeRender = () => {
        console.log("RENDER CONVERSATION");
        renderConversation(alias, user_ID);
    };

    //ejecutamos 1 vez para cuando se entre al chat
    executeRender();

    //hacemos que a partir de entonces se ejecute cada 5seg
    setInterval(executeRender, 5000);

    //PROFILE TAB -------------------

    const image = document.getElementById('profileTab-img');
    image.src = userData[0].path;
    image.alt = `photo_of_,${ alias }`;

    const nameText = document.getElementById('profileTab-name');
    nameText.innerText = userData[0].name;

    const ageText = document.getElementById('profileTab-age');
    ageText.innerText = userData[0].age;

}

function renderNoMatchWithoutConversation() {

    // Create the outer div with the specified class
    const noMatchesDiv = document.createElement('div');
    noMatchesDiv.className = 'no-matches-without-messages';

    // Create the first paragraph
    const paragraph1 = document.createElement('p');
    paragraph1.textContent = 'Hi ha gent esperant per parlar amb tu.';

    // Create the second paragraph
    const paragraph2 = document.createElement('p');
    paragraph2.textContent = 'Torna\'ls el like per començar a xatejar';

    // Append paragraphs to the outer div
    noMatchesDiv.appendChild(paragraph1);
    noMatchesDiv.appendChild(paragraph2);

    // Find the container-without-messages element
    const container = document.getElementById('container-without-messages');
    container.appendChild(noMatchesDiv);
}

function renderNoMatchWithConversation() {
    // Create a new div element with the required class
    const noMatchesDiv = document.createElement('div');
    noMatchesDiv.className = 'no-matches-with-messages';

    // Create the first paragraph
    const firstParagraph = document.createElement('p');
    firstParagraph.textContent = 'No hi ha cap conversa,';

    // Create the second paragraph
    const secondParagraph = document.createElement('p');
    secondParagraph.textContent = 'descobreix gent nova i fes match';

    // Append paragraphs to the div
    noMatchesDiv.appendChild(firstParagraph);
    noMatchesDiv.appendChild(secondParagraph);

    // Find the container div
    const container = document.getElementById('container-with-messages');

    // Append the new div to the container
    if (container) {
        container.appendChild(noMatchesDiv);
    } else {
        console.error('Container with id "container-with-messages" not found.');
    }

}

// CLEAR NOT USED BY THE MOMENT
function clearWithoutMessageCard() {
    const container = document.getElementById("container-without-messages");

    container.innerHTML = "";

    const title = document.createElement("h2");
    title.textContent = "Els meus matches";
    container.appendChild(title);
}

function clearWithMessageCard() {
    const container = document.getElementById("container-with-messages");
    container.innerHTML = "";

    const title = document.createElement("h2");
    title.textContent = "Missatges";
    container.appendChild(title);
}

// Function to get matches and user data using AJAX
async function fetchMatches() {

    try {
        const response = await fetch("messages.php?action=get_matches");
        const users = await response.json();
        if (users.success) {
            insertLog("Sucessfully loaded matches in messages.js", "INFO");
            return users.message;
        } else {
            insertLog("Sucessfully loaded matches in messages.js", "ERROR");
            return [];
        }

    } catch (error) {
        insertLog(`While loading matches: ${error}`, "ERROR");
        return [];
    }

}

// JS that makes AJAX call to insert user interaction in BBDD
async function insertLog(logMessage, type) {

    try {

        const response = await fetch('discover.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ endpoint: "insertLog", logMessage, type })
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

// Classify matches based on if it hasMessage or not (true/false)
function classifyMatches(arrMatches) {

    arrMatches.map(match => {

        if (match.hasMessage) {

            matchesWithMessages.push(match);

        } else {

            matchesWithoutMessages.push(match);

        }

    });

    insertLog("Successfully classified matches", "INFO");

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


//FUNCIONES DEL CHAT -------------------------------------------------------------------------

// Funcion para coger el match_id
async function fetchMatchID(alias) {
    try {
        const response = await fetch(`messages.php?getMatchID=${encodeURIComponent(alias)}`);
        const match = await response.json();
        if (match.success){
            insertLog("Sucessfully fetched match_ID in messages.js", "INFO");
            return match.match_ID;
        } else {
            insertLog("Error fetching match_ID in messages.js", "ERROR");
            return [];
        }
        
    } catch (error) {
        insertLog(`While fetching match_ID: ${error}`, "ERROR");
        return [];
    }
}


// Funcion para coger el ID de la persona loggeada
async function fetchLoggedUserID() {
    try {
        const response = await fetch('messages.php?action=getLoggedUserID');
        const user = await response.json();
        if (user.success){
            insertLog("Sucessfully fetched logged userID in messages.js", "INFO");
            return user.message;
        } else {
            insertLog("Error fetching logged userID in messages.js", "ERROR");
            return [];
        }
        
    } catch (error) {
        insertLog(`While loading logged userID: ${error}`, "ERROR");
        return [];
    }
}

// Funcion para coger el nombre y la foto de la otra persona
async function fetchUserNameAndImage(alias) {
    try {
        const response = await fetch(`messages.php?getUserNameAndImage=${encodeURIComponent(alias)}`);
        const user = await response.json();
        console.log(user);
        if (user.success){
            insertLog("Sucessfully fetched user name and image in messages.js", "INFO");
            return user.message;
        } else {
            insertLog("Error fetching user name and image in messages.js", "ERROR");
            return [];
        }
        
    } catch (error) {
        insertLog(`While fetching user name and image: ${error}`, "ERROR");
        return [];
    }
}


// Funcion para formatar el timestamp en catalan
function formatTimestampToCatalan(timestamp) {
    const date = new Date(timestamp);

    // Configuración para el idioma catalán
    const formatter = new Intl.DateTimeFormat('ca-ES', {
        weekday: 'long', // Día de la semana completo
        day: '2-digit', // Día del mes con dos dígitos
        month: 'long', // Mes completo
        year: 'numeric', // Año completo
        hour: '2-digit', // Hora en formato 24 horas
        minute: '2-digit', // Minutos
    });

    // Formatear la fecha
    const formattedDate = formatter.format(date);

    return formattedDate.replace(",", ""); // Quitar coma si es necesario
}

// Funcion para insertar 1 mensaje en la bbdd
async function insertMessage(matchID, senderID, messageContent) {
    try {
        const response = await fetch('messages.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({endpoint: "insertMessage", matchID, senderID, messageContent})
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


// Funcion para renderizar toda una conversacion
async function renderConversation(alias, user_ID){
    let conversation = await fetchConversation(alias); //recogemos los mensajes de la bbdd

    console.log(oldconversation);
    console.log(conversation);

    if (oldconversation != conversation){ //si ha cambiado algo, recargamos chat
        console.log("HA CAMBIADO!");
        const chatMessagesContainer = document.getElementById('chat-messages-container');
        chatMessagesContainer.innerHTML = ''; //clear
        for (let i = 0; i < conversation.length; i++) {
        const message = conversation[i];
        renderTextMessage(user_ID, message.creation_date, message.sender_id, message.content); //llamamos a renderizar 1 mensaje
        scrollToBottom();
    }
    }

    oldconversation = conversation;
}


// Funcion para recuperar mensajes de la bbdd
async function fetchConversation(alias) {
    try {
        const response = await fetch(`messages.php?getConversation=${encodeURIComponent(alias)}`);
        const conversation = await response.json();
        if (conversation.success){
            insertLog("Sucessfully fetched conversation in messages.js", "INFO");
            return conversation.message;
        } else {
            insertLog("Error fetching  conversation in messages.js", "ERROR");
            return [];
        }
        
    } catch (error) {
        insertLog(`While fetching conversation: ${error}`, "ERROR");
        return [];
    }
}


// Función para desplazar hacia el final del contenedor
function scrollToBottom() {
    const chatMessagesContainer = document.getElementById("chat-messages-container");
    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
}


// Funcion para renderizar 1 mensaje
function renderTextMessage(user_ID, creation_date, sender_id, content) {
    const chatContainer = document.getElementById("chat-messages-container");
    let lastTextMessage = chatContainer.lastElementChild; //ultimo mensaje

    let textMessage = document.createElement('p');

    if (sender_id == user_ID) {
        textMessage.classList.add("chat-me");
    } else {
        textMessage.classList.add("chat-you");
    }

    textMessage.textContent = content; //mensaje
    textMessage.setAttribute("data-timestamp", creation_date); //timestamp

    //Si el contenedor no está vacío, verificamos el último mensaje
    if (lastTextMessage) {
        let lastTimestamp = lastTextMessage.getAttribute("data-timestamp");
        const date1 = new Date(creation_date);
        const date2 = new Date(lastTimestamp);

        if (date1 - date2 > (5*60 * 1000)) { //5min
            let textTime = document.createElement('p');
            textTime.classList.add("chat-time");
            textTime.textContent = formatTimestampToCatalan(creation_date);
            chatContainer.append(textTime);
        }
    }
    else{
        let textTime = document.createElement('p');
        textTime.classList.add("chat-time");
        textTime.textContent = formatTimestampToCatalan(creation_date);
        chatContainer.append(textTime);
    }

    chatContainer.appendChild(textMessage);
}