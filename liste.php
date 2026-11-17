
<?php
// ===== Exercices 6 et 7 : liste, recherche, tri, statistiques =====
require_once __DIR__ . '/functions.php';
exiger_connexion();

$liste = lire_etudiants();

// --- Recherche (GET q) ---
$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $liste = array_filter($liste, function ($e) use ($q) {
        return stripos($e['nom'], $q) !== false
            || stripos($e['prenom'], $q) !== false;
    });
}

// --- Tri (GET tri) ---
$tri = $_GET['tri'] ?? 'ajout';

if (!in_array($tri, ['ajout', 'nom', 'filiere', 'note'])) {
    $tri = 'ajout';
}

$liste = array_values($liste);

if ($tri === 'nom') {
    usort($liste, fn($a, $b) => strcasecmp($a['nom'], $b['nom']));
} elseif ($tri === 'filiere') {
    usort($liste, fn($a, $b) => strcasecmp($a['filiere'], $b['filiere']));
} elseif ($tri === 'note') {
    usort($liste, fn($a, $b) => $b['note'] <=> $a['note']);
}

// --- Statistiques ---
$nb = count($liste);
$notes = array_column($liste, 'note');

$moyenne = $nb > 0 ? array_sum($notes) / $nb : 0;
$meilleure = $nb > 0 ? max($notes) : 0;

// --- Message flash ---
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$titre = 'Liste des étudiants';
include __DIR__ . '/header.php';
?>

<?php if ($flash !== ''): ?>
    <p class="message succes"><?= h($flash) ?></p>
<?php endif; ?>

<!-- Recherche et tri -->
<form method="get" class="recherche">
    <input
        type="text"
        id="recherche"
        name="q"
        placeholder="Rechercher un nom ou prénom..."
        value="<?= h($q) ?>"
    >

    <select name="tri">
        <option value="ajout" <?= $tri === 'ajout' ? 'selected' : '' ?>>
            Ordre d'ajout
        </option>

        <option value="nom" <?= $tri === 'nom' ? 'selected' : '' ?>>
            Nom
        </option>

        <option value="filiere" <?= $tri === 'filiere' ? 'selected' : '' ?>>
            Filière
        </option>

        <option value="note" <?= $tri === 'note' ? 'selected' : '' ?>>
            Note (décroissante)
        </option>
    </select>

    <button type="submit">Valider</button>
</form>

<!-- Statistiques -->
<p class="stats">
    Étudiants affichés : <strong><?= $nb ?></strong> |
    Moyenne : <strong><?= number_format($moyenne, 2) ?></strong> |
    Meilleure note : <strong><?= number_format($meilleure, 2) ?></strong>
</p>

<!-- Tableau des étudiants -->
<table>
    <thead>
        <tr>
            <th>Photo</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Filière</th>
            <th>Note</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($liste)): ?>
            <tr>
                <td colspan="6">Aucun étudiant</td>
            </tr>
        <?php else: ?>

            <?php foreach ($liste as $e): ?>

                <?php
                $classe = $e['note'] >= 10 ? 'note-verte' : 'note-rouge';

                $photoOk = $e['photo'] !== ''
                    && file_exists(DOSSIER_PHOTOS . basename($e['photo']));
                ?>

                <tr data-nom="<?= h($e['nom'] . ' ' . $e['prenom']) ?>">

                    <td>
                        <?php if ($photoOk): ?>
                            <img
                                class="photo"
                                src="uploads/<?= h(basename($e['photo'])) ?>"
                                alt="Photo de <?= h($e['prenom']) ?>"
                            >
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>

                    <td><?= h(strtoupper($e['nom'])) ?></td>

                    <td><?= h(ucfirst($e['prenom'])) ?></td>

                    <td><?= h($e['filiere']) ?></td>

                    <td class="<?= $classe ?>">
                        <?= h((string)$e['note']) ?>
                    </td>

                    <td>
                        <a href="ajouter.php?id=<?= (int)$e['id'] ?>">
                            Modifier
                        </a>

                        |

                        <a
                            class="lien-suppr"
                            href="supprimer.php?id=<?= (int)$e['id'] ?>"
                        >
                            Supprimer
                        </a>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/footer.php'; ?>