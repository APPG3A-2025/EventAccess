document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('verify-code-form');

    function showNotification(message) {
        const notification = document.getElementById('notification');
        const messageElement = notification.querySelector('.notification-message');
        messageElement.textContent = message;
        
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);
    }

    async function verifyCode(email, code, newPassword) {
        try {
            const response = await fetch('../../backend/verify-code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, code, newPassword })
            });

            const data = await response.json();
            showNotification(data.message);

            if (response.ok) {
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 3000);
            }
        } catch (error) {
            showNotification('Une erreur est survenue. Veuillez réessayer.');
        }
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const urlParams = new URLSearchParams(window.location.search);
            const email = urlParams.get('email');
            const code = document.getElementById('code').value;
            const newPassword = document.getElementById('new-password').value;

            if (email && code && newPassword) {
                verifyCode(email, code, newPassword);
            } else {
                showNotification('Tous les champs sont obligatoires.');
            }
        });
    }
});
