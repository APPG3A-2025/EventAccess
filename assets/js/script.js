document.addEventListener('DOMContentLoaded', () => {
    const menuIcon = document.querySelector('.menu-icon');
    const navLinks = document.querySelector('.nav-links');
    const menuOverlay = document.querySelector('.menu-overlay');
    
    if (menuIcon) {
        menuIcon.addEventListener('click', () => {
            menuIcon.classList.toggle('active');
            navLinks.classList.toggle('active');
            menuOverlay.classList.toggle('active');
        });

        // Fermer le menu quand on clique sur l'overlay
        menuOverlay.addEventListener('click', () => {
            menuIcon.classList.remove('active');
            navLinks.classList.remove('active');
            menuOverlay.classList.remove('active');
        });

        // Fermer le menu quand on clique sur un lien
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                menuIcon.classList.remove('active');
                navLinks.classList.remove('active');
                menuOverlay.classList.remove('active');
            });
        });
    }
});

// Classe pour représenter un événement
class Event {
    constructor(name, date, time, location, description) {
        this.id = Date.now(); // Identifiant unique
        this.name = name;
        this.date = date;
        this.time = time;
        this.location = location;
        this.description = description;
    }
}

// Gestionnaire d'événements
class EventManager {
    constructor() {
        this.events = JSON.parse(localStorage.getItem('events')) || [];
    }

    addEvent(event) {
        this.events.push(event);
        this.saveEvents();
    }

    getEvents() {
        return this.events.sort((a, b) => new Date(a.date) - new Date(b.date));
    }

    saveEvents() {
        localStorage.setItem('events', JSON.stringify(this.events));
    }
}

// Initialisation du gestionnaire
const eventManager = new EventManager();

// Pour la page de création d'événement
if (document.querySelector('.vintage-form')) {
    const form = document.querySelector('.vintage-form');
    
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const newEvent = new Event(
            document.getElementById('eventName').value,
            document.getElementById('eventDate').value,
            document.getElementById('eventTime').value,
            document.getElementById('eventLocation').value,
            document.getElementById('eventDescription').value
        );
        
        eventManager.addEvent(newEvent);
        alert('Événement créé avec succès!');
        form.reset();
        window.location.href = 'list.html';
    });
}

// Pour la page de liste des événements
if (document.querySelector('.events-grid')) {
    function formatDate(dateStr) {
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        return new Date(dateStr).toLocaleDateString('fr-FR', options);
    }

    function displayEvents() {
        const eventsGrid = document.querySelector('.events-grid');
        const events = eventManager.getEvents();
        
        if (events.length === 0) {
            eventsGrid.innerHTML = '<p class="no-events">Aucun événement n\'a été créé pour le moment.</p>';
            return;
        }

        eventsGrid.innerHTML = events.map(event => `
            <div class="event-card">
                <div class="event-date">${formatDate(event.date)}</div>
                <h3>${event.name}</h3>
                <p>${event.description}</p>
                <div class="event-location">${event.location}</div>
                <div class="event-time">Heure: ${event.time}</div>
                <button class="vintage-button">Voir les détails</button>
            </div>
        `).join('');
    }

    // Afficher les événements au chargement de la page
    displayEvents();
} 