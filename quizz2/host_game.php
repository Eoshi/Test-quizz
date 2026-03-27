<?php
require_once 'db.php';
$quiz_id = $_GET['quiz_id'] ?? null;
$current_pin = $_GET['pin'] ?? null;

if (isset($_POST['start_session'])) {
    $pin = rand(100000, 999999);
    // On initialise le fichier JSON immédiatement pour éviter les bugs de lecture
    $gameStateFile = "sessions/game_" . $pin . ".json";
    $initialState = ['players' => [], 'scores' => [], 'answers' => [], 'status' => 'lobby', 'current_q_index' => -1];
    if (!is_dir('sessions')) { mkdir('sessions', 0755, true); }
    file_put_contents($gameStateFile, json_encode($initialState));
    
    header("Location: host_game.php?pin=$pin&quiz_id=$quiz_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Salon - Bernard Quizz</title>
</head>
<body class="bg-indigo-600 min-h-screen text-white flex flex-col items-center p-10 font-sans">
    <?php if (!$current_pin): ?>
        <div class="bg-white p-8 rounded-2xl shadow-xl text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Prêt à lancer ?</h2>
            <form method="POST">
                <button name="start_session" class="bg-indigo-600 text-white px-10 py-4 rounded-full font-bold text-xl hover:bg-indigo-700 transition">
                    CRÉER LE SALON
                </button>
            </form>
        </div>
    <?php else: ?>
        <p class="text-xl mb-2">Rejoignez avec le code :</p>
        <h1 class="text-8xl font-black mb-12 tracking-widest bg-white text-indigo-600 px-10 py-4 rounded-2xl shadow-2xl">
            <?= $current_pin ?>
        </h1>

        <div class="w-full max-w-3xl bg-indigo-800 bg-opacity-40 p-8 rounded-3xl border-2 border-indigo-400 border-dashed">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-bold">Joueurs : <span id="count" class="text-yellow-400">0</span></h3>
                <button id="go-btn" onclick="startGame()" class="hidden bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-full font-black text-lg transition shadow-lg">
                    C'EST PARTI !
                </button>
            </div>
            <div id="list" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
        </div>

        <script>
            function refresh() {
                fetch(`api_live.php?action=get_players&pin=<?= $current_pin ?>`)
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('list');
                    const countSpan = document.getElementById('count');
                    const goBtn = document.getElementById('go-btn');
                    
                    list.innerHTML = "";
                    if(data.players && data.players.length > 0) {
                        data.players.forEach(p => {
                            list.innerHTML += `<div class="bg-white text-indigo-800 p-3 rounded-xl font-bold text-center shadow-md animate-pulse">${p}</div>`;
                        });
                        countSpan.innerText = data.players.length;
                        goBtn.classList.remove('hidden');
                    } else {
                        countSpan.innerText = "0";
                        goBtn.classList.add('hidden');
                    }
                });
            }

            function startGame() {
                fetch(`api_live.php?action=start_game&pin=<?= $current_pin ?>&quiz_id=<?= $quiz_id ?>`)
                .then(r => r.json())
                .then(data => {
                    window.location.href = `host_screen.php?pin=<?= $current_pin ?>`;
                });
            }
            
            refresh();
            setInterval(refresh, 2000);
        </script>
    <?php endif; ?>
</body>
</html>