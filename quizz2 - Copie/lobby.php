<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Lobby - Bernard Quizz</title>
    <style>
        .selected { border: 4px solid #6366f1 !important; transform: scale(1.1); }
        .avatar-view { position: relative; width: 140px; height: 140px; margin: 0 auto; background: #f3f4f6; border-radius: 50%; }
        .av-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; }
        .aura-layer { mix-blend-mode: screen; animation: rotate 4s linear infinite; }
        @keyframes rotate { from { transform: rotate(0deg) scale(1.2); } to { transform: rotate(360deg) scale(1.2); } }
    </style>
</head>
<body class="bg-indigo-900 text-white flex flex-col items-center justify-center min-h-screen p-4 font-sans">
    
    <div id="setup-ui" class="bg-white text-gray-800 p-6 rounded-3xl shadow-2xl max-w-sm w-full">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-black text-indigo-700 uppercase">Avatar</h2>
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="bg-yellow-400 text-yellow-900 text-[10px] px-2 py-1 rounded-full font-bold">MEMBRE VIP ⭐</span>
            <?php endif; ?>
        </div>
        
        <div class="avatar-view mb-6">
            <img id="prev-aura" src="" class="av-layer aura-layer hidden">
            <img id="prev-outfit" src="personnage/tenue/tenue1.png" class="av-layer">
            <img id="prev-hair" src="personnage/cheveux/cheveux1.png" class="av-layer">
        </div>

        <input type="text" id="nick-input" value="<?= $_SESSION['username'] ?? '' ?>" placeholder="Ton Pseudo..." class="w-full p-3 border-2 rounded-xl mb-4 outline-none focus:border-indigo-500 font-bold text-center">

        <p class="font-bold text-xs mb-2 uppercase text-gray-400">Cheveux</p>
        <div class="grid grid-cols-4 gap-2 mb-4">
            <?php for($i=1; $i<=4; $i++): ?>
                <button onclick="setHair(<?= $i ?>)" class="hair-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent"><img src="personnage/cheveux/cheveux<?= $i ?>.png" class="h-8 mx-auto"></button>
            <?php endfor; ?>
        </div>

        <?php if(isset($_SESSION['user_id'])): ?>
        <p class="font-bold text-xs mb-2 uppercase text-yellow-600">Aura Premium</p>
        <div class="grid grid-cols-4 gap-2 mb-6">
            <button onclick="setAura(0)" class="aura-btn bg-gray-100 rounded-lg p-1 border-2">❌</button>
            <?php for($i=1; $i<=3; $i++): ?>
                <button onclick="setAura(<?= $i ?>)" class="aura-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent"><img src="personnage/aura/aura<?= $i ?>.png" class="h-8 mx-auto"></button>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <button onclick="joinGame()" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-xl shadow-xl">REJOINDRE</button>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        const loggedUserId = "<?= $_SESSION['user_id'] ?? '' ?>";
        let hIdx = 1, oIdx = 1, aIdx = 0;

        function setHair(id) { hIdx = id; document.getElementById('prev-hair').src = `personnage/cheveux/cheveux${id}.png`; }
        function setAura(id) { 
            aIdx = id; 
            const img = document.getElementById('prev-aura');
            if(id === 0) img.classList.add('hidden');
            else { img.classList.remove('hidden'); img.src = `personnage/aura/aura${id}.png`; }
        }

        function joinGame() {
            const nick = document.getElementById('nick-input').value;
            localStorage.setItem('quiz_nickname', nick);
            fetch(`api_live.php?action=join&pin=${pin}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname: nick, user_id: loggedUserId, hair: hIdx, outfit: oIdx, aura: aIdx })
            }).then(() => {
                document.getElementById('setup-ui').innerHTML = "<h1 class='text-center text-2xl font-black p-10'>C'EST PARTI !</h1>";
            });
        }

        setInterval(() => {
            fetch(`api_live.php?action=get_state&pin=${pin}`)
            .then(r => r.json()).then(data => { if(data.status === 'playing') window.location.href = `game_screen.php?pin=${pin}`; });
        }, 2000);
    </script>
</body>
</html>