function formatDate(dateString) {
    const options = { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('fr-FR', options).replace(',', ' à');
}

document.addEventListener('DOMContentLoaded', function() {
    // Charger les données initiales
    loadRecommendedEvents();
    loadTodayEvents();

    // Gestion de la navigation
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionName = item.dataset.section;
            
            // Mise à jour des classes active
            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            
            // Cacher toutes les sections
            document.querySelectorAll('main > div').forEach(section => {
                section.style.display = 'none';
            });
            
            // Afficher la section appropriée
            document.getElementById(`${sectionName}-section`).style.display = 'block';
            
            // Charger les données appropriées
            if (sectionName === 'dashboard') {
                loadRecommendedEvents();
                loadTodayEvents();
            } else if (sectionName === 'my-events') {
                loadMyEvents();
            } else if (sectionName === 'search') {
                initializeSearch();
            }
        });
    });

    // Gestionnaire de recherche
    const searchButton = document.getElementById('search-button');
    if (searchButton) {
        searchButton.addEventListener('click', performSearch);
    }
});

async function loadRecommendedEvents() {
    try {
        const response = await fetch('../../assets/php/participant/get_recommended_events.php');
        const data = await response.json();
        
        console.log('Réponse recommandations:', data); // Debug

        if (data.success) {
            const container = document.getElementById('recommended-events');
            if (data.events.length === 0) {
                container.innerHTML = '<p class="no-events">Aucun événement recommandé pour le moment</p>';
            } else {
                container.innerHTML = data.events.map(event => createEventCard(event)).join('');
            }
        } else {
            console.error('Erreur:', data.message);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des événements recommandés:', error);
    }
}

async function loadTodayEvents() {
    try {
        const response = await fetch('../../assets/php/participant/get_today_events.php');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('today-events');
            container.innerHTML = data.events.map(event => createEventCard(event, false)).join('');
        }
    } catch (error) {
        console.error('Erreur lors du chargement des événements du jour:', error);
    }
}

async function loadMyEvents() {
    try {
        const response = await fetch('../../assets/php/participant/get_my_events.php');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('my-events-list');
            container.innerHTML = data.events.map(event => createEventCard(event, false)).join('');
        }
    } catch (error) {
        console.error('Erreur lors du chargement de mes événements:', error);
    }
}

async function performSearch() {
    const searchParams = {
        query: document.getElementById('search-input').value,
        category: document.getElementById('category-filter').value,
        date: document.getElementById('date-filter').value,
        city: document.getElementById('city-filter').value
    };

    try {
        const response = await fetch('../../assets/php/participant/search_events.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(searchParams)
        });
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('search-results');
            container.innerHTML = data.events.map(event => createEventCard(event, true)).join('');
        }
    } catch (error) {
        console.error('Erreur lors de la recherche:', error);
    }
}

function initializeSearch() {
    const searchButton = document.getElementById('search-button');
    if (searchButton) {
        searchButton.addEventListener('click', performSearch);
    }
}

function createEventCard(event) {
    const card = document.createElement('div');
    card.className = 'event-card';

    // Définir l'image par défaut
    const defaultImagePath = '../../assets/images/default-event.jpg';
    const imagePath = event.image 
        ? `../../uploads/images/${event.image}` 
        : defaultImagePath;

    card.innerHTML = `
        <div class="event-image">
            <img src="${imagePath}" 
                 alt="${event.nom}"
                 onerror="this.src='${defaultImagePath}'">
        </div>
        <div class="event-details">
            <h3>${event.nom}</h3>
            <p class="event-date">${formatDate(event.date)}</p>
            <p class="event-location">${event.ville} (${event.code_postal})</p>
            <p class="event-price">${event.prix}€</p>
            <button class="register-btn ${event.is_registered ? 'registered' : ''}" 
                    onclick="toggleRegistration(${event.id}, this)">
                ${event.is_registered ? 'Inscrit' : "S'inscrire"}
            </button>
        </div>
    `;

    return card.outerHTML;
}

async function toggleRegistration(eventId, button) {
    try {
        const response = await fetch('../../assets/php/participant/toggle_registration.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ event_id: eventId })
        });
        const data = await response.json();
        
        if (data.success) {
            button.classList.toggle('registered');
            button.textContent = button.classList.contains('registered') ? 'Se désinscrire' : 'S\'inscrire';
        }
    } catch (error) {
        console.error('Erreur lors de l\'inscription/désinscription:', error);
    }
}

async function cancelRegistration(eventId, button) {
    if (confirm('Êtes-vous sûr de vouloir annuler votre participation ?')) {
        try {
            const response = await fetch('../../assets/php/participant/cancel_registration.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ event_id: eventId })
            });
            const data = await response.json();
            
            if (data.success) {
                // Recharger les événements du jour
                loadTodayEvents();
            }
        } catch (error) {
            console.error('Erreur lors de l\'annulation:', error);
        }
    }
} 