<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Lobby - Bernard Quizz</title>
    <style>
        .selected { border: 4px solid #6366f1 !important; transform: scale(1.1); }
        .avatar-view { position: relative; width: 140px; height: 140px; margin: 0 auto; background: #eee; border-radius: 50%; }
        .av-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; z-index: 10; }
        .aura-view { position: absolute; top: -10px; left: -10px; width: 160px; height: 160px; z-index: 1; animation: spin 5s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-indigo-900 text-white flex flex-col items-center justify-center min-h-screen p-4">
    
    <div id="setup-ui" class="bg-white text-gray-800 p-6 rounded-3xl shadow-2xl max-w-sm w-full">
        <h2 class="text-xl font-black mb-4 text-center text-indigo-700 uppercase">Ton Personnage</h2>
        
        <div class="avatar-view mb-6 flex items-center justify-center">
            <img id="prev-aura" src="" class="aura-view hidden">
            <img id="prev-outfit" src="personnage/tenue/tenue1.png" class="av-layer">
            <img id="prev-hair" src="personnage/cheveux/cheveux1.png" class="av-layer" style="z-index: 20;">
        </div>

        <input type="text" id="nick-input" placeholder="Ton Pseudo..." value="<?= $_SESSION['username'] ?? '' ?>" class="w-full p-3 border-2 rounded-xl mb-4 font-bold text-center">

        <div class="overflow-y-auto max-h-48 space-y-4 pr-2">
            <p class="font-bold text-xs uppercase text-gray-400">Cheveux</p>
            <div class="grid grid-cols-4 gap-2">
                <?php for($i=1; $i<=4; $i++): ?>
                    <button onclick="setHair(<?= $i ?>)" class="hair-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent">
                        <img src="personnage/cheveux/cheveux<?= $i ?>.png" class="h-8 mx-auto object-contain">
                    </button>
                <?php endfor; ?>
            </div>

            <p class="font-bold text-xs uppercase text-gray-400">Tenue</p>
            <div class="grid grid-cols-5 gap-2">
                <?php for($i=1; $i<=5; $i++): ?>
                    <button onclick="setOutfit(<?= $i ?>)" class="outfit-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent">
                        <img src="personnage/tenue/tenue<?= $i ?>.png" class="h-8 mx-auto object-contain">
                    </button>
                <?php endfor; ?>
            </div>

            <?php if(isset($_SESSION['user_id'])): ?>
            <p class="font-bold text-xs uppercase text-yellow-500">Aura VIP</p>
            <div class="grid grid-cols-4 gap-2">
                <button onclick="setAura(0)" class="aura-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent">❌</button>
                <?php for($i=1; $i<=3; $i++): ?>
                    <button onclick="setAura(<?= $i ?>)" class="aura-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent">
                        <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-[10px]">A<?= $i ?></div>
                    </button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>

        <button onclick="joinGame()" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black mt-6 shadow-xl">REJOINDRE</button>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        let hIdx = 1, oIdx = 1, aIdx = 0;

        function setHair(id) {
            hIdx = id;
            document.getElementById('prev-hair').src = `personnage/cheveux/cheveux${id}.png`;
            document.querySelectorAll('.hair-btn').forEach((b, i) => b.classList.toggle('selected', i+1 === id));
        }
        function setOutfit(id) {
            oIdx = id;
            document.getElementById('prev-outfit').src = `personnage/tenue/tenue${id}.png`;
            document.querySelectorAll('.outfit-btn').forEach((b, i) => b.classList.toggle('selected', i+1 === id));
        }
        function setAura(id) {
            aIdx = id;
            const img = document.getElementById('prev-aura');
            if(id === 0) { img.classList.add('hidden'); }
            else {
                img.src = `personnage/aura/aura${id}.png`;
                img.classList.remove('hidden');
            }
            document.querySelectorAll('.aura-btn').forEach((b, i) => b.classList.toggle('selected', i === id));
        }

        function joinGame() {
            const nick = document.getElementById('nick-input').value;
            fetch(`api_live.php?action=join&pin=${pin}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname: nick, hair: hIdx, outfit: oIdx, aura: aIdx, is_member: <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?> })
            }).then(() => {
                document.getElementById('setup-ui').innerHTML = "<h1 class='text-4xl font-black text-center text-white'>PRÊT !</h1>";
            });
        }

        setInterval(() => {
            fetch(`api_live.php?action=get_state&pin=${pin}`)
            .then(r => r.json()).then(data => { if(data.status === 'reveal' || data.status === 'playing') window.location.href = `game_screen.php?pin=${pin}`; });
        }, 2000);
        
        setHair(1); setOutfit(1);
    </script>
</body>
</html>