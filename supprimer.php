<?php
// ===== Exercice 10 : suppression =====
require_once 'functions.php';
exiger_connexion();

$id = (int)($_GET['id'] ?? 0);
$liste = lire_etudiants();

if (isset($liste[$id])) {
    $photo = $liste[$id]['photo'];
    if ($photo !== '' && file_exists(DOSSIER_PHOTOS . basename($photo))) {
        unlink(DOSSIER_PHOTOS . basename($photo));
    }
    unset($liste[$id]);
    sauver_etudiants($liste);
    $_SESSION['flash'] = 'Étudiant supprimé.';
} else {
    $_SESSION['flash'] = 'Étudiant introuvable.';
}

header('Location: liste.php');
exit;
