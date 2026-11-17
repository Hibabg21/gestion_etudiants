<?php
// =====  ajout / modification + photo =====
require_once 'functions.php';
exiger_connexion();

$liste = lire_etudiants();
$id = (int)($_GET['id'] ?? 0);
$modif = $id > 0 && isset($liste[$id]);

if ($id > 0 && !$modif) {
    $_SESSION['flash'] = 'Étudiant introuvable.';
    header('Location: liste.php');
    exit;
}


$valeurs = $modif
    ? $liste[$id]
    : ['nom' => '', 'prenom' => '', 'filiere' => '', 'note' => '', 'photo' => ''];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = nettoyer($_POST['nom'] ?? '');
    $prenom  = nettoyer($_POST['prenom'] ?? '');
    $filiere = $_POST['filiere'] ?? '';
    $note    = str_replace(',', '.', trim($_POST['note'] ?? ''));

    // On garde les valeurs saisies
    $valeurs['nom'] = $nom;
    $valeurs['prenom'] = $prenom;
    $valeurs['filiere'] = $filiere;
    $valeurs['note'] = $note;

    // --- Validation ---
    if ($nom === '') {
        $erreurs[] = 'Le nom est obligatoire.';
    }
    if ($prenom === '') {
        $erreurs[] = 'Le prénom est obligatoire.';
    }
    if (!in_array($filiere, FILIERES)) {
        $erreurs[] = 'Filière invalide.';
    }
    if (!is_numeric($note) || $note < 0 || $note > 20) {
        $erreurs[] = 'La note doit être un nombre entre 0 et 20.';
    }

    // --- Photo  ---
    $ext = '';
    $photoEnvoyee = isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE;
    if ($photoEnvoyee) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = "Erreur lors de l'envoi de la photo.";
        } else {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $erreurs[] = 'Photo : extensions autorisées jpg, jpeg, png.';
            } elseif ($_FILES['photo']['size'] > 1024 * 1024) {
                $erreurs[] = 'Photo : taille maximale 1 Mo.';
            }
        }
    }

    // --- Enregistrement ---
    if (empty($erreurs)) {
        $photo = $modif ? $liste[$id]['photo'] : '';

        if ($photoEnvoyee) {
            $nouveauNom = uniqid('etu_') . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], DOSSIER_PHOTOS . $nouveauNom)) {
                // supprimr l'ancienne photo en cas de modification
                if ($photo !== '' && file_exists(DOSSIER_PHOTOS . basename($photo))) {
                    unlink(DOSSIER_PHOTOS . basename($photo));
                }
                $photo = $nouveauNom;
            } else {
                $erreurs[] = "Impossible d'enregistrer la photo.";
            }
        }

        if (empty($erreurs)) {
            $nid = $modif ? $id : ($liste ? max(array_keys($liste)) + 1 : 1);
            $liste[$nid] = [
                'id'      => $nid,
                'nom'     => $nom,
                'prenom'  => $prenom,
                'filiere' => $filiere,
                'note'    => (float)$note,
                'photo'   => $photo,
            ];
            sauver_etudiants($liste);
            $_SESSION['flash'] = $modif ? 'Étudiant modifié avec succès.' : 'Étudiant ajouté avec succès.';
            header('Location: liste.php');
            exit;
        }
    }
}

$titre = $modif ? 'Modifier un étudiant' : 'Ajouter un étudiant';
include 'header.php';
?>
<form method="post" enctype="multipart/form-data" class="carte">
    <h2><?= h($titre) ?></h2>

    <?php foreach ($erreurs as $err): ?>
        <p class="message erreur"><?= h($err) ?></p>
    <?php endforeach; ?>

    <label>Nom
        <input type="text" name="nom" value="<?= h($valeurs['nom']) ?>">
    </label>
    <label>Prénom
        <input type="text" name="prenom" value="<?= h($valeurs['prenom']) ?>">
    </label>
    <label>Filière
        <select name="filiere">
            <?php foreach (FILIERES as $f): ?>
                <option value="<?= h($f) ?>" <?= $valeurs['filiere'] === $f ? 'selected' : '' ?>><?= h($f) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Note (0 à 20)
        <input type="text" name="note" value="<?= h((string)$valeurs['note']) ?>">
    </label>
    <label>Photo (jpg, jpeg, png - 1 Mo max)
        <input type="file" name="photo" accept=".jpg,.jpeg,.png">
    </label>

    <button type="submit"><?= $modif ? 'Mettre à jour' : 'Ajouter' ?></button>
    <a href="liste.php">Annuler</a>
</form>
<?php include 'footer.php'; ?>
