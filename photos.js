
document.addEventListener("DOMContentLoaded", async () => {

    // Parameter quantity of photos
    max_photos = 6;

    // Gets array of fetched photos
    userPhotos = await fetchLoggedUserPhotos();
    console.log(userPhotos);

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

    // GENERATION OF GET PHOTOS
    while (index < max_photos && index < arrPhotos.length) {

        // console.log(`INDEX ANTES DE GENERAR = ${index}`)

        const divPhoto = document.createElement("div");

        const imgPhoto = document.createElement("img");
        imgPhoto.src = arrPhotos[index].path;
        imgPhoto.alt = arrPhotos[index].path.substring(arrPhotos[index].path.lastIndexOf("/") + 1);

        const deleteButton = document.createElement("button");
        deleteButton.innerText = "X"
        deleteButton.value = index;
         /*MARK: DELETE*/ 
        deleteButton.addEventListener("click", async () => {

            // console.log(deleteButton.value);
            // console.log(arrPhotos[deleteButton.value].id);
            // console.log(arrPhotos)

            if (arrPhotos.length === 1) {

                const error = document.getElementById("error-text")
                error.innerText = "Mínim has de tenir una foto de perfil";

            } else {

                const error = document.getElementById("error-text")
                error.innerText = "";
            
                // DELETE PHOTO FROM BBDD

                const isDeleted = await deletePhoto(arrPhotos[deleteButton.value].photo_ID, arrPhotos[deleteButton.value].path);
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

    // GENERATION OF AVAILABLE SPACES
    while(index < max_photos) {

        const divAvailable = document.createElement("div");
        divAvailable.classList.add("available");

        let input = document.createElement("input");
        input.type = "file";
        input.name = "fileToUpload";

        input.addEventListener("change", () => {
            handlePhotoUpload(input);
        })

        divAvailable.appendChild(input);

        divAvailable.addEventListener("click", async () => {

            const error = document.getElementById("error-text")
            error.innerText = "";

            input.click();

            /* IF EVERYTHING IS ALRIGHT
                userReloadedPhotos = await fetchLoggedUserPhotos();
                renderPhotos(photoContainer, userReloadedPhotos);
            */

        })

        photoContainer.appendChild(divAvailable);
        index += 1;
    }

    /* IF WANT TO BE DISABLED
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

async function handlePhotoUpload(input) {
    console.log(input);
    console.log(input.files);
    const file = input.files[0];
    console.log(file);

    if (file) {

        const validImageTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
        const fileType = file.type;

        if (!validImageTypes.includes(fileType)) {

            const error = document.getElementById("error-text")
            error.innerText = "Selecciona un format vàlid (JPG, JPEG, PNG, or WEBP)";
            return; 

        }

        let formData = new FormData();
        formData.append("image", file);
        formData.append("endpoint", "imageUpload");
        console.log(formData);

        let isUploadedCorrectly = await uploadPhoto(formData);
        if(isUploadedCorrectly) {

            userReloadedPhotos = await fetchLoggedUserPhotos(); 
            const container = document.getElementById("photos-container")
            renderPhotos(container, userReloadedPhotos);

        } else {
            const error = document.getElementById("error-text")
            error.innerText = "Error al pujar la imatge";
        }
    } else {

        const error = document.getElementById("error-text")
        error.innerText = "No s'ha pujat cap arxiu.";
    }
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
        console.log(error)
        return;
    }
}

// JS that makes AJAX call to upload photo to database
async function uploadPhoto(formData) {

    try {
        
        const response = await fetch('photos.php', { 
            method: 'POST',
            body: formData
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

// JS that deletes photo from database and directory
async function deletePhoto(photoID, path) {

    try {
        
        const response = await fetch('photos.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({endpoint: "deletePhoto", photoID, path})
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

