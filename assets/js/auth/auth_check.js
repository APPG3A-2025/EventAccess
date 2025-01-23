document.addEventListener('DOMContentLoaded', function() {
    const protectedLinks = document.querySelectorAll('a[href*="create.php"]');
    
    protectedLinks.forEach(link => {
        link.addEventListener('click', async function(e) {
            e.preventDefault();
            
            try {
                const response = await fetch('/assets/php/auth/check_auth.php');
                const data = await response.json();
                
                if (!data.authenticated) {
                    // Sauvegarder la page de destination
                    sessionStorage.setItem('redirectAfterLogin', this.href);
                    window.location.href = data.redirect;
                } else {
                    window.location.href = this.href;
                }
            } catch (error) {
                console.error('Erreur de vérification:', error);
            }
        });
    });
}); 