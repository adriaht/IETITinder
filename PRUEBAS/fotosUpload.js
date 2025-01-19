
document.addEventListener("DOMContentLoaded", async () => {

    const availableDiv = Array.from(document.getElementsByClassName("available"));
    const inputs = Array.from(document.getElementsByTagName("input"));+

    availableDiv.map(div => {
        div.addEventListener("click", () => {
            div.children[0].click();
        })
    })

    inputs.map(input => {

        input.addEventListener("change", function () {

            const file = input.files[0];
            console.log(file);

            if (file) {

                const validImageTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
                const fileType = file.type;

                if (!validImageTypes.includes(fileType)) {

                    // MENSAJE DE ERROR
                    alert("Please select a valid image (JPG, JPEG, PNG, or WEBP).");
                    return; 

                }

                let formData = new FormData();
                formData.append("image", file);
                formData.append("endpoint", "pollaGorda");
                console.log(formData);

                uploadPhoto(formData);

            }
            

        });

    })

});

// JS that makes AJAX call to insert user interaction in BBDD
async function uploadPhoto(formData) {

    try {
        
        const response = await fetch('upload.php', { 
            method: 'POST',
            body: formData
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

