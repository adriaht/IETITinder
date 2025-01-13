document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailGroup = document.getElementById('emailGroup');
    const passwordGroup = document.getElementById('passwordGroup');
    const errorMessage = document.getElementById('errorMessage');

    // Visibilidad de la contraseña
    //togglePassword.addEventListener('click', () => {
    //    const type = passwordInput.type === 'password' ? 'text' : 'password';
    //   passwordInput.type = type;
    //    togglePassword.textContent = type === 'password' ? '👁️' : '🔒';
    //});

    // Resetear errores cuando se empieza a escribir
    emailInput.addEventListener('input', () => {
        emailGroup.classList.remove('error');
        errorMessage.textContent = '';
    });

    passwordInput.addEventListener('input', () => {
        passwordGroup.classList.remove('error');
        errorMessage.textContent = '';
    });

    // Gestionar el envio del formulario
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Resetear errores
        emailGroup.classList.remove('error');
        passwordGroup.classList.remove('error');
        errorMessage.textContent = '';
        
        try {
            const response = await fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: emailInput.value,
                    password: passwordInput.value
                })
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = 'discover.php';
            } else {
                // Gestionar errores de usuario y contraseña
                if (data.message === 'Usuari i contrasenya incorrectes') {
                    emailGroup.classList.add('error');
                    passwordGroup.classList.add('error');
                    errorMessage.textContent = 'Usuari i contrasenya incorrectes';
                } else if (data.message === 'Contrasenya incorrecta') {
                    passwordGroup.classList.add('error');
                    errorMessage.textContent = 'Contrasenya incorrecta';
                } else {
                    emailGroup.classList.add('error');
                    passwordGroup.classList.add('error');
                    errorMessage.textContent = data.message || 'Usuari i contrasenya incorrectes';
                }
            }
        } catch (error) {
            errorMessage.textContent = 'Error de connexió amb el servidor';
        }
    });
});