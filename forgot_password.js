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

function validateData(formData) {

    // variables de traduccion, ya que el formulario es en ingles y la base de datos també
    const fieldTranslations = {
        email: "Correu electrònic",
        password: "Contrasenya",
        passwordConfirm: "Confirma la contrasenya"
    };

    let errores = [];
    formData.forEach((value, key) => {
        // Convertir value a string antes de usar trim
        if (!String(value).trim()) {
            const fieldName = fieldTranslations[key] || key; // Traduir el camp si existeix al mapa
            errores.push(`${fieldName} és obligatori.`);
        }
    });

    return errores;
}

function validatePasswords(password, passwordConfirm) {
    let errores = [];
    console.log('entra a la funcion de validar la contraseña ',password, passwordConfirm);
    if (password !== passwordConfirm) {
        errores.push("Les contrassenyes no coincideixen.");
       
    } if (password.length < 8) {
        errores.push("La contrasenya ha de tenir almenys 8 caracters.");
    } if (password.length > 20) {
        errores.push("La contrasenya ha de tenir menys de 20 caracters.");
    }

    // comprobar si hay mayusculas, minusculas y numeros
     // Comprobamos si la contraseña contiene una mayúscula, una minúscula y un número
     let tieneMayuscula = /[A-Z]/.test(password);
     let tieneMinuscula = /[a-z]/.test(password);
     let tieneNumero = /[0-9]/.test(password);
 
     // Si falta alguno de estos requisitos, añadir un error
     if (!tieneMayuscula) {
         errores.push("La contrasenya ha de tenir almenys una lletra majúscula.");
     }
     if (!tieneMinuscula) {
         errores.push("La contrasenya ha de tenir almenys una lletra minúscula.");
     }
     if (!tieneNumero) {
         errores.push("La contrasenya ha de tenir almenys un número.");
     }


    console.log('en la funcion hay: ',errores.length, errores); 
    return errores;
}
// funcion de fetch para enviar el correo
async function sendForgotPasswordForm(event) {

    event.preventDefault(); // Evita el recargado de la página
    const formElement1 = event.target; // Formulario que disparó el evento


    const dataRecoverForm1 = new FormData(formElement1);
    console.log(dataRecoverForm1);

    const areErrors = validateData(dataRecoverForm1); // Valida los datos y devuelve errores

    console.log('detecta que Hay algun error, hay ' + areErrors.length + ' errores.');
    console.log(areErrors);
    // Selecciona el primer elemento con la clase "error-message"
    const errorDiv = document.getElementsByClassName("error-message")[0];


    errorDiv.innerHTML = ''; // Limpiar errores anteriores

    if (areErrors && areErrors.length > 0) {
        console.log('detecta que Hay algun error, hay ' + areErrors.length + ' errores.');
        // Si hay errores, agregarlos al contenedor
        areErrors.forEach(error => {
            const pElement = document.createElement("p");
            pElement.textContent = error;
            errorDiv.appendChild(pElement);
        });

        // Desplazar la página hacia el div de errores
        errorDiv.scrollIntoView({ behavior: "smooth", block: "start" });

    } else {

        console.log('No hay errores.');

        try {

            dataRecoverForm1.append('endpoint', 'forgotPassword');
            const response = await fetch('forgot_password.php', {
                method: 'POST',

                body: dataRecoverForm1, // Deja que el navegador establezca automáticamente el Content-Type
            });


            const forgotPassword = await response.json();
            if (response.ok) {
                if (forgotPassword.success) {
                showAlerts("info", "correo enviat, siusplau, valida el teu correo per poder cambiar la contrasenya.");
                console.log('respuesta todo ok ', forgotPassword.message, forgotPassword.email);
                 setTimeout(() => {

                    window.location.href = "forgot_password.php";
                    
                 }, 3000);

                }else {
                    console.error('ha ocurrido un error al procesar o verificar el correo, porfavor, compruebe los datos y vuelvelo a probar',forgotPassword.message);
                    showAlerts('error', 'ha ocurrido un error al procesar o verificar el correo, porfavor, compruebe los datos y vuelvelo a probar');
                }
            } else {
                console.error('Error en la respuesta del servidor al enviar el correo de cambio de contraseña',forgotPassword.message);

            }

        } catch (error) {
            console.error('Error al comunicarse con el servidor en la parte del email:', error);


        }


    }

}

// una vez enviado el correo y validado, activamos esta funcion al meter la contraseña
async function sendChangePasswordForm(event) {
console.log("ENTRA");
    event.preventDefault(); // Evita el recargado de la página
    const formElement2 = event.target; // Formulario que disparó el evento


    const dataRecoverForm2 = new FormData(formElement2);
    console.log(dataRecoverForm2);


    const areErrors = validateData(dataRecoverForm2); // Valida los datos y devuelve errores
    const passwordError = validatePasswords(dataRecoverForm2.get('password'), dataRecoverForm2.get('confirm_password'));

console.log('hay errores de password: ', passwordError.length, passwordError);
    console.log('detecta que Hay algun error, hay ' + areErrors.length + ' errores.');
    console.log(areErrors);
    // Selecciona el primer elemento con la clase "error-message"
    const errorDiv = document.getElementsByClassName("error-message")[0];
   
    errorDiv.innerHTML = ''; // Limpiar errores anteriores

    if (areErrors && areErrors.length > 0 ) {
        
        console.log('entra en el error del formulario vacio');
        console.log('detecta que Hay algun error, hay ' + areErrors.length + ' errores.');
        // Si hay errores, agregarlos al contenedor
        areErrors.forEach(error => {
            const pElement = document.createElement("p");
            pElement.textContent = error;
            errorDiv.appendChild(pElement);
        });
      

        // Desplazar la página hacia el div de errores
        errorDiv.scrollIntoView({ behavior: "smooth", block: "start" });

    }else if (passwordError && passwordError.length > 0 ) {
        console.log('entra en el error del password');
        console.log('detecta que Hay algun error, hay ' + passwordError.length + ' errores.');
        // Si hay errores, agregarlos al contenedor
        passwordError.forEach(error => {
            const pElement = document.createElement("p");
            pElement.textContent = error;
            errorDiv.appendChild(pElement);
        });
      

        // Desplazar la página hacia el div de errores
        errorDiv.scrollIntoView({ behavior: "smooth", block: "start" });
    
    }else {

        console.log('No hay errores.');


        try {
            dataRecoverForm2.append('endpoint', 'changePassword');
            const response = await fetch('forgot_password.php', {
                method: 'POST',
                body: dataRecoverForm2, // Deja que el navegador establezca automáticamente el Content-Type
            });

            // Lee el cuerpo de la respuesta solo una vez
            const forgot = await response.json();  // Lee el JSON de la respuesta

            if (response.ok) {
                if (!forgot.success) {
                    console.error('ha ocurrido un error al procesar o verificar el correo, porfavor, compruebe los datos y vuelvelo a probar',forgot.message,forgot.email);
                    showAlerts('error', forgot.message);
                }else{
                    
              
                showAlerts("info", "Contrasenya canviada correctament.");
                console.log('Respuesta todo ok:', forgot.message, forgot.email);
                setTimeout(() => {

                    window.location.href = "login.php";
                }, 3000);
            }
            } else {
                console.error('Error en la respuesta del servidor al cambiar la contraseña', forgot.email);
            }

        } catch (error) {
            console.error('Error al comunicarse con el servidor al cambiar la contraseña:', error);
        }

    }

}