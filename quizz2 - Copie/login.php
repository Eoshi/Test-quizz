<?php
require_once 'db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $userData = $stmt->fetch();

    if ($userData && password_verify($pass, $userData['password'])) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['role'] = $userData['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Pseudo ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Connexion - Bernard Quizz</title>
</head>
<body class="bg-indigo-900 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
        <h2 class="text-2xl font-bold text-indigo-700 mb-6 text-center">Connexion</h2>
        <?php if(isset($_GET['registered'])): ?>
            <p class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">Compte créé ! Connectez-vous.</p>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="text" name="username" placeholder="Pseudo" required class="w-full p-3 border rounded-lg outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="password" name="password" placeholder="Mot de passe" required class="w-full p-3 border rounded-lg outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="w-full bg-indigo-600 text-white p-3 rounded-lg font-bold hover:bg-indigo-700 transition">Se connecter</button>
        </form>
    </div>
</body>
</html>