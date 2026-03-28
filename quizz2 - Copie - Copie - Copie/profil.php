<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

// Sécurité si les colonnes sont vides
$correct = $u['total_correct'] ?? 0;
$wrong = $u['total_wrong'] ?? 0;
$p1 = $u['podium_1'] ?? 0;
$p2 = $u['podium_2'] ?? 0;
$p3 = $u['podium_3'] ?? 0;

$rank = "Merguez de Bronze";
if($correct > 50) $rank = "Apprenti Bernard";
if($correct > 150) $rank = "Expert des Quiz";
if($correct > 500) $rank = "Légende de Bernard";

$precision = ($correct + $wrong > 0) ? round(($correct / ($correct + $wrong)) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Profil - Bernard Quizz</title>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-black text-indigo-800 uppercase">Mon Profil</h1>
            <a href="dashboard.php" class="text-indigo-600 font-bold">← Dashboard</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-indigo-900 text-white p-8 rounded-3xl shadow-xl text-center">
                <div class="text-6xl mb-4 italic">👑</div>
                <h2 class="text-xl font-bold opacity-60 uppercase text-xs">Ton Rang</h2>
                <p class="text-2xl font-black text-yellow-400"><?= $rank ?></p>
                <p class="text-xs mt-4 italic opacity-80"><?= $correct ?> bonnes réponses</p>
            </div>

            <div class="md:col-span-2 grid grid-cols-2 gap-4">
                <div class="bg-white p-6 rounded-3xl shadow-sm border text-center">
                    <p class="text-4xl font-black text-indigo-600"><?= $precision ?>%</p>
                    <p class="text-xs font-bold text-gray-400 uppercase">Précision</p>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border text-center">
                    <p class="text-4xl font-black text-green-500"><?= $p1 ?></p>
                    <p class="text-xs font-bold text-gray-400 uppercase">Victoires</p>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border col-span-2 flex justify-around items-center">
                    <div class="text-center"><p class="text-xl font-bold">🥈 <?= $p2 ?></p><p class="text-[10px] uppercase text-gray-400 font-bold">2ème place</p></div>
                    <div class="text-center"><p class="text-xl font-bold">🥉 <?= $p3 ?></p><p class="text-[10px] uppercase text-gray-400 font-bold">3ème place</p></div>
                    <div class="text-center"><p class="text-xl font-bold">❌ <?= $wrong ?></p><p class="text-[10px] uppercase text-gray-400 font-bold">Erreurs</p></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>