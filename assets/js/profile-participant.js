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
        // Charger les événements du participant
        loadParticipantEvents();

    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors du chargement du profil', 'error');
    }
}); 