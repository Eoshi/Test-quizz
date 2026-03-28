<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$u = $stmt->fetch();

// Calcul du rang
$total = $u['total_correct'];
$rank = "Merguez de Bronze";
if($total > 50) $rank = "Apprenti Bernard";
if($total > 150) $rank = "Expert des Quiz";
if($total > 500) $rank = "Légende de Bernard";

// Précision
$ratio = ($u['total_correct'] + $u['total_wrong'] > 0) 
    ? round(($u['total_correct'] / ($u['total_correct'] + $u['total_wrong'])) * 100) 
    : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Mon Profil - Bernard Quizz</title>
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-black text-indigo-800 uppercase tracking-tighter">Mon Profil</h1>
            <a href="dashboard.php" class="bg-white px-4 py-2 rounded-lg shadow text-sm font-bold">← Dashboard</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-indigo-900 text-white p-8 rounded-3xl shadow-xl flex flex-col items-center justify-center text-center">
                <div class="text-6xl mb-4">👑</div>
                <h2 class="text-xl font-bold opacity-70">Ton Rang</h2>
                <p class="text-2xl font-black text-yellow-400 uppercase"><?= $rank ?></p>
                <p class="mt-4 text-sm italic">"<?= $total ?> bonnes réponses au compteur"</p>
            </div>

            <div class="md:col-span-2 grid grid-cols-2 gap-4">
                <div class="bg-white p-6 rounded-3xl shadow-sm border flex flex-col items-center justify-center">
                    <span class="text-4xl font-black text-indigo-600"><?= $ratio ?>%</span>
                    <span class="text-gray-500 font-bold uppercase text-xs">Précision</span>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border flex flex-col items-center justify-center">
                    <span class="text-4xl font-black text-green-500"><?= $u['podium_1'] ?></span>
                    <span class="text-gray-500 font-bold uppercase text-xs">Victoires</span>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border col-span-2 flex justify-around">
                    <div class="text-center"><p class="text-xl font-bold">🥈 <?= $u['podium_2'] ?></p><p class="text-[10px] text-gray-400">2ème</p></div>
                    <div class="text-center"><p class="text-xl font-bold">🥉 <?= $u['podium_3'] ?></p><p class="text-[10px] text-gray-400">3ème</p></div>
                    <div class="text-center"><p class="text-xl font-bold">❌ <?= $u['total_wrong'] ?></p><p class="text-[10px] text-gray-400">Erreurs</p></div>
                </div>
            </div>
        </div>
        
        <div class="mt-10 bg-white p-8 rounded-3xl shadow-sm border">
            <h3 class="font-black text-xl mb-6 uppercase tracking-widest text-gray-400">Personnalisation Permanente</h3>
            <p class="text-sm text-gray-500 mb-4">Ton avatar sera sauvegardé ici prochainement pour tes futures parties.</p>
        </div>
    </div>
</body>
</html>