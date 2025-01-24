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
                if (isset($_POST['user_id'])) {
                    $stmt = $db->prepare('DELETE FROM utilisateur WHERE id = ?');
                    $stmt->execute([$_POST['user_id']]);
                    $message = "Utilisateur supprimé avec succès.";
                }
                break;
                
            case 'update':
                if (isset($_POST['user_id'])) {
                    $stmt = $db->prepare('UPDATE utilisateur SET 
                        email = ?, 
                        prenom = ?, 
                        nom = ?, 
                        role = ?
                        WHERE id = ?');
                    $stmt->execute([
                        $_POST['email'],
                        $_POST['prenom'],
                        $_POST['nom'],
                        $_POST['role'],
                        $_POST['user_id']
                    ]);
                    $message = "Utilisateur mis à jour avec succès.";
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
    $searchCondition = "WHERE nom LIKE :search OR prenom LIKE :search OR email LIKE :search";
    $params[':search'] = "%$search%";
}

// Compte total des utilisateurs pour la pagination
try {
    $countQuery = "SELECT COUNT(*) FROM utilisateur $searchCondition";
    $stmt = $db->prepare($countQuery);
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");
    }
    $stmt->execute();
    $totalUsers = $stmt->fetchColumn();
    $totalPages = ceil($totalUsers / $limit);
} catch(PDOException $e) {
    error_log("Erreur de comptage : " . $e->getMessage());
    $error = "Une erreur est survenue lors du comptage des utilisateurs.";
}

// Récupération des utilisateurs
try {
    $query = "SELECT * FROM utilisateur 
              $searchCondition 
              ORDER BY date_inscription DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");
    }
    
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Erreur de récupération : " . $e->getMessage());
    $error = "Une erreur est survenue lors de la récupération des utilisateurs.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Gestion des utilisateurs</title>
    <link rel="stylesheet" href="assets/css/admin.css">
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
                <a href="users.php" class="active">
                    <i class="fas fa-users"></i> Utilisateurs
                </a>
                <a href="events.php">
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
                <h1>Gestion des utilisateurs</h1>
                
                <!-- Barre de recherche -->
                <form class="search-form" method="GET">
                    <input type="text" name="search" placeholder="Rechercher un utilisateur..." 
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

            <!-- Liste des utilisateurs -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($totalPages > 1): ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                           class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal d'édition -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Modifier l'utilisateur</h2>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" id="editUserId">
                
                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="editPrenom">Prénom</label>
                    <input type="text" id="editPrenom" name="prenom" required>
                </div>
                
                <div class="form-group">
                    <label for="editNom">Nom</label>
                    <input type="text" id="editNom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="editRole">Rôle</label>
                    <select id="editRole" name="role" required>
                        <option value="utilisateur">Utilisateur</option>
                        <option value="organisateur">Organisateur</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editUser(userId) {
            // Récupérer les données de l'utilisateur via AJAX et remplir le modal
            fetch(`get_user.php?id=${userId}`)
                .then(response => response.json())
                .then(user => {
                    document.getElementById('editUserId').value = user.id;
                    document.getElementById('editEmail').value = user.email;
                    document.getElementById('editPrenom').value = user.prenom;
                    document.getElementById('editNom').value = user.nom;
                    document.getElementById('editRole').value = user.role;
                    document.getElementById('editModal').style.display = 'block';
                });
        }

        function deleteUser(userId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${userId}">
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