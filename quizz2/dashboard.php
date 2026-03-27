<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Dashboard - Bernard Quizz</title>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-md p-4 flex justify-between items-center">
        <h1 class="text-xl font-black text-indigo-700 italic">BERNARD QUIZZ</h1>
        <div class="flex items-center gap-4">
            <span class="text-gray-700 font-medium">Salut, <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)</span>
            <a href="logout.php" class="text-red-500 text-sm hover:underline">Déconnexion</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto mt-10 p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-green-500">
            <h3 class="text-lg font-bold mb-2">Jouer</h3>
            <p class="text-gray-600 text-sm mb-4">Rejoindre un salon avec un code PIN.</p>
            <a href="index.php" class="block text-center bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition">Rejoindre</a>
        </div>

        <?php if(hasRole('createur')): ?>
        <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-indigo-500">
            <h3 class="text-lg font-bold mb-2">Créer</h3>
            <p class="text-gray-600 text-sm mb-4">Gérer vos quiz et vos questions.</p>
            <a href="manage_quizzes.php" class="block text-center bg-indigo-500 text-white py-2 rounded-lg hover:bg-indigo-600 transition">Mes Quiz</a>
        </div>
        <?php endif; ?>

        <?php if(hasRole('admin')): ?>
        <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-red-500">
            <h3 class="text-lg font-bold mb-2">Administration</h3>
            <p class="text-gray-600 text-sm mb-4">Gérer les utilisateurs et les rangs.</p>
            <a href="admin_users.php" class="block text-center bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition">Panel Admin</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>