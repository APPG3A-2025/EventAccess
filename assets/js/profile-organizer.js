document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('userToken');
    
    // Si pas de token, redirection vers login
    if (!token) {
        window.location.href = '../pages/auth/login.html';
        return;
    }

    try {
        // Vérifier le token et le type de compte
        const response = await fetch('../assets/php/check_token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();
        
        if (!data.success || !data.user) {
            window.location.href = '../pages/auth/login.html';
            return;
        }

        // Vérifier si c'est bien un organisateur
        if (data.user.type_compte !== 'organisateur') {
            window.location.href = '../pages/profile-participant.html';
            return;
        }

        // Afficher les données du profil
        updateProfileInfo(data.user);
        // Charger les événements de l'organisateur
        loadOrganizerEvents();

    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors du chargement du profil', 'error');
    }
});

// Fonction pour mettre à jour les informations du profil
function updateProfileInfo(user) {
    const fields = {
        'civilite': user.civilite,
        'firstname': user.prenom,
        'lastname': user.nom,
        'email': user.email,
        'phone': user.telephone,
        'postal': user.code_postal
    };

    Object.entries(fields).forEach(([field, value]) => {
        const element = document.querySelector(`[data-field="${field}"]`);
        if (element) {
            element.textContent = value || 'Non renseigné';
            if (!value) element.classList.add('not-set');
        }
    });
} 