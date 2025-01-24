document.addEventListener('DOMContentLoaded', function() {
    loadDashboardEvents();

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
                loadDashboardEvents();
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

    // Ajoutons un écouteur d'événements pour le formulaire de recherche
    const searchForm = document.querySelector('.search-filters');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        // Recherche automatique lors du changement des filtres
        const filters = searchForm.querySelectorAll('input, select');
        filters.forEach(filter => {
            filter.addEventListener('change', performSearch);
        });
    }
});

function loadDashboardEvents() {
    fetch('../../assets/php/participant/get_dashboard_events.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayEvents('recommended-events', data.data.recommended);
                displayEvents('today-events', data.data.today);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayEvents(containerId, events) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';

    if (events.length === 0) {
        container.innerHTML = '<p class="no-events">Aucun événement à afficher</p>';
        return;
    }

    events.forEach(event => {
        const eventCard = `
            <div class="event-card">
                <div class="event-image">
                    <img src="${event.image ? '../../uploads/images/' + event.image : '../../assets/images/default-event.jpg'}" 
                         alt="${event.nom}">
                </div>
                <div class="event-details">
                    <h3>${event.nom}</h3>
                    <p class="event-date">
                        <i class="fas fa-calendar"></i>
                        ${new Date(event.date).toLocaleString('fr-FR', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}
                    </p>
                    <p class="event-location">
                        <i class="fas fa-map-marker-alt"></i>
                        ${event.ville}
                    </p>
                    <p class="event-price">
                        <i class="fas fa-euro-sign"></i>
                        ${event.prix ? event.prix + ' €' : 'Gratuit'}
                    </p>
                    <div class="event-actions">
                        <a href="../../assets/php/event-details.php?id=${event.id}" class="btn-details">
                            Voir les détails
                        </a>
                        ${event.is_registered ? `
                            <button onclick="unregisterFromEvent(${event.id})" class="btn-danger">
                                Se désinscrire
                            </button>
                        ` : `
                            <button onclick="registerForEvent(${event.id})" class="btn-primary">
                                S'inscrire
                            </button>
                        `}
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', eventCard);
    });
}

function registerForEvent(eventId) {
    fetch('../../assets/php/participant/register_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `event_id=${eventId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Inscription réussie !');
            loadDashboardEvents();
        } else {
            alert(data.message || 'Une erreur est survenue');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    });
}

function unregisterFromEvent(eventId) {
    if (confirm('Êtes-vous sûr de vouloir vous désinscrire ?')) {
        fetch('../../assets/php/participant/unregister_event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `event_id=${eventId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Désinscription réussie !');
                loadDashboardEvents();
            } else {
                alert(data.message || 'Une erreur est survenue');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue');
        });
    }
}

function loadMyEvents() {
    fetch('../../assets/php/participant/get_my_events.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayEvents('my-events-list', data.events);
            } else {
                document.getElementById('my-events-list').innerHTML = 
                    '<p class="no-events">Une erreur est survenue lors du chargement de vos événements.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('my-events-list').innerHTML = 
                '<p class="no-events">Une erreur est survenue lors du chargement de vos événements.</p>';
        });
}

function performSearch() {
    const searchData = new FormData();
    searchData.append('query', document.getElementById('search-input').value);
    searchData.append('category', document.getElementById('category-filter').value);
    searchData.append('date', document.getElementById('date-filter').value);
    searchData.append('city', document.getElementById('city-filter').value);

    fetch('../../assets/php/participant/search_events.php', {
        method: 'POST',
        body: searchData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayEvents('search-results', data.events);
        } else {
            document.getElementById('search-results').innerHTML = 
                '<p class="no-events">Une erreur est survenue lors de la recherche.</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('search-results').innerHTML = 
            '<p class="no-events">Une erreur est survenue lors de la recherche.</p>';
    });
}

function createEventCard(event) {
    const card = document.createElement('div');
    card.className = 'event-card';

    // Définir l'image par défaut
    const defaultImagePath = '../../assets/images/default-event.jpg';
    const imagePath = event.image 
        ? `../../uploads/events/${event.image}` 
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

    return card;
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