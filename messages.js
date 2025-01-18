const matchesWithoutMessages = [];
const matchesWithMessages = [];

document.addEventListener("DOMContentLoaded", async () => {


    // Get the match_id and user_id from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');
    const userAlias = urlParams.get('user');

    console.log(action)
    console.log(userAlias)

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
            
            if (matchesWithoutMessages.length > 0){

                insertLog("User got matches without messages. Generating content", "INFO");

                const contentContainer = document.createElement("div");
                contentContainer.id = 'container-without-messages-content';

                matchesWithoutMessages.map(match => {

                    contentContainer.append(generateWithoutMessageCard(match));

                })

                const container = document.getElementById("container-without-messages");
                container.appendChild(contentContainer);

                insertLog("Successfully rendered matches without messages", "INFO");

            } else {


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

});

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

function generateConversation(alias) {
    const mainContainer = document.getElementById("content");
    mainContainer.innerHTML = "";
    const p = document.createElement("p");
    p.innerText = `Conversa amb ${alias}`;
    mainContainer.appendChild(p);

    MostrarAlertas("warning", "Funcionalitat en desenvolupament")
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
        if (users.success){
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

// Classify matches based on if it hasMessage or not (true/false)
function classifyMatches(arrMatches){

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