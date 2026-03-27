<?php
require_once 'db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$user]);

    if ($check->rowCount() > 0) {
        $error = "Ce pseudo est déjà pris.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'utilisateur')");
        $stmt->execute([$user, $pass]);
        header("Location: login.php?registered=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Inscription - Bernard Quizz</title>
</head>
<body class="bg-indigo-900 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
        <h2 class="text-2xl font-bold text-indigo-700 mb-6 text-center">Créer un compte</h2>
        <?php if($error): ?>
            <p class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="text" name="username" placeholder="Pseudo" required class="w-full p-3 border rounded-lg outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="password" name="password" placeholder="Mot de passe" required class="w-full p-3 border rounded-lg outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="w-full bg-indigo-600 text-white p-3 rounded-lg font-bold hover:bg-indigo-700 transition">S'inscrire</button>
        </form>
        <p class="mt-4 text-center text-gray-600 text-sm">Déjà un compte ? <a href="login.php" class="text-indigo-600 hover:underline">Se connecter</a></p>
    </div>
</body>
</html>