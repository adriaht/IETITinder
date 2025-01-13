// MAIN EXECUTION

document.addEventListener("DOMContentLoaded", async () => {

    // PLACEHOLDER
    const userContainer = document.createElement('div');
    userContainer.id = 'user-c  ontainer';
    // lo pone al final del documento.
    document.body.appendChild(userContainer);

    // Gets array of fetched users
    const fetchedUsers = await fetchUsers();

    // If there is any user to discover
    if (fetchedUsers && fetchedUsers.length > 0) {

        console.log(fetchedUsers)

        // EVENT TO RENDER USER CARDS

        // LOG

    } else {

        // LOG

        // EVENT
        // Show message there's no users left to show
        console.log("No users left")

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