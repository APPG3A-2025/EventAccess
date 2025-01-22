document.addEventListener('DOMContentLoaded', () => {
    const progressBar = document.querySelector('.progress');
    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    const form = document.querySelector('.registration-form');
    
    let currentStep = 1;
    const totalSteps = steps.length;

    // Fonction de validation d'email
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Mettre à jour la barre de progression
    function updateProgress() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        progressBar.style.width = `${progress}%`;
        
        steps.forEach((step, idx) => {
            if (idx + 1 < currentStep) {
                step.classList.add('completed');
                step.classList.add('active');
            } else if (idx + 1 === currentStep) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active');
                step.classList.remove('completed');
            }
        });
    }

    // Passer à l'étape suivante
    function nextStep() {
        stepContents[currentStep - 1].classList.remove('active');
        currentStep++;
        stepContents[currentStep - 1].classList.add('active');
        updateProgress();
    }

    // Revenir à l'étape précédente
    function prevStep() {
        stepContents[currentStep - 1].classList.remove('active');
        currentStep--;
        stepContents[currentStep - 1].classList.add('active');
        updateProgress();
    }

    // Fonction pour afficher les notifications
    function showNotification(message, type = 'error') {
        const notification = document.getElementById('notification');
        const messageElement = notification.querySelector('.notification-message');
        messageElement.textContent = message;
        notification.className = `notification ${type}`;
        setTimeout(() => {
            notification.className = 'notification';
        }, 5000);
    }

    // Event listeners pour les boutons suivant/précédent
    nextButtons.forEach(button => {
        button.addEventListener('click', nextStep);
    });

    prevButtons.forEach(button => {
        button.addEventListener('click', prevStep);
    });

    // Gestion de la soumission du formulaire
    const submitBtn = document.querySelector('.submit-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const form = document.querySelector('.registration-form');
            const cguCheckbox = document.querySelector('.step-content[data-step="4"] input[type="checkbox"]');
            
            if (!cguCheckbox.checked) {
                showNotification('Veuillez accepter les conditions d\'utilisation');
                return;
            }

            try {
                const formData = new FormData(form);
                
                // Nettoyer le numéro de téléphone
                const phone = formData.get('telephone').replace(/\s/g, '');
                formData.set('telephone', phone);

                // Afficher les données envoyées (pour debug)
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                const response = await fetch('../../assets/php/register.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                console.log('Réponse du serveur:', data);

                if (data.success) {
                    // Stockage du token
                    localStorage.setItem('userToken', data.token);
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showNotification(data.message || 'Une erreur est survenue');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Une erreur est survenue lors de l\'inscription');
            }
        });
    }

    // Formatage du numéro de téléphone
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 9) value = value.slice(0, 9);
            
            let formattedValue = '';
            if (value.length > 0) {
                formattedValue = value[0];
                for (let i = 1; i < value.length; i += 2) {
                    formattedValue += ' ' + value.slice(i, i + 2);
                }
            }
            
            e.target.value = formattedValue;
        });
    }

    // Initialisation
    updateProgress();
}); 