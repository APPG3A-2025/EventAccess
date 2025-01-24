<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';
require_once 'includes/Auth.php';

session_start();

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance()->getConnection();

// Gestion des actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'delete':
                if (isset($_POST['event_id'])) {
                    // Récupérer l'image avant la suppression
                    $stmt = $db->prepare('SELECT image FROM evenement WHERE id = ?');
                    $stmt->execute([$_POST['event_id']]);
                    $event = $stmt->fetch();

                    if ($event && $event['image']) {
                        $imagePath = '../' . $event['image'];
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }

                    $stmt = $db->prepare('DELETE FROM evenement WHERE id = ?');
                    $stmt->execute([$_POST['event_id']]);
                    $message = "Événement supprimé avec succès.";
                }
                break;
                
            case 'update':
                if (isset($_POST['event_id'])) {
                    $stmt = $db->prepare('UPDATE evenement SET 
                        nom = ?, 
                        categorie = ?, 
                        date = ?, 
                        ville = ?, 
                        code_postal = ?,
                        adresse = ?,
                        prix = ?,
                        description = ?
                        WHERE id = ?');
                    $stmt->execute([
                        $_POST['nom'],
                        $_POST['categorie'],
                        $_POST['date'] . ' ' . $_POST['time'],
                        $_POST['ville'],
                        $_POST['code_postal'],
                        $_POST['adresse'],
                        $_POST['prix'],
                        $_POST['description'],
                        $_POST['event_id']
                    ]);
                    $message = "Événement mis à jour avec succès.";
                }
                break;
        }
    } catch (PDOException $e) {
        $error = "Une erreur est survenue : " . $e->getMessage();
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
$params = [];

if (!empty($search)) {
    $searchCondition = "WHERE e.nom LIKE :search OR e.ville LIKE :search OR e.categorie LIKE :search";
    $params[':search'] = "%$search%";
}

// Compte total des événements pour la pagination
try {
    $countQuery = "SELECT COUNT(*) FROM evenement e $searchCondition";
    $stmt = $db->prepare($countQuery);
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");
    }
    $stmt->execute();
    $totalEvents = $stmt->fetchColumn();
    $totalPages = ceil($totalEvents / $limit);
} catch(PDOException $e) {
    error_log("Erreur de comptage : " . $e->getMessage());
    $error = "Une erreur est survenue lors du comptage des événements.";
}

// Récupération des événements
try {
    $query = "SELECT e.*, u.prenom, u.nom as organisateur_nom 
              FROM evenement e 
              LEFT JOIN utilisateur u ON e.organisateur_id = u.id 
              $searchCondition 
              ORDER BY e.date DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");
    }
    
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Erreur de récupération : " . $e->getMessage());
    $error = "Une erreur est survenue lors de la récupération des événements.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Gestion des événements</title>
    <link rel="stylesheet" href="./assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>EventAccess</h2>
                <p>Administration</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i> Tableau de bord
                </a>
                <a href="users.php">
                    <i class="fas fa-users"></i> Utilisateurs
                </a>
                <a href="events.php" class="active">
                    <i class="fas fa-calendar"></i> Événements
                </a>
                <a href="admins.php">
                    <i class="fas fa-user-shield"></i> Administrateurs
                </a>
            </nav>

            <!-- Profil utilisateur -->
            <div class="user-profile">
                <div class="user-info">
                    <div class="user-details">
                        <p class="user-name"><?php echo htmlspecialchars($_SESSION['admin_nom']); ?></p>
                        <p class="user-role">Administrateur</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-button">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </div>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <header class="content-header">
                <h1>Gestion des événements</h1>
                <form class="search-form" method="GET">
                    <input type="text" name="search" placeholder="Rechercher un événement..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </form>
            </header>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Date</th>
                            <th>Ville</th>
                            <th>Organisateur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['nom']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($event['date'])); ?></td>
                                <td><?php echo htmlspecialchars($event['ville']); ?></td>
                                <td>
                                    <?php 
                                    echo $event['prenom'] && $event['organisateur_nom'] 
                                        ? htmlspecialchars($event['prenom'] . ' ' . $event['organisateur_nom'])
                                        : 'Non assigné';
                                    ?>
                                </td>
                                <td>
                                    <button onclick="editEvent(<?php echo $event['id']; ?>)" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteEvent(<?php echo $event['id']; ?>)" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn <?php echo $page === $i ? 'btn-primary' : 'btn-secondary'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal d'édition -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Modifier l'événement</h2>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="event_id" id="editEventId">
                
                <div class="form-group">
                    <label for="editNom">Nom</label>
                    <input type="text" id="editNom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="editCategorie">Catégorie</label>
                    <select id="editCategorie" name="categorie" required>
                        <option value="concert">Concert</option>
                        <option value="theatre">Théâtre</option>
                        <option value="festival">Festival</option>
                        <option value="sport">Sport</option>
                        <option value="comedy">Comedy</option>
                        <option value="nightlife">Nightlife</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editDate">Date</label>
                        <input type="date" id="editDate" name="date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editTime">Heure</label>
                        <input type="time" id="editTime" name="time" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editVille">Ville</label>
                    <input type="text" id="editVille" name="ville" required>
                </div>
                
                <div class="form-group">
                    <label for="editCodePostal">Code postal</label>
                    <input type="text" id="editCodePostal" name="code_postal" required>
                </div>
                
                <div class="form-group">
                    <label for="editAdresse">Adresse</label>
                    <input type="text" id="editAdresse" name="adresse" required>
                </div>
                
                <div class="form-group">
                    <label for="editPrix">Prix</label>
                    <input type="number" id="editPrix" name="prix" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="editDescription">Description</label>
                    <textarea id="editDescription" name="description" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editEvent(eventId) {
            // Récupérer les données de l'événement via AJAX et remplir le modal
            fetch(`get_event.php?id=${eventId}`)
                .then(response => response.json())
                .then(event => {
                    document.getElementById('editEventId').value = event.id;
                    document.getElementById('editNom').value = event.nom;
                    document.getElementById('editCategorie').value = event.categorie;
                    
                    // Séparer la date et l'heure
                    const datetime = new Date(event.date);
                    document.getElementById('editDate').value = datetime.toISOString().split('T')[0];
                    document.getElementById('editTime').value = datetime.toTimeString().slice(0,5);
                    
                    document.getElementById('editVille').value = event.ville;
                    document.getElementById('editCodePostal').value = event.code_postal;
                    document.getElementById('editAdresse').value = event.adresse;
                    document.getElementById('editPrix').value = event.prix;
                    document.getElementById('editDescription').value = event.description;
                    
                    document.getElementById('editModal').style.display = 'block';
                });
        }

        function deleteEvent(eventId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="event_id" value="${eventId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Fermer le modal si on clique en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html> 