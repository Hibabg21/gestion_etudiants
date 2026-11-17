
<?php
// ===== Connexion + Cookie =====
require_once __DIR__ . '/functions.php';

if (connecte()) {
    header('Location: liste.php');
    exit;
}

$erreur = '';
$login = $_COOKIE['dernier_user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    if (isset(UTILISATEURS[$login]) && UTILISATEURS[$login] === $mdp) {
        $_SESSION['login'] = $login;

        setcookie('dernier_user', $login, [
            'expires' => time() + 7 * 24 * 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        header('Location: liste.php');
        exit;
    }

    $erreur = 'Login ou mot de passe incorrect.';
}

$titre = 'Connexion';
include __DIR__ . '/header.php';
?>

<form method="post" class="carte">
    <h2>Connexion</h2>

    <?php if ($erreur !== ''): ?>
        <p class="message erreur"><?= h($erreur) ?></p>
    <?php endif; ?>

    <label>Login
        <input type="text" name="login"
               value="<?= h($login) ?>" required>
    </label>

    <label>Mot de passe
        <input type="password" name="mdp" required>
    </label>

    <button type="submit">Se connecter</button>
</form>

<?php include __DIR__ . '/footer.php'; ?>
