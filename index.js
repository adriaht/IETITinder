document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const submitButton = document.getElementById('submitButton');
    const errorMessage = document.getElementById('errorMessage');

    // Visibilidad de la contraseña
    //togglePassword.addEventListener('click', () => {
    //    const type = passwordInput.type === 'password' ? 'text' : 'password';
    //   passwordInput.type = type;
    //    togglePassword.textContent = type === 'password' ? '👁️' : '🔒';
    //});

    // Gestionar el envio del formulario
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
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
                errorMessage.textContent = data.message || 'Error d\'inici de sessió';
            }
        } catch (error) {
            errorMessage.textContent = 'Error de connexió amb el servidor';
        }
    });
});
