
document.addEventListener("DOMContentLoaded", async () => {

    max_photos = 6;

    // Gets array of fetched photos
    userPhotos = await fetchLoggedUserPhotos();

    if (userPhotos && userPhotos.length > 0) {

        const mainContent = document.getElementById("content");

        const divContent = document.createElement("div");
        divContent.id = "photos-content";
        const title = document.createElement("h2");
        title.innerText = "Les meves fotos"
        divContent.appendChild(title);
    
        mainContent.appendChild(divContent);
    
        const photoDivContent = document.createElement("div");
        photoDivContent.id = "photos-container";
        divContent.appendChild(photoDivContent);
    
        renderPhotos(photoDivContent, userPhotos)
        
        const errorDiv = document.createElement("div");
        errorDiv.id = "error-div";
        const error = document.createElement("h3");
        error.id = "error-text";
        errorDiv.appendChild(error);
        divContent.appendChild(errorDiv);
         
    } else {

        console.log("ERROR AL CARGAR IMÁGENES");

    }
  
   
});

function renderPhotos(photoContainer, arrPhotos){

    photoContainer.innerHTML = "";

    let index = 0;

    // console.log(`INDEX ANTES DE ENTRAR = ${index}`)

    while (index < max_photos && index < arrPhotos.length) {

        // console.log(`INDEX ANTES DE GENERAR = ${index}`)

        const divPhoto = document.createElement("div");

        const imgPhoto = document.createElement("img");
        imgPhoto.src = arrPhotos[index].path;
        imgPhoto.alt = arrPhotos[index].path.substring(arrPhotos[index].path.lastIndexOf("/") + 1);

        const deleteButton = document.createElement("button");
        deleteButton.innerText = "X"
        deleteButton.value = index;

        deleteButton.addEventListener("click", async () => {

            // console.log(deleteButton.value);
            // console.log(arrPhotos[deleteButton.value].id);
            // console.log(arrPhotos)

            if (arrPhotos.length === 1) {

                const error = document.getElementById("error-text")
                error.innerText = "Minim has de tenir una foto de perfil";

            } else {

                const error = document.getElementById("error-text")
                error.innerText = "";
            
                // DELETE PHOTO FROM BBDD
                console.log(arrPhotos[deleteButton.value].photo_ID)
                const isDeleted = await deletePhoto(arrPhotos[deleteButton.value].photo_ID);
                if (isDeleted) {
                    arrPhotos.splice(deleteButton.value, 1)
                    renderPhotos(photoContainer, arrPhotos)
                } else {
                    console.log("ERROR AL ELIMINAR LA IMÁGEN")
                }
              
            }
          
        })

        divPhoto.appendChild(imgPhoto);
        divPhoto.appendChild(deleteButton);
        photoContainer.appendChild(divPhoto);

        index += 1;
        // console.log(`INDEX DESPUES DE GENERAR = ${index}`)
    }

    // console.log(`INDEX DESPUÉS DE ENTRAR = ${index}`)

    while(index < max_photos) {

        const divAvailable = document.createElement("div");
        divAvailable.classList.add("available");

        divAvailable.addEventListener("click", async () => {

            // INSERT PHOTO
          
            // IF OK
            arrPhotos.push({id:50, path:"/images/user5_photo1.jpg"})
            renderPhotos(photoContainer, arrPhotos)
        })

        photoContainer.appendChild(divAvailable);
        index += 1;
    }

    /*
    if (index < max_photos) {

        const divAvailable = document.createElement("div");
        divAvailable.classList.add("available");

        divAvailable.addEventListener("click", async () => {

            // INSERT PHOTO
          
            // IF OK
            arrPhotos.push({id:50, path:"/images/user5_photo1.jpg"})
            renderPhotos(photoContainer, arrPhotos)
        })

        photoContainer.appendChild(divAvailable);
        index += 1;
    }

    // console.log(`INDEX DESPUÉS DE PRIMER AVAILABLE = ${index}`)
 
    while(index < max_photos) {

        const divAvailable = document.createElement("div");
        divAvailable.classList.add("disabled");
        photoContainer.appendChild(divAvailable);
        index += 1;

    }
    */
    // console.log(`INDEX AL FINAL = ${index}`)
}

async function fetchLoggedUserPhotos() {

    try {

        const response = await fetch("fotosDin.php?action=get_user_photos");
        const userPhotos = await response.json();

        // IF SUCCESS = returns array of users data | ELSE = returns empty array
        if (userPhotos.success){
            return userPhotos.message;
        } else {
            return;
        }
        
    } catch (error) {
        console.log(error)
        return;
    }
}

// JS that makes AJAX call to insert user interaction in BBDD
async function deletePhoto(photoID) {

    try {
        
        const response = await fetch('fotosDin.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({endpoint: "deletePhoto", photoID})
        });

        // resultado de JSON a objeto Javascript. PHP devuelve {success: error, message: "abc"}
        const result = await response.json();

        // Segun resultado, pone mensaje de error o no
        if (result.success) { 
            return result.success
        } else {
            return false;
        }

    } catch (error) {
        console.log('Error al comunicarse con el servidor: ' + error)
    }

}