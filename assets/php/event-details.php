<?php
session_start();
require_once 'connexion.php';

// Récupérer l'ID de l'événement
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$event_id) {
    header('Location: ../../index.html');
    exit();
}

try {
    // Récupérer les détails de l'événement
    $stmt = $bdd->prepare('
        SELECT e.*, u.prenom as organisateur_prenom, u.nom as organisateur_nom 
        FROM evenement e 
        LEFT JOIN utilisateur u ON e.organisateur_id = u.id 
        WHERE e.id = ?
    ');
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Événement non trouvé');
    }

    // Vérifier si l'utilisateur est déjà inscrit
    $isRegistered = false;
    if (isset($_SESSION['user'])) {
        $stmt = $bdd->prepare('
            SELECT id FROM participants_evenements 
            WHERE evenement_id = ? AND utilisateur_id = ? AND statut = "confirmé"
        ');
        $stmt->execute([$event_id, $_SESSION['user']['id']]);
        $isRegistered = $stmt->fetch() !== false;
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventAccess - <?php echo htmlspecialchars($event['nom']); ?></title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/pages/event-details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include '../includes/navbar.php'; ?>

    <main class="event-details">
        <div class="event-header" style="background-image: url('../../uploads/images/<?php echo $event['image']; ?>')">
            <div class="event-header-content">
                <h1><?php echo htmlspecialchars($event['nom']); ?></h1>
                <div class="event-meta">
                    <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($event['date'])); ?></span>
                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['ville']); ?></span>
                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($event['categorie']); ?></span>
                </div>
            </div>
        </div>

        <div class="event-content">
            <div class="event-info">
                <section class="event-description">
                    <h2>Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </section>

                <section class="event-details-info">
                    <h2>Informations pratiques</h2>
                    <ul>
                        <li><i class="fas fa-map-marked-alt"></i> <strong>Adresse:</strong> <?php echo htmlspecialchars($event['adresse']); ?></li>
                        <li><i class="fas fa-euro-sign"></i> <strong>Prix:</strong> <?php echo $event['prix'] ? number_format($event['prix'], 2) . ' €' : 'Gratuit'; ?></li>
                        <li><i class="fas fa-user"></i> <strong>Organisateur:</strong> <?php echo htmlspecialchars($event['organisateur_prenom'] . ' ' . $event['organisateur_nom']); ?></li>
                    </ul>
                </section>
            </div>

            <div class="event-actions">
                <?php if (isset($_SESSION['user'])): ?>
                    <?php if (!$isRegistered): ?>
                        <button id="registerBtn" class="btn-primary" onclick="registerForEvent(<?php echo $event_id; ?>)">
                            S'inscrire à l'événement
                        </button>
                    <?php else: ?>
                        <button id="unregisterBtn" class="btn-danger" onclick="unregisterFromEvent(<?php echo $event_id; ?>)">
                            Se désinscrire
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn-primary">Connectez-vous pour vous inscrire</a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
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
                location.reload();
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
                    location.reload();
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
    </script>
</body>
</html> 