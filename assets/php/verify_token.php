<?php
function verifyToken($bdd, $token) {
    if (empty($token)) {
        return false;
    }

    $stmt = $bdd->prepare('SELECT id FROM utilisateur WHERE token = ?');
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return !empty($user);
}
?> 