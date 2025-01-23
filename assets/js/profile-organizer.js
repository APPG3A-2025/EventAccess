document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('userToken');
    
    if (!token) {
        showNotification('Vous devez être connecté pour accéder à cette page', 'error');
        return;
    }

    try {
        // Vérifier le token
        const response = await fetch('../assets/php/check_token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();
        
        if (!data.success || !data.user) {
            showNotification('Session invalide', 'error');
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