document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('forgot-password-form');

    function showNotification(message) {
        const notification = document.getElementById('notification');
        const messageElement = notification.querySelector('.notification-message');
        messageElement.textContent = message;
        
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);
    }

    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;

            if (!validateEmail(email)) {
                showNotification('Veuillez entrer une adresse email valide');
                return;
            }

            // Simulation d'envoi d'email
            showNotification('Un email de réinitialisation a été envoyé si cette adresse existe');
            form.reset();

            // Redirection après 3 secondes
            setTimeout(() => {
                window.location.href = '../auth/login.html';
            }, 3000);
        });
    }
}); 