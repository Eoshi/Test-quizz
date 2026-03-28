<?php
require_once 'db.php';
$quiz_id = $_GET['quiz_id'] ?? null;
$current_pin = $_GET['pin'] ?? null;

// ACTION : Création de la session JSON
if (isset($_POST['start_session'])) {
    $pin = rand(100000, 999999);
    
    // Chemin absolu vers le dossier sessions
    $chemin_dossier = __DIR__ . '/sessions';
    
    // Création du dossier s'il n'existe pas
    if (!is_dir($chemin_dossier)) { 
        mkdir($chemin_dossier, 0777, true); 
    }
    
    // NOM DU FICHIER : On utilise 'partie_' pour vérifier que le nouveau code est lu
    $fichier_partie = $chemin_dossier . '/partie_' . $pin . '.json';
    
    $blank = [
        'players' => [], 
        'scores' => new stdClass(), 
        'answers' => new stdClass(), 
        'status' => 'lobby', 
        'current_q_index' => -1, 
        'last_update' => time()
    ];
    
    file_put_contents($fichier_partie, json_encode($blank));
    
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
        <p class="text-xl mb-2 italic">Rejoignez sur votre téléphone avec le code :</p>
        <h1 class="text-8xl font-black mb-12 tracking-widest bg-white text-indigo-600 px-10 py-4 rounded-2xl shadow-2xl animate-pulse"><?= $current_pin ?></h1>
        
        <div class="w-full max-w-4xl bg-indigo-800 bg-opacity-40 p-8 rounded-3xl border-2 border-indigo-400 border-dashed">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-bold uppercase tracking-widest">Joueurs : <span id="count" class="text-yellow-400">0</span></h3>
                <button id="go-btn" onclick="startGame()" class="hidden bg-green-500 hover:bg-green-400 text-white px-8 py-3 rounded-full font-black text-lg transition shadow-lg transform hover:scale-105">LANCER LE JEU !</button>
            </div>
            <div id="list" class="grid grid-cols-2 md:grid-cols-5 gap-6"></div>
        </div>

        <script>
            function refresh() {
                fetch(`api_live.php?action=get_state&pin=<?= $current_pin ?>`)
                .then(r => r.json()).then(data => {
                    const list = document.getElementById('list');
                    list.innerHTML = "";
                    if(data.players && data.players.length > 0) {
                        data.players.forEach(p => {
                            let aura = (p.aura && p.aura > 0) ? `<img src="personnage/aura/aura${p.aura}.png" class="absolute inset-[-15%] w-[130%] h-[130%] object-contain pointer-events-none" style="z-index: 30;">` : '';
                            let badge = p.is_member ? `<div class="absolute -bottom-2 -right-2 bg-yellow-400 text-black text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full border-2 border-white" style="z-index: 40;">★</div>` : '';
                            
                            list.innerHTML += `
                                <div class="bg-white text-indigo-800 p-4 rounded-2xl font-bold text-center shadow-lg transform transition hover:-translate-y-2">
                                    <div class="relative w-16 h-16 mx-auto mb-3">
                                        <img src="personnage/tenue/tenue${p.outfit}.png" class="absolute inset-0 w-full h-full object-contain" style="z-index: 10;">
                                        <img src="personnage/cheveux/cheveux${p.hair}.png" class="absolute inset-0 w-full h-full object-contain" style="z-index: 20;">
                                        ${aura}${badge}
                                    </div>
                                    <p class="truncate text-xs uppercase tracking-widest">${p.nickname}</p>
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