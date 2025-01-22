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

        // Vérifier si c'est bien un participant
        if (data.user.type_compte === 'organisateur') {
            window.location.href = '../pages/profile-organizer.html';
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