<?php require_once 'db.php'; $is_vip = isset($_SESSION['user_id']); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Lobby - Bernard Quizz</title>
    <style>
        .selected { border: 4px solid #6366f1 !important; transform: scale(1.1); }
        .avatar-view { position: relative; width: 140px; height: 140px; background: #eee; border-radius: 50%; overflow: hidden; }
        .av-layer { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; }
        .aura-layer { position: absolute; inset: -10%; width: 120%; height: 120%; z-index: 30; pointer-events: none; }
        .locked { filter: grayscale(1); opacity: 0.5; cursor: not-allowed; position: relative; }
    </style>
</head>
<body class="bg-indigo-900 text-white flex flex-col items-center justify-center min-h-screen p-4">
    <div id="setup-ui" class="bg-white text-gray-800 p-6 rounded-3xl shadow-2xl max-w-sm w-full">
        <h2 class="text-xl font-black mb-4 text-center text-indigo-700 uppercase">Avatar</h2>
        <div class="avatar-view mb-6 mx-auto">
            <img id="prev-outfit" src="personnage/tenue/tenue1.png" class="av-layer" style="z-index: 10;">
            <img id="prev-hair" src="personnage/cheveux/cheveux1.png" class="av-layer" style="z-index: 20;">
            <img id="prev-aura" src="" class="aura-layer hidden">
        </div>
        <input type="text" id="nick-input" value="<?= $_SESSION['username'] ?? '' ?>" class="w-full p-3 border-2 rounded-xl mb-6 font-bold text-center">
        <div class="space-y-4 max-h-60 overflow-y-auto pr-2 text-xs">
            <p class="font-bold uppercase text-gray-400">Cheveux</p>
            <div class="grid grid-cols-4 gap-2"><?php for($i=1;$i<=4;$i++): ?><button onclick="setHair(<?= $i ?>)" class="hair-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent"><img src="personnage/cheveux/cheveux<?= $i ?>.png" class="h-8 mx-auto"></button><?php endfor; ?></div>
            <p class="font-bold uppercase text-gray-400">Tenue</p>
            <div class="grid grid-cols-5 gap-2"><?php for($i=1;$i<=5;$i++): ?><button onclick="setOutfit(<?= $i ?>)" class="outfit-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent"><img src="personnage/tenue/tenue<?= $i ?>.png" class="h-8 mx-auto"></button><?php endfor; ?></div>
            <p class="font-bold uppercase text-yellow-600">Aura VIP</p>
            <div class="grid grid-cols-4 gap-2">
                <button onclick="<?= $is_vip ? 'setAura(0)' : '' ?>" class="aura-btn bg-gray-100 rounded-lg p-2 <?= !$is_vip ? 'locked' : '' ?>">❌</button>
                <?php for($i=1;$i<=3;$i++): ?><button onclick="<?= $is_vip ? "setAura($i)" : '' ?>" class="aura-btn bg-gray-100 rounded-lg p-1 border-2 <?= !$is_vip ? 'locked' : '' ?>"><div class="w-6 h-6 rounded-full bg-indigo-500 mx-auto"></div></button><?php endfor; ?>
            </div>
        </div>
        <button onclick="joinGame()" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black mt-6">REJOINDRE</button>
    </div>
    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        let hIdx=1, oIdx=1, aIdx=0;
        function setHair(id) { hIdx=id; document.getElementById('prev-hair').src=`personnage/cheveux/cheveux${id}.png`; document.querySelectorAll('.hair-btn').forEach((b,i)=>b.classList.toggle('selected',i+1===id)); }
        function setOutfit(id) { oIdx=id; document.getElementById('prev-outfit').src=`personnage/tenue/tenue${id}.png`; document.querySelectorAll('.outfit-btn').forEach((b,i)=>b.classList.toggle('selected',i+1===id)); }
        function setAura(id) { aIdx=id; const img=document.getElementById('prev-aura'); if(id===0) img.classList.add('hidden'); else { img.src=`personnage/aura/aura${id}.png`; img.classList.remove('hidden'); } document.querySelectorAll('.aura-btn').forEach((b,i)=>b.classList.toggle('selected',i===id)); }
        function joinGame() {
            fetch(`api_live.php?action=join&pin=${pin}`, {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ nickname: document.getElementById('nick-input').value, hair: hIdx, outfit: oIdx, aura: aIdx, is_member: <?= $is_vip ? 'true' : 'false' ?> })
            }).then(() => document.body.innerHTML = "<h1 class='text-white text-4xl font-black text-center mt-20'>PRÊT !</h1>");
        }
        setInterval(() => { fetch(`api_live.php?action=get_state&pin=${pin}`).then(r=>r.json()).then(d => { if(d.status === 'reveal' || d.status === 'playing') window.location.href=`game_screen.php?pin=${pin}`; }); }, 1500);
        setHair(1); setOutfit(1);
    </script>
</body>
</html>