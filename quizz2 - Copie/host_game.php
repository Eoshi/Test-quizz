<?php
require_once 'db.php';
$quiz_id = $_GET['quiz_id'] ?? null;
$current_pin = $_GET['pin'] ?? null;

if (isset($_POST['start_session'])) {
    $pin = rand(100000, 999999);
    if (!is_dir('sessions')) { mkdir('sessions', 0755, true); }
    $f = "sessions/game_" . $pin . ".json";
    $blank = ['players' => [], 'scores' => [], 'answers' => [], 'status' => 'lobby', 'current_q_index' => -1];
    file_put_contents($f, json_encode($blank));
    header("Location: host_game.php?pin=$pin&quiz_id=$quiz_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Salon - Bernard Quizz</title>
    <style>
        .av-lobby { position: relative; width: 60px; height: 60px; margin: 0 auto; }
        .av-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; }
    </style>
</head>
<body class="bg-indigo-600 min-h-screen text-white flex flex-col items-center p-10 font-sans">
    <?php if (!$current_pin): ?>
        <div class="bg-white p-8 rounded-2xl shadow-xl text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Prêt à lancer ?</h2>
            <form method="POST"><button name="start_session" class="bg-indigo-600 text-white px-10 py-4 rounded-full font-bold text-xl hover:bg-indigo-700 transition">CRÉER LE SALON</button></form>
        </div>
    <?php else: ?>
        <p class="text-xl mb-2 italic">Rejoignez sur votre téléphone avec le code :</p>
        <h1 class="text-8xl font-black mb-12 tracking-widest bg-white text-indigo-600 px-10 py-4 rounded-2xl shadow-2xl animate-pulse"><?= $current_pin ?></h1>
        <div class="w-full max-w-3xl bg-indigo-800 bg-opacity-40 p-8 rounded-3xl border-2 border-indigo-400 border-dashed">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-bold uppercase tracking-widest">Joueurs : <span id="count" class="text-yellow-400">0</span></h3>
                <button id="go-btn" onclick="startGame()" class="hidden bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-full font-black text-lg transition shadow-lg">C'EST PARTI !</button>
            </div>
            <div id="list" class="grid grid-cols-2 md:grid-cols-4 gap-6"></div>
        </div>
        <script>
            function refresh() {
                fetch(`api_live.php?action=get_state&pin=<?= $current_pin ?>`)
                .then(r => r.json()).then(data => {
                    const list = document.getElementById('list');
                    list.innerHTML = "";
                    if(data.players && data.players.length > 0) {
                        data.players.forEach(p => {
                            // Correction : On accède aux propriétés p.nickname, p.hair, etc.
                            list.innerHTML += `
                                <div class="bg-white text-indigo-800 p-4 rounded-2xl font-bold text-center shadow-lg transform transition hover:scale-110">
                                    <div class="av-lobby mb-2">
                                        <img src="personnage/tenue/tenue${p.outfit}.png" class="av-layer">
                                        <img src="personnage/cheveux/cheveux${p.hair}.png" class="av-layer">
                                    </div>
                                    <p class="truncate text-sm uppercase">${p.nickname}</p>
                                </div>`;
                        });
                        document.getElementById('count').innerText = data.players.length;
                        document.getElementById('go-btn').classList.remove('hidden');
                    }
                });
            }
            function startGame() {
                fetch(`api_live.php?action=start_game&pin=<?= $current_pin ?>&quiz_id=<?= $quiz_id ?>`)
                .then(() => window.location.href = `host_screen.php?pin=<?= $current_pin ?>`);
            }
            setInterval(refresh, 2000);
            refresh();
        </script>
    <?php endif; ?>
</body>
</html>