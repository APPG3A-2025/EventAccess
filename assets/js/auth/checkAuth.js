async function checkAuthentication() {
    const token = localStorage.getItem('userToken');
    console.log('Token récupéré:', token);
    
    // Si pas de token du tout, redirection vers login
    if (!token) {
        window.location.href = '../../app/pages/auth/login.html';
        return false;
    }

    try {
        const response = await fetch('../../app/assets/php/check_token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();
        console.log('Réponse de check_token.php:', data);
        // Si le token est valide, on retourne simplement les données utilisateur
        if (data.success && data.user) {
            return data.user;
        }
        
        // Si le token est invalide, redirection vers login
        window.location.href = '../../app/pages/auth/login.html';
        return false;

    } catch (error) {
        console.error('Erreur de vérification du token:', error);
        // En cas d'erreur de connexion au serveur, on ne redirige pas
        // On laisse l'utilisateur sur la page actuelle
        return false;
    }
} 