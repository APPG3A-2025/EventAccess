document.addEventListener('DOMContentLoaded', function() {
    // Charger les statistiques et les événements au chargement
    loadDashboardStats();
    loadEvents();

    // Gestionnaire pour le formulaire de création d'événement
    document.querySelector('.create-event-btn').addEventListener('click', showCreateEventForm);

    // Image de profil par défaut
    const profileImg = document.querySelector('.profile-image img');
    profileImg.onerror = function() {
        this.src = 'https://static.vecteezy.com/system/resources/thumbnails/005/544/718/small/profile-icon-design-free-vector.jpg';
    };

    // Gestion de la navigation
    const navItems = document.querySelectorAll('.nav-item');
    const sections = {
        dashboard: document.querySelector('.welcome-section').parentElement,
        events: createEventsSection(),
        delete: createDeleteSection(),
        stats: createStatsSection()
    };

    // Cacher toutes les sections sauf dashboard
    Object.values(sections).forEach(section => {
        if (section !== sections.dashboard) {
            section.style.display = 'none';
        }
    });

    navItems.forEach(item => {
        item.addEventListener('click', async (e) => {
            e.preventDefault();
            const sectionName = item.dataset.section;
            
            // Mise à jour des classes active
            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            
            // Cacher toutes les sections
            document.getElementById('dashboard-section').style.display = 'none';
            document.getElementById('stats-section').style.display = 'none';
            
            // Afficher la section appropriée
            if (sectionName === 'dashboard') {
                document.getElementById('dashboard-section').style.display = 'block';
                loadDashboardStats();
                loadEvents();
            } else if (sectionName === 'stats') {
                document.getElementById('stats-section').style.display = 'block';
                loadDetailedStats();
            }
        });
    });
    
});

async function loadDashboardStats() {
    try {
        const response = await fetch('../../assets/php/organisateur/get_stats.php');
        const data = await response.json();
        
        document.getElementById('active-events').textContent = data.activeEvents;
        document.getElementById('total-participants').textContent = data.totalParticipants;
        document.getElementById('next-event').textContent = data.nextEvent || 'Aucun événement prévu';
    } catch (error) {
        console.error('Erreur lors du chargement des statistiques:', error);
    }
}

async function loadEvents(section = 'events') {
    try {
        const response = await fetch('../../assets/php/organisateur/get_events.php');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message);
        }

        const events = result.events;
        const containerId = section === 'events' ? 'events-list' : 'delete-events-list';
        const container = document.getElementById(containerId);
        container.innerHTML = '';

        if (events.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Aucun événement</h3>
                    <p>Vous n'avez pas encore créé d'événement</p>
                </div>
            `;
            return;
        }

        events.forEach(event => {
            container.appendChild(createEventCard(event, section));
        });
    } catch (error) {
        console.error('Erreur lors du chargement des événements:', error);
    }
}

function createEventCard(event, section) {
    const card = document.createElement('div');
    card.className = 'event-card';
    const imagePath = event.image ? `../../uploads/images/${event.image}` : '../../assets/images/default-event.jpg';
    
    card.innerHTML = `
        <div class="event-image">
            <img src="${imagePath}" alt="${event.nom}" onerror="this.src='../../assets/images/default-event.jpg'">
        </div>
        <div class="event-details">
            <h3>${event.nom}</h3>
            <p><i class="fas fa-calendar"></i> ${formatDate(event.date)}</p>
            <p><i class="fas fa-map-marker-alt"></i> ${event.ville}</p>
            <p><i class="fas fa-ticket-alt"></i> ${event.prix}€</p>
            <div class="event-actions">
                <button onclick="editEvent(${event.id})" class="edit-btn">
                    <i class="fas fa-edit"></i> Modifier
                </button>
                <button onclick="deleteEvent(${event.id})" class="delete-btn">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
        </div>
    `;
    return card;
}

function showCreateEventForm() {
    const modal = document.getElementById('eventModal');
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <form action="../../assets/php/creation_evenement.php" method="POST" enctype="multipart/form-data" class="create-event-form" id="createEventForm">
                <!-- Informations de base -->
                <div class="form-group">
                    <label for="event-name">Nom de l'événement *</label>
                    <input type="text" id="event-name" name="name" required />
                </div>

                <div class="form-group">
                    <label for="event-category">Catégorie *</label>
                    <select id="event-category" name="category" required>
                        <option value="">Sélectionnez une catégorie</option>
                        <option value="concert">Concert</option>
                        <option value="theatre">Théâtre</option>
                        <option value="festival">Festival</option>
                        <option value="sport">Sport</option>
                        <option value="comedy">Comedy Show</option>
                        <option value="nightlife">Vie Nocturne</option>
                    </select>
                </div>

                <!-- Date et heure -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="event-date">Date *</label>
                        <input
                            type="date"
                            id="event-date"
                            name="date"
                            required
                            min="<?php echo date('Y-m-d'); ?>"
                        />
                    </div>
                    <div class="form-group">
                        <label for="event-time">Heure *</label>
                        <input type="time" id="event-time" name="time" required />
                    </div>
                </div>

                <!-- Lieu -->
                <div class="location-section">
                    <h3>Lieu de l'événement</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event-city">Ville *</label>
                            <div class="city-search-container">
                                <input
                                    type="text"
                                    id="event-city"
                                    name="city"
                                    autocomplete="off"
                                    required
                                />
                                <div id="city-suggestions" class="city-suggestions"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="event-postal">Code postal *</label>
                            <input
                                type="text"
                                id="event-postal"
                                name="postal"
                                pattern="[0-9]{5}"
                                required
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="event-address">Adresse *</label>
                        <input type="text" id="event-address" name="address" required />
                    </div>
                </div>

                <!-- Prix -->
                <div class="form-group">
                    <label for="event-price">Prix (€) *</label>
                    <input
                        type="number"
                        id="event-price"
                        name="price"
                        min="0"
                        step="0.01"
                        placeholder="0 pour gratuit"
                        required
                    />
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="event-description">Description *</label>
                    <textarea
                        id="event-description"
                        name="description"
                        rows="5"
                        required
                    ></textarea>
                </div>

                <!-- Images -->
                <div class="form-group">
                    <label for="event-image">Image</label>
                    <input type="file" id="event-image" name="image" accept="image/*" />
                    <div class="image-preview" id="image-preview"></div>
                </div>

                <button type="submit" class="submit-button">Créer l'événement</button>
            </form>
        </div>
    `;

    const form = document.getElementById('createEventForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validation des champs
        const requiredFields = ['name', 'category', 'date', 'time', 'city', 'postal', 'address', 'price', 'description'];
        let isValid = true;

        requiredFields.forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
        });

        if (!isValid) {
            showNotification('Veuillez remplir tous les champs obligatoires', 'error');
            return;
        }

        // Soumission du formulaire
        const formData = new FormData(form);
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                modal.style.display = 'none';
                showNotification('Événement créé avec succès!', 'success');
                loadEvents();
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            showNotification('Erreur lors de la création de l\'événement', 'error');
        }
    });

    modal.style.display = "block";

    modal.querySelector('.close').onclick = () => modal.style.display = "none";
    window.onclick = (event) => {
        if (event.target == modal) modal.style.display = "none";
    }
}

// Fonctions utilitaires
function formatDate(dateString) {
    const options = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('fr-FR', options);
}

function showNotification(message, type = 'info') {
    // Implémenter la notification
}

function createEventsSection() {
    const section = document.createElement('div');
    section.className = 'dashboard-section';
    section.innerHTML = `
        <div class="section-header">
            <h2>Mes événements</h2>
            <button class="create-event-btn" onclick="showCreateEventForm()">
                <i class="fas fa-plus"></i> Nouvel événement
            </button>
        </div>
        <div id="events-list" class="events-grid">
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>Aucun événement</h3>
                <p>Vous n'avez pas encore créé d'événement</p>
            </div>
        </div>
    `;
    return section;
}

function createDeleteSection() {
    const section = document.createElement('div');
    section.className = 'dashboard-section';
    section.innerHTML = `
        <h2>Supprimer des événements</h2>
        <div id="delete-events-list" class="events-grid">
            <div class="empty-state">
                <i class="fas fa-trash-alt"></i>
                <h3>Aucun événement à supprimer</h3>
                <p>Vous n'avez pas d'événements à supprimer pour le moment</p>
            </div>
        </div>
    `;
    return section;
}

function createStatsSection() {
    const section = document.createElement('div');
    section.className = 'dashboard-section';
    section.innerHTML = `
        <h2>Statistiques détaillées</h2>
        <div class="detailed-stats">
            <div class="stats-card">
                <i class="fas fa-calendar-alt"></i>
                <h3>Total des événements</h3>
                <p id="total-events">Chargement...</p>
            </div>
            <div class="stats-card">
                <i class="fas fa-clock"></i>
                <h3>Événements dernière semaine</h3>
                <p id="last-week-events">Chargement...</p>
            </div>
            <div class="stats-card">
                <i class="fas fa-calendar-week"></i>
                <h3>Événements dernier mois</h3>
                <p id="last-month-events">Chargement...</p>
            </div>
            <div class="stats-card">
                <i class="fas fa-forward"></i>
                <h3>Événements semaine prochaine</h3>
                <p id="next-week-events">Chargement...</p>
            </div>
            <div class="stats-card">
                <i class="fas fa-euro-sign"></i>
                <h3>Prix moyen des événements</h3>
                <p id="avg-price">Chargement...</p>
            </div>
        </div>
    `;

    // Charger les statistiques détaillées
    loadDetailedStats();
    return section;
}

async function loadDetailedStats() {
    try {
        const response = await fetch('../../assets/php/organisateur/get_detailed_stats.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('total-events').textContent = data.totalEvents;
            document.getElementById('last-week-events').textContent = data.lastWeekEvents;
            document.getElementById('last-month-events').textContent = data.lastMonthEvents;
            document.getElementById('next-week-events').textContent = data.nextWeekEvents;
            document.getElementById('avg-price').textContent = data.avgPrice + ' €';
            document.getElementById('total-participants-stats').textContent = data.totalParticipants;
            document.getElementById('avg-participants').textContent = data.avgParticipants;
            document.getElementById('avg-age').textContent = data.avgAge + ' ans';
        }
    } catch (error) {
        console.error('Erreur lors du chargement des statistiques détaillées:', error);
        // Afficher un message d'erreur dans les cartes
        const statsElements = [
            'total-events', 'last-week-events', 'last-month-events', 
            'next-week-events', 'avg-price', 'total-participants-stats',
            'avg-participants', 'avg-age'
        ];
        statsElements.forEach(id => {
            document.getElementById(id).textContent = 'Erreur de chargement';
        });
    }
}

function editEvent(eventId) {
    fetch(`../../assets/php/organisateur/get_event.php?id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showEditForm(data.event);
            }
        });
}

function showEditForm(event) {
    const modal = document.getElementById('eventModal');
    const dateTime = event.date.split(' ');
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <form action="../../assets/php/organisateur/update_event.php" method="POST" enctype="multipart/form-data" class="create-event-form" id="editEventForm">
                <input type="hidden" name="event_id" value="${event.id}">
                <div class="form-group">
                    <label for="nom">Nom de l'événement *</label>
                    <input type="text" id="nom" name="nom" value="${event.nom}" required>
                </div>
                <div class="form-group">
                    <label for="categorie">Catégorie *</label>
                    <select id="categorie" name="categorie" required>
                        <option value="concert" ${event.categorie === 'concert' ? 'selected' : ''}>Concert</option>
                        <option value="theatre" ${event.categorie === 'theatre' ? 'selected' : ''}>Théâtre</option>
                        <option value="festival" ${event.categorie === 'festival' ? 'selected' : ''}>Festival</option>
                        <option value="sport" ${event.categorie === 'sport' ? 'selected' : ''}>Sport</option>
                        <option value="comedy" ${event.categorie === 'comedy' ? 'selected' : ''}>Comedy Show</option>
                        <option value="nightlife" ${event.categorie === 'nightlife' ? 'selected' : ''}>Vie Nocturne</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date">Date *</label>
                        <input type="date" id="date" name="date" value="${dateTime[0]}" required>
                    </div>
                    <div class="form-group">
                        <label for="time">Heure *</label>
                        <input type="time" id="time" name="time" value="${dateTime[1]}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" value="${event.ville}" required>
                </div>
                <div class="form-group">
                    <label for="code_postal">Code postal *</label>
                    <input type="text" id="code_postal" name="code_postal" value="${event.code_postal}" required>
                </div>
                <div class="form-group">
                    <label for="adresse">Adresse *</label>
                    <input type="text" id="adresse" name="adresse" value="${event.adresse}" required>
                </div>
                <div class="form-group">
                    <label for="prix">Prix (€) *</label>
                    <input type="number" id="prix" name="prix" value="${event.prix}" min="0" required>
                </div>
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required>${event.description}</textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    ${event.image ? `<p>Image actuelle : ${event.image}</p>` : ''}
                </div>
                <button type="submit" class="submit-btn">Mettre à jour l'événement</button>
            </form>
        </div>
    `;
    modal.style.display = "block";
}

function deleteEvent(eventId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
        fetch('../../assets/php/organisateur/delete_event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `event_id=${eventId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadEvents();
                showNotification('Événement supprimé avec succès', 'success');
            } else {
                showNotification(data.message, 'error');
            }
        });
    }
} 