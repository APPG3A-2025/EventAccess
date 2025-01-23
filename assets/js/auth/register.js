document.addEventListener('DOMContentLoaded', () => {
    // Éléments du DOM
    const progressBar = document.querySelector('.progress');
    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    const form = document.querySelector('.registration-form');
    const passwordInput = document.getElementById('password');
    const strengthBar = document.querySelector('.strength-bar');
    const strengthText = document.querySelector('.strength-text');

    let currentStep = 1;
    const totalSteps = steps.length;

    // Initialisation
    function init() {
        stepContents.forEach(content => {
            content.style.display = 'none';
            content.style.opacity = '0';
        });

        stepContents[0].style.display = 'block';
        stepContents[0].style.opacity = '1';
        updateProgress();
        setupEventListeners();
        setupPasswordStrength();
    }

    // Configuration de l'indicateur de force du mot de passe
    function setupPasswordStrength() {
        if (passwordInput && strengthBar && strengthText) {
            passwordInput.addEventListener('input', updatePasswordStrength);
        }
    }

    // Mise à jour de l'indicateur de force du mot de passe
    function updatePasswordStrength() {
        const password = passwordInput.value;
        let strength = 0;
        let message = '';

        // Critères de force
        if (password.length >= 8) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[a-z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;

        // Mise à jour de la barre de progression
        strengthBar.style.width = `${strength}%`;

        // Mise à jour du message
        if (strength <= 25) {
            message = 'Faible';
            strengthBar.style.backgroundColor = '#ff4444';
        } else if (strength <= 50) {
            message = 'Moyen';
            strengthBar.style.backgroundColor = '#ffbb33';
        } else if (strength <= 75) {
            message = 'Bon';
            strengthBar.style.backgroundColor = '#00C851';
        } else {
            message = 'Excellent';
            strengthBar.style.backgroundColor = '#007E33';
        }

        strengthText.textContent = `Force du mot de passe : ${message}`;
    }

    // Mise à jour de la barre de progression
    function updateProgress() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        progressBar.style.width = `${progress}%`;
        
        steps.forEach((step, idx) => {
            if (idx + 1 < currentStep) {
                step.classList.add('completed', 'active');
            } else if (idx + 1 === currentStep) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });
    }

    // Affichage des notifications
    function showNotification(message, type = 'error') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `<span class="notification-message">${message}</span>`;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Validation des étapes
    function validateStep(step) {
        switch(step) {
            case 1:
                const email = document.getElementById('email').value;
                if (!email) {
                    showNotification('Veuillez entrer une adresse email');
                    return false;
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showNotification('Adresse email invalide');
                    return false;
                }
                return true;

            case 2:
                const required = ['civilite', 'firstname', 'lastname', 'birthdate', 'phone', 'postal'];
                for (const field of required) {
                    const value = document.getElementById(field).value.trim();
                    if (!value) {
                        showNotification('Tous les champs sont obligatoires');
                        return false;
                    }
                }

                // Validation du téléphone
                const phone = document.getElementById('phone').value.replace(/\D/g, '');
                if (phone.length !== 9) {
                    showNotification('Le numéro doit contenir 9 chiffres');
                    return false;
                }
                if (!['1','2','3','4','5','6','7','9'].includes(phone[0])) {
                    showNotification('Numéro de téléphone invalide');
                    return false;
                }

                // Validation du code postal
                const postal = document.getElementById('postal').value;
                if (!/^\d{5}$/.test(postal)) {
                    showNotification('Code postal invalide');
                    return false;
                }

                return true;

            case 3:
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm-password').value;

                if (!password || !confirmPassword) {
                    showNotification('Veuillez remplir tous les champs');
                    return false;
                }

                if (password !== confirmPassword) {
                    showNotification('Les mots de passe ne correspondent pas');
                    return false;
                }

                if (password.length < 8) {
                    showNotification('Le mot de passe doit contenir au moins 8 caractères');
                    return false;
                }

                if (!/[A-Z]/.test(password)) {
                    showNotification('Le mot de passe doit contenir au moins une majuscule');
                    return false;
                }

                if (!/[a-z]/.test(password)) {
                    showNotification('Le mot de passe doit contenir au moins une minuscule');
                    return false;
                }

                if (!/[0-9]/.test(password)) {
                    showNotification('Le mot de passe doit contenir au moins un chiffre');
                    return false;
                }

                return true;

            case 4:
                const terms = document.querySelector('input[type="checkbox"]');
                if (!terms.checked) {
                    showNotification('Veuillez accepter les conditions d\'utilisation');
                    return false;
                }
                return true;
        }
    }

    // Navigation entre les étapes
    function goToStep(direction) {
        if (direction === 'next' && !validateStep(currentStep)) {
            return;
        }

        stepContents[currentStep - 1].style.display = 'none';
        stepContents[currentStep - 1].style.opacity = '0';

        currentStep += direction === 'next' ? 1 : -1;

        stepContents[currentStep - 1].style.display = 'block';
        setTimeout(() => {
            stepContents[currentStep - 1].style.opacity = '1';
        }, 50);

        updateProgress();
    }

    // Configuration des écouteurs d'événements
    function setupEventListeners() {
        nextButtons.forEach(button => {
            button.addEventListener('click', () => goToStep('next'));
        });

        prevButtons.forEach(button => {
            button.addEventListener('click', () => goToStep('prev'));
        });

        // Gestion de la soumission du formulaire
        const submitButton = document.querySelector('button[type="submit"]');
        /*if (submitButton) {
            submitButton.addEventListener('click', async (e) => {
                e.preventDefault();
                if (!validateStep(currentStep)) return;

                try {
                    const formData = {
                        email: document.getElementById('email').value,
                        civilite: document.getElementById('civilite').value,
                        firstname: document.getElementById('firstname').value,
                        lastname: document.getElementById('lastname').value,
                        birthdate: document.getElementById('birthdate').value,
                        phone: document.getElementById('phone').value.replace(/\D/g, ''),
                        postal: document.getElementById('postal').value,
                        password: document.getElementById('password').value
                    };

                    const response = await fetch('../../assets/php/auth/register.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        showNotification('Inscription réussie ! Redirection...', 'success');
                        setTimeout(() => {
                            window.location.href = 'login.html';
                        }, 2000);
                    } else {
                        console.log(data.message);
                        
                        showNotification(data.message || 'Une erreur est survenue');
                    }
                } catch (error) {
                    showNotification('Une erreur est survenue lors de l\'inscription');
                }
            });
        }*/
    }

    // Démarrage
    init();
}); 