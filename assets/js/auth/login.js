document.addEventListener('DOMContentLoaded', async () => {
    console.log('Script login.js chargé');

    // Vérifier si un token existe déjà
    const token = localStorage.getItem('userToken');
    console.log('Token dans localStorage:', token);
    
    if (token) {
        try {
            // Vérifier si le token est valide
            const response = await fetch('../../assets/php/check_token.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            const data = await response.json();
            console.log('Réponse de check_token.php:', data);

            // Si le token est valide, rediriger vers le profil
            if (data.success && data.user) {
                window.location.href = '../profile.html';
                return;
            }
            // Si le token n'est pas valide, on continue avec le formulaire de connexion
            // sans supprimer le token
        } catch (error) {
            console.error('Erreur de vérification du token:', error);
            // En cas d'erreur, on continue avec le formulaire de connexion
        }
    }

    // Si on arrive ici, soit il n'y a pas de token, soit il est invalide
    // On continue avec le formulaire de connexion normal
    const form = document.querySelector('.auth-form');
    console.log('Formulaire trouvé:', form);
    
    // Fonction pour afficher les notifications
    function showNotification(message, type = 'error') {
        console.log('Notification:', message, type);
        
        // Supprimer les anciennes notifications
        const oldNotifications = document.querySelectorAll('.notification');
        oldNotifications.forEach(notif => notif.remove());

        // Créer la nouvelle notification
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        `;
        
        document.body.appendChild(notification);
        notification.classList.add('active');

        // Ajouter le bouton de fermeture
        const closeBtn = notification.querySelector('.notification-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                notification.classList.remove('active');
                setTimeout(() => notification.remove(), 300);
            });
        }
        
        // Auto-fermeture après 3 secondes seulement pour les succès
        if (type === 'success') {
            setTimeout(() => {
                notification.classList.remove('active');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    }

    // Gestion de la soumission du formulaire
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Formulaire soumis');
            
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            if (!emailInput || !passwordInput) {
                showNotification('Erreur : formulaire incomplet');
                return;
            }

            const email = emailInput.value.trim();
            const password = passwordInput.value;
            
            console.log('Email saisi:', email);
            console.log('Mot de passe saisi: [MASQUÉ]');

            if (!email || !password) {
                showNotification('Veuillez remplir tous les champs');
                return;
            }
            
            try {
                console.log('Envoi de la requête de connexion...');
                
                // Création de l'objet FormData
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);

                // Envoi de la requête
                const response = await fetch('../../assets/php/login.php', {
                    method: 'POST',
                    body: formData
                });

                console.log('Status de la réponse:', response.status);
                
                // Récupérer le texte brut de la réponse d'abord
                const responseText = await response.text();
                console.log('Réponse brute du serveur:', responseText);

                // Essayer de parser le JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                    console.log('Données parsées:', data);
                } catch (jsonError) {
                    console.error('Erreur de parsing JSON:', jsonError);
                    console.log('Contenu qui a causé l\'erreur:', responseText);
                    throw new Error('Réponse invalide du serveur');
                }

                if (data.success) {
                    console.log('Connexion réussie');
                    localStorage.setItem('userToken', data.token);
                    showNotification('Connexion réussie !', 'success');
                    
                    // Redirection selon le type de compte
                    if (data.user.type_compte === 'organisateur') {
                        window.location.href = '../profile-organizer.html';
                    } else {
                        window.location.href = '../profile-participant.html';
                    }
                } else {
                    console.log('Échec de la connexion:', data.message);
                    showNotification(data.message);
                    // Vider le champ mot de passe
                    passwordInput.value = '';
                    passwordInput.focus();
                }
            } catch (error) {
                console.error('Erreur lors de la connexion:', error);
                showNotification('Une erreur est survenue lors de la connexion');
            }
        });

        // Log des événements du formulaire
        form.addEventListener('click', () => console.log('Clic sur le formulaire'));
        document.querySelector('.submit-btn').addEventListener('click', () => console.log('Clic sur le bouton submit'));
    } else {
        console.error('Formulaire de connexion non trouvé');
    }

    // ... reste du code (gestion du bouton voir mot de passe, etc.)
}); 