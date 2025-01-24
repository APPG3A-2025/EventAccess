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
            case 'create':
                // Vérification des données
                if (empty($_POST['email']) || empty($_POST['password']) || 
                    empty($_POST['nom']) || empty($_POST['prenom'])) {
                    throw new Exception('Tous les champs sont obligatoires');
                }

                // Vérification si l'email existe déjà
                $stmt = $db->prepare('SELECT COUNT(*) FROM administrateurs WHERE email = ?');
                $stmt->execute([$_POST['email']]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Cet email est déjà utilisé');
                }

                // Création de l'administrateur
                $stmt = $db->prepare('
                    INSERT INTO administrateurs (email, mot_de_passe, nom, prenom) 
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([
                    $_POST['email'],
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['nom'],
                    $_POST['prenom']
                ]);
                $message = "Administrateur créé avec succès.";
                break;

            case 'update':
                if (isset($_POST['admin_id'])) {
                    $updates = [];
                    $params = [];

                    // Construction dynamique de la requête UPDATE
                    if (!empty($_POST['email'])) {
                        $updates[] = 'email = ?';
                        $params[] = $_POST['email'];
                    }
                    if (!empty($_POST['password'])) {
                        $updates[] = 'mot_de_passe = ?';
                        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    }
                    if (!empty($_POST['nom'])) {
                        $updates[] = 'nom = ?';
                        $params[] = $_POST['nom'];
                    }
                    if (!empty($_POST['prenom'])) {
                        $updates[] = 'prenom = ?';
                        $params[] = $_POST['prenom'];
                    }

                    if (!empty($updates)) {
                        $params[] = $_POST['admin_id'];
                        $sql = 'UPDATE administrateurs SET ' . implode(', ', $updates) . ' WHERE id = ?';
                        $stmt = $db->prepare($sql);
                        $stmt->execute($params);
                        $message = "Administrateur mis à jour avec succès.";
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['admin_id'])) {
                    // Empêcher la suppression de son propre compte
                    if ($_POST['admin_id'] == $_SESSION['admin_id']) {
                        throw new Exception('Vous ne pouvez pas supprimer votre propre compte');
                    }

                    $stmt = $db->prepare('DELETE FROM administrateurs WHERE id = ?');
                    $stmt->execute([$_POST['admin_id']]);
                    $message = "Administrateur supprimé avec succès.";
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Récupération des administrateurs
$stmt = $db->query('SELECT id, email, nom, prenom, date_creation, derniere_connexion FROM administrateurs ORDER BY date_creation DESC');
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Gestion des administrateurs</title>
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
                <a href="users.php">
                    <i class="fas fa-users"></i> Utilisateurs
                </a>
                <a href="events.php">
                    <i class="fas fa-calendar"></i> Événements
                </a>
                <a href="admins.php" class="active">
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
                <h1>Gestion des administrateurs</h1>
                <button class="btn btn-primary" onclick="showCreateModal()">
                    <i class="fas fa-plus"></i> Nouvel administrateur
                </button>
            </header>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Liste des administrateurs -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Date de création</th>
                            <th>Dernière connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?php echo $admin['id']; ?></td>
                                <td><?php echo htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($admin['date_creation'])); ?></td>
                                <td>
                                    <?php 
                                    echo $admin['derniere_connexion'] 
                                        ? date('d/m/Y H:i', strtotime($admin['derniere_connexion']))
                                        : 'Jamais connecté';
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editAdmin(<?php echo $admin['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteAdmin(<?php echo $admin['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal de création -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h2>Nouvel administrateur</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="createEmail">Email</label>
                    <input type="email" id="createEmail" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="createPassword">Mot de passe</label>
                    <input type="password" id="createPassword" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="createPrenom">Prénom</label>
                    <input type="text" id="createPrenom" name="prenom" required>
                </div>
                
                <div class="form-group">
                    <label for="createNom">Nom</label>
                    <input type="text" id="createNom" name="nom" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal d'édition -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Modifier l'administrateur</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="admin_id" id="editAdminId">
                
                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email">
                </div>
                
                <div class="form-group">
                    <label for="editPassword">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" id="editPassword" name="password">
                </div>
                
                <div class="form-group">
                    <label for="editPrenom">Prénom</label>
                    <input type="text" id="editPrenom" name="prenom">
                </div>
                
                <div class="form-group">
                    <label for="editNom">Nom</label>
                    <input type="text" id="editNom" name="nom">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showCreateModal() {
            document.getElementById('createModal').style.display = 'block';
        }

        function editAdmin(adminId) {
            // Récupérer les données de l'administrateur via AJAX
            fetch(`get_admin.php?id=${adminId}`)
                .then(response => response.json())
                .then(admin => {
                    document.getElementById('editAdminId').value = admin.id;
                    document.getElementById('editEmail').value = admin.email;
                    document.getElementById('editPrenom').value = admin.prenom;
                    document.getElementById('editNom').value = admin.nom;
                    document.getElementById('editModal').style.display = 'block';
                });
        }

        function deleteAdmin(adminId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="admin_id" value="${adminId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Fermer les modals si on clique en dehors
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html> 