import { validatePassword, calculatePasswordStrength, validatePhoneNumber } from './utils/validation.js';
import { formatPhoneNumber } from './utils/formatters.js';
import { showNotification } from './utils/notification.js';

document.addEventListener('DOMContentLoaded', () => {
    // Gestion des modales
    const modals = document.querySelectorAll('.modal');
    const editButtons = document.querySelectorAll('[data-modal]');
    const cancelButtons = document.querySelectorAll('.cancel-button');

    // Ouvrir la modale
    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
            }
        });
    });

    // Fermer la modale
    cancelButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Fermer la modale en cliquant en dehors
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Ajouter cette fonction pour pré-remplir l'email
    function fillEmailForm() {
        const currentEmail = document.querySelector('.current-email').textContent;
        document.getElementById('current-email').value = currentEmail;
    }

    // Ajouter l'événement pour ouvrir la modale email et pré-remplir le formulaire
    document.querySelector('[data-modal="edit-email"]').addEventListener('click', () => {
        fillEmailForm();
        document.getElementById('edit-email').classList.add('active');
    });

    // Validation du changement d'email
    const emailForm = document.getElementById('email-form');
    if (emailForm) {
        emailForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const currentEmail = document.getElementById('current-email').value;
            const newEmail = document.getElementById('new-email').value;
            const confirmEmail = document.getElementById('confirm-email').value;

            if (newEmail !== confirmEmail) {
                showNotification('Les adresses email ne correspondent pas');
                return;
            }

            // Simulation de la mise à jour
            document.querySelector('.current-email').textContent = newEmail;
            showNotification('Email mis à jour avec succès', 'success');
            emailForm.reset();
            document.getElementById('edit-email').classList.remove('active');
        });
    }

    // Validation du changement de mot de passe
    const passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            const validation = validatePassword(newPassword);
            if (!validation.isValid) {
                validation.errors.forEach(error => {
                    showNotification(error);
                });
                return;
            }

            if (newPassword !== confirmPassword) {
                showNotification('Les mots de passe ne correspondent pas');
                return;
            }

            showNotification('Mot de passe mis à jour avec succès', 'success');
            passwordForm.reset();
            document.getElementById('edit-password').classList.remove('active');
        });
    }

    // Validation de la suppression du compte
    const deleteForm = document.getElementById('delete-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const password = document.getElementById('delete-password').value;

            if (!password) {
                showNotification('Veuillez entrer votre mot de passe');
                return;
            }

            // Simulation de la suppression
            showNotification('Compte supprimé avec succès');
            setTimeout(() => {
                window.location.href = '../index.html';
            }, 2000);
        });
    }

    // Fonction pour pré-remplir le formulaire de modification
    function fillEditForm() {
        // Récupérer les données actuelles depuis les éléments affichés
        const currentData = {
            civilite: document.querySelector('.info-item p[data-field="civilite"]').textContent,
            firstname: document.querySelector('.info-item p[data-field="firstname"]').textContent,
            lastname: document.querySelector('.info-item p[data-field="lastname"]').textContent,
            birthdate: document.querySelector('.info-item p[data-field="birthdate"]').getAttribute('data-raw-date'),
            phone: document.querySelector('.info-item p[data-field="phone"]').textContent.replace('+33 ', ''),
            postal: document.querySelector('.info-item p[data-field="postal"]').textContent
        };

        console.log('Données récupérées:', currentData); // Pour le débogage

        // Pré-remplir le formulaire avec les données actuelles
        document.getElementById('edit-civilite').value = currentData.civilite === 'M.' ? 'm' : 
                                                        currentData.civilite === 'Mme.' ? 'mme' : 'mx';
        document.getElementById('edit-firstname').value = currentData.firstname;
        document.getElementById('edit-lastname').value = currentData.lastname;
        document.getElementById('edit-birthdate').value = currentData.birthdate;
        document.getElementById('edit-phone').value = currentData.phone;
        document.getElementById('edit-postal').value = currentData.postal;
    }

    // Gérer le formulaire de modification des informations
    const infoForm = document.getElementById('info-form');
    if (infoForm) {
        infoForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Vérification du numéro de téléphone
            const phoneInput = document.getElementById('edit-phone');
            const cleanPhone = phoneInput.value.replace(/\D/g, '');
            if (cleanPhone.length !== 9) {
                showNotification('Le numéro doit contenir exactement 9 chiffres');
                return;
            }

            // Simuler la sauvegarde des données
            const newData = {
                civilite: document.getElementById('edit-civilite').value,
                firstname: document.getElementById('edit-firstname').value,
                lastname: document.getElementById('edit-lastname').value,
                birthdate: document.getElementById('edit-birthdate').value,
                phone: document.getElementById('edit-phone').value,
                postal: document.getElementById('edit-postal').value
            };

            // Mettre à jour l'affichage
            const civiliteDisplay = newData.civilite === 'm' ? 'M.' : 
                                  newData.civilite === 'mme' ? 'Mme.' : 'Mx.';
            document.querySelector('[data-field="civilite"]').textContent = civiliteDisplay;
            document.querySelector('[data-field="firstname"]').textContent = newData.firstname;
            document.querySelector('[data-field="lastname"]').textContent = newData.lastname;
            document.querySelector('[data-field="birthdate"]').textContent = new Date(newData.birthdate)
                .toLocaleDateString('fr-FR');
            document.querySelector('[data-field="phone"]').textContent = newData.phone;
            document.querySelector('[data-field="postal"]').textContent = newData.postal;

            // Fermer la modale et afficher la notification
            document.getElementById('edit-info').classList.remove('active');
            showNotification('Informations mises à jour avec succès', 'success');
        });
    }

    // Ajouter le formatage du numéro de téléphone pour le champ de modification
    const editPhoneInput = document.getElementById('edit-phone');
    if (editPhoneInput) {
        formatPhoneNumber(editPhoneInput);
    }

    // Ajouter l'événement pour ouvrir la modale et pré-remplir le formulaire
    document.querySelector('[data-modal="edit-info"]').addEventListener('click', () => {
        fillEditForm();
        document.getElementById('edit-info').classList.add('active');
    });

    // Gestion de la force du mot de passe dans la modale
    const newPasswordInput = document.getElementById('new-password');
    const strengthBar = document.querySelector('.strength-bar');
    const strengthText = document.querySelector('.strength-text');

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', (e) => {
            const password = e.target.value;
            const validation = validatePassword(password);
            
            strengthBar.className = 'strength-bar';
            if (password) {
                strengthBar.classList.add(validation.strength);
                
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
}); 