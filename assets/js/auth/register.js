document.addEventListener('DOMContentLoaded', () => {
    const progressBar = document.querySelector('.progress');
    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    const form = document.querySelector('.registration-form');
    
    let currentStep = 1;
    const totalSteps = steps.length;

    // Ajoutez cette fonction de validation d'email
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
        if (validateCurrentStep()) {
            stepContents[currentStep - 1].classList.remove('active');
            currentStep++;
            stepContents[currentStep - 1].classList.add('active');
            updateProgress();
        }
    }

    // Revenir à l'étape précédente
    function prevStep() {
        stepContents[currentStep - 1].classList.remove('active');
        currentStep--;
        stepContents[currentStep - 1].classList.add('active');
        updateProgress();
    }

    // Ajoutez cette fonction pour gérer les notifications
    function showNotification(message) {
        const notification = document.getElementById('notification');
        const messageElement = notification.querySelector('.notification-message');
        messageElement.textContent = message;
        
        notification.classList.add('show');
        
        // Cache la notification après 5 secondes
        setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);
    }

    // Valider l'tape courante
    function validateCurrentStep() {
        const currentStepContent = stepContents[currentStep - 1];
        const inputs = currentStepContent.querySelectorAll('input[required]');
        let isValid = true;

        // Pour l'étape 1 (Email et Type de compte)
        if (currentStep === 1) {
            const email = document.getElementById('email');
            const accountType = currentStepContent.querySelector('input[name="account-type"]:checked');
            
            if (!email.value || !validateEmail(email.value)) {
                isValid = false;
                email.classList.add('error');
                showNotification('Veuillez entrer une adresse email valide');
                return false;
            } else {
                email.classList.remove('error');
            }

            if (!accountType) {
                isValid = false;
                showNotification('Veuillez sélectionner un type de compte');
                return false;
            }
        }

        // Pour l'étape 4 (Confirmation), vérifier les CGU
        else if (currentStep === 4) {
            const cguCheckbox = currentStepContent.querySelector('input[type="checkbox"]');
            const termsLabel = currentStepContent.querySelector('.terms');
            
            if (!cguCheckbox.checked) {
                isValid = false;
                cguCheckbox.classList.add('error');
                termsLabel.classList.add('error');
                showNotification('Veuillez accepter les conditions d\'utilisation');
                return false;
            } else {
                cguCheckbox.classList.remove('error');
                termsLabel.classList.remove('error');
            }
        }
        // Pour l'étape 3 (Sécurité)
        else if (currentStep === 3) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const validation = validatePassword(password);

            if (!validation.isValid) {
                isValid = false;
                validation.errors.forEach(error => {
                    showNotification(error);
                });
                return false;
            }

            if (password !== confirmPassword) {
                isValid = false;
                showNotification('Les mots de passe ne correspondent pas');
                return false;
            }
        }
        // Pour l'étape 2 (Profil)
        else if (currentStep === 2) {
            const firstname = document.getElementById('firstname');
            const lastname = document.getElementById('lastname');
            const birthdate = document.getElementById('birthdate');
            const phone = document.getElementById('phone');
            const postal = document.getElementById('postal');

            if (!firstname.value) {
                isValid = false;
                firstname.classList.add('error');
                showNotification('Veuillez entrer votre prénom');
                return false;
            }

            if (!lastname.value) {
                isValid = false;
                lastname.classList.add('error');
                showNotification('Veuillez entrer votre nom');
                return false;
            }

            if (!birthdate.value) {
                isValid = false;
                birthdate.classList.add('error');
                showNotification('Veuillez entrer votre date de naissance');
                return false;
            }

            const phoneValidation = validatePhoneNumber(phone.value);
            if (!phoneValidation.isValid) {
                isValid = false;
                phone.classList.add('error');
                showNotification(phoneValidation.error);
                return false;
            }

            if (!postal.value || !/^[0-9]{5}$/.test(postal.value)) {
                isValid = false;
                postal.classList.add('error');
                showNotification('Veuillez entrer un code postal valide (5 chiffres)');
                return false;
            }

            // Si tout est valide, enlever les classes d'erreur
            [firstname, lastname, birthdate, phone, postal].forEach(input => {
                input.classList.remove('error');
            });
        }

        return isValid;
    }

    // Event listeners
    nextButtons.forEach(button => {
        button.addEventListener('click', nextStep);
    });

    prevButtons.forEach(button => {
        button.addEventListener('click', prevStep);
    });

    // Soumission du formulaire
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (validateCurrentStep()) {
            // Traitement de l'inscription
            console.log('Inscription réussie !');
            // Redirection ou autre traitement
        }
    });

    // Initialisation
    updateProgress();

    // Ajoutez ces fonctions pour la validation du mot de passe
    function validatePassword(password) {
        const minLength = password.length >= 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);

        const errors = [];
        if (!minLength) errors.push('Le mot de passe doit contenir au moins 8 caractères');
        if (!hasUpperCase) errors.push('Le mot de passe doit contenir au moins une majuscule');
        if (!hasLowerCase) errors.push('Le mot de passe doit contenir au moins une minuscule');
        if (!hasNumber) errors.push('Le mot de passe doit contenir au moins un chiffre');

        return {
            isValid: minLength && hasUpperCase && hasLowerCase && hasNumber,
            strength: calculatePasswordStrength(password),
            errors: errors
        };
    }

    function calculatePasswordStrength(password) {
        let score = 0;

        // Longueur minimale
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (password.length >= 16) score++;

        // Caractères spéciaux
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        // Retourne un niveau de force basé sur le score
        if (score < 3) return 'weak';
        if (score < 4) return 'medium';
        if (score < 6) return 'strong';
        return 'very-strong';
    }

    function getPasswordErrors(password) {
        const errors = [];
        
        if (password.length < 8) errors.push('Au moins 8 caractères');
        if (password.length > 26) errors.push('Maximum 26 caractères');
        if (!/[A-Z]/.test(password)) errors.push('Au moins une majuscule');
        if (!/[a-z]/.test(password)) errors.push('Au moins une minuscule');
        if (!/[0-9]/.test(password)) errors.push('Au moins un chiffre');

        return errors;
    }

    // Ajoutez cet écouteur d'événements pour le champ mot de passe
    const passwordInput = document.getElementById('password');
    const strengthBar = document.querySelector('.strength-bar');
    const strengthText = document.querySelector('.strength-text');

    if (passwordInput) {
        passwordInput.addEventListener('input', (e) => {
            const password = e.target.value;
            const validation = validatePassword(password);
            
            // Mise à jour de la barre de force
            strengthBar.className = 'strength-bar';
            if (password) {
                strengthBar.classList.add(validation.strength);
                
                // Mise à jour du texte
                switch(validation.strength) {
                    case 'weak':
                        strengthText.textContent = 'Faible';
                        strengthText.style.color = '#ff4444';
                        break;
                    case 'medium':
                        strengthText.textContent = 'Moyen';
                        strengthText.style.color = '#ffbb33';
                        break;
                    case 'strong':
                        strengthText.textContent = 'Fort';
                        strengthText.style.color = '#00C851';
                        break;
                    case 'very-strong':
                        strengthText.textContent = 'Très fort';
                        strengthText.style.color = '#007E33';
                        break;
                }
            } else {
                strengthText.textContent = 'Force du mot de passe';
                strengthText.style.color = 'var(--gray-medium)';
            }
        });
    }

    // Modifiez la fonction formatPhoneNumber
    function formatPhoneNumber(input) {
        input.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, ''); // Garde uniquement les chiffres
            if (value.length > 9) value = value.slice(0, 9); // Limite à 9 chiffres

            // Format X XX XX XX XX
            let formattedValue = '';
            if (value.length > 0) {
                // Premier chiffre
                formattedValue = value[0];
                
                // Reste des chiffres par paires
                for (let i = 1; i < value.length; i += 2) {
                    formattedValue += ' ' + value.slice(i, i + 2);
                }
            }
            
            e.target.value = formattedValue;
        });
    }

    // Initialiser le formatage du numéro de téléphone
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        formatPhoneNumber(phoneInput);
    }

    // Modifiez la fonction validatePhoneNumber
    function validatePhoneNumber(phone) {
        // Nettoie le numéro de tout sauf les chiffres
        const cleanPhone = phone.replace(/\D/g, '');
        
        if (cleanPhone.length !== 9) {
            return {
                isValid: false,
                error: 'Le numéro doit contenir 9 chiffres'
            };
        }

        // Vérifie que le premier chiffre est valide (1-9)
        if (!['1','2','3','4','5','6','7','9'].includes(cleanPhone[0])) {
            return {
                isValid: false,
                error: 'Numéro invalide'
            };
        }

        return {
            isValid: true,
            error: null
        };
    }

    // Gestion du bouton "Créer mon compte"
    const submitBtn = document.querySelector('.submit-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const cguCheckbox = document.querySelector('.step-content[data-step="4"] input[type="checkbox"]');
            const termsLabel = document.querySelector('.step-content[data-step="4"] .terms');
            
            if (!cguCheckbox.checked) {
                cguCheckbox.classList.add('error');
                termsLabel.classList.add('error');
                showNotification('Veuillez accepter les conditions d\'utilisation');
                return false;
            } else {
                cguCheckbox.classList.remove('error');
                termsLabel.classList.remove('error');
                // Continuer avec la soumission du formulaire
                form.submit();
            }
        });
    }
}); 