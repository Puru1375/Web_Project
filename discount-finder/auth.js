// auth.js - Logic for login.html and register.html

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const loginMessage = document.getElementById('login-message');
    const registerMessage = document.getElementById('register-message');

    // --- Registration Form Handler ---
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            setMessage(registerMessage, '', 'black'); // Clear message

            if (!username || !email || !password || !confirmPassword) {
                setMessage(registerMessage, 'Please fill in all fields.', 'red'); return;
            }
            if (password !== confirmPassword) {
                setMessage(registerMessage, 'Passwords do not match.', 'red'); return;
            }
            if (password.length < 6) {
                 setMessage(registerMessage, 'Password must be at least 6 characters long.', 'red'); return;
            }

            setMessage(registerMessage, 'Processing registration...', 'black');
            try {
                const response = await fetch('api/register_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, email, password })
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const result = await response.json();
                setMessage(registerMessage, result.message, result.success ? 'green' : 'red');
                if (result.success) registerForm.reset(); // Clear form on success
            } catch (error) {
                console.error('Registration fetch error:', error);
                setMessage(registerMessage, 'Server connection error. Please try again.', 'red');
            }
        });
    }

    // --- Login Form Handler ---
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const emailInput = document.getElementById('email'); // Input field can contain email or username
            const passwordInput = document.getElementById('password');
            const identifier = emailInput.value; // Use 'identifier' for clarity
            const password = passwordInput.value;

            setMessage(loginMessage, '', 'black'); // Clear message

            if (!identifier || !password) {
                setMessage(loginMessage, 'Please enter email/username and password.', 'red'); return;
            }

            setMessage(loginMessage, 'Processing login...', 'black');
            try {
                const response = await fetch('api/login_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: identifier, password: password }) // Send identifier as 'email' key
                });
                 if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                 const result = await response.json();

                 if (result.success) {
                     setMessage(loginMessage, `${result.message} Welcome, ${result.username}!`, 'green');
                     // Redirect to home page after a short delay
                     setTimeout(() => { window.location.href = 'index.html'; }, 1500);
                 } else {
                     setMessage(loginMessage, result.message, 'red');
                 }

            } catch (error) {
                 console.error('Login fetch error:', error);
                 setMessage(loginMessage, 'Server connection error. Please try again.', 'red');
            }
        });
    }

    // Helper function to set messages
    function setMessage(element, text, color) {
        if (element) {
            element.textContent = text;
            element.style.color = color;
        }
    }
});