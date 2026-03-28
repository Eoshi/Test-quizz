<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Dashboard - Bernard Quizz</title>
</head>
<body class="bg-gray-100 font-sans">
    <nav class="bg-indigo-700 text-white p-4 shadow-lg">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-black italic">BERNARD QUIZZ</h1>
            <div class="flex gap-4 items-center">
                <a href="profil.php" class="bg-indigo-600 px-4 py-2 rounded-lg font-bold hover:bg-indigo-500 transition">👤 Mon Profil</a>
                <a href="logout.php" class="text-sm opacity-70 hover:opacity-100">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto py-12 px-6">
        <h2 class="text-3xl font-bold mb-8 text-gray-800">Bonjour, <?= htmlspecialchars($_SESSION['username']) ?> !</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if(hasRole('createur')): ?>
            <a href="manage_quizzes.php" class="bg-white p-8 rounded-3xl shadow-sm border-b-4 border-indigo-500 hover:scale-105 transition">
                <div class="text-4xl mb-4">📚</div>
                <h3 class="font-black text-xl mb-2">MES QUIZ</h3>
                <p class="text-gray-500 text-sm">Créez et gérez vos propres sessions de jeu.</p>
            </a>
            <?php endif; ?>

            <?php if(hasRole('admin')): ?>
            <a href="admin_users.php" class="bg-white p-8 rounded-3xl shadow-sm border-b-4 border-red-500 hover:scale-105 transition">
                <div class="text-4xl mb-4">🛠️</div>
                <h3 class="font-black text-xl mb-2">PANEL ADMIN</h3>
                <p class="text-gray-500 text-sm">Gérez les utilisateurs et les rangs du site.</p>
            </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>