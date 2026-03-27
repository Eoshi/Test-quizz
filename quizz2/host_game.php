<?php
require_once 'db.php';
$quiz_id = $_GET['quiz_id'] ?? null;
$current_pin = $_GET['pin'] ?? null;

if (isset($_POST['start_session'])) {
    $pin = rand(100000, 999999);
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
<body class="bg-indigo-600 min-h-screen text-white flex flex-col items-center p-10">
    <?php if (!$current_pin): ?>
        <form method="POST"><button name="start_session" class="bg-white text-indigo-600 px-8 py-4 rounded-full font-bold">OUVRIR LE SALON</button></form>
    <?php else: ?>
        <h1 class="text-7xl font-black mb-10"><?= $current_pin ?></h1>
        <div class="bg-indigo-800 p-8 rounded-xl w-full max-w-2xl">
            <div class="flex justify-between mb-4">
                <h3 class="text-xl font-bold">Joueurs : <span id="count">0</span></h3>
                <button id="go" onclick="startGame()" class="hidden bg-green-500 px-6 py-2 rounded">LANCER !</button>
            </div>
            <div id="list" class="grid grid-cols-3 gap-4 font-bold text-center"></div>
        </div>
        <script>
            function refresh() {
                fetch(`api_live.php?action=get_players&pin=<?= $current_pin ?>`)
                .then(r => r.json()).then(data => {
                    const list = document.getElementById('list');
                    list.innerHTML = "";
                    data.players.forEach(p => {
                        list.innerHTML += `<div class="bg-indigo-500 p-2 rounded">${p}</div>`;
                    });
                    document.getElementById('count').innerText = data.players.length;
                    if(data.players.length > 0) document.getElementById('go').classList.remove('hidden');
                });
            }
            function startGame() {
                fetch(`api_live.php?action=start_game&pin=<?= $current_pin ?>&quiz_id=<?= $quiz_id ?>`)
                .then(() => window.location.href = `host_screen.php?pin=<?= $current_pin ?>`);
            }
            setInterval(refresh, 2000);
        </script>
    <?php endif; ?>
</body>
</html>