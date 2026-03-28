<?php 
require_once 'db.php'; 
// On vérifie si l'utilisateur est un VIP (connecté)
$is_vip = isset($_SESSION['user_id']); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Lobby - Bernard Quizz</title>
    <style>
        .selected { border: 4px solid #6366f1 !important; transform: scale(1.1); }
        
        .avatar-view { 
            position: relative; width: 150px; height: 150px; 
            background: #e5e7eb; border-radius: 50%; border: 4px solid #fff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        /* L'Aura est devant tout le monde (z-30) et STATIQUE */
        .aura-layer { 
            position: absolute; inset: -15%; width: 130%; height: 130%; 
            z-index: 30; pointer-events: none; object-fit: contain;
        }

        .locked { filter: grayscale(1); opacity: 0.4; cursor: not-allowed !important; position: relative; }
        .lock-icon { 
            position: absolute; top: -5px; right: -5px; background: #000; color: #fff; 
            font-size: 10px; border-radius: 50%; width: 18px; height: 18px; 
            display: flex; align-items: center; justify-content: center; border: 2px solid #fff;
        }
    </style>
</head>
<body class="bg-indigo-900 text-white flex flex-col items-center justify-center min-h-screen p-4 font-sans">
    
    <div id="setup-ui" class="bg-white text-gray-800 p-6 rounded-3xl shadow-2xl max-w-sm w-full">
        <h2 class="text-xl font-black mb-4 text-center text-indigo-700 uppercase tracking-tighter">Ton Bernard</h2>
        
        <div class="avatar-view mb-6">
            <img id="prev-outfit" src="personnage/tenue/tenue1.png" class="absolute inset-0 w-full h-full object-contain" style="z-index: 10;">
            <img id="prev-hair" src="personnage/cheveux/cheveux1.png" class="absolute inset-0 w-full h-full object-contain" style="z-index: 20;">
            <img id="prev-aura" src="" class="aura-layer hidden">
        </div>

        <input type="text" id="nick-input" placeholder="Ton Pseudo..." value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" class="w-full p-3 border-2 rounded-xl mb-6 font-bold text-center outline-none focus:border-indigo-500">

        <div class="space-y-4 max-h-64 overflow-y-auto pr-2">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Coupe</p>
            <div class="grid grid-cols-4 gap-2">
                <?php for($i=1; $i<=4; $i++): ?>
                    <button onclick="setHair(<?= $i ?>)" class="hair-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent transition hover:bg-gray-200">
                        <img src="personnage/cheveux/cheveux<?= $i ?>.png" class="h-10 mx-auto object-contain">
                    </button>
                <?php endfor; ?>
            </div>

            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tenue</p>
            <div class="grid grid-cols-5 gap-2">
                <?php for($i=1; $i<=5; $i++): ?>
                    <button onclick="setOutfit(<?= $i ?>)" class="outfit-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent transition hover:bg-gray-200">
                        <img src="personnage/tenue/tenue<?= $i ?>.png" class="h-10 mx-auto object-contain">
                    </button>
                <?php endfor; ?>
            </div>

            <p class="text-xs font-bold text-yellow-500 uppercase tracking-widest flex justify-between items-center">
                Aura de Membre <?= !$is_vip ? '<span>🔒</span>' : '' ?>
            </p>
            <div class="grid grid-cols-4 gap-2">
                <button onclick="<?= $is_vip ? 'setAura(0)' : '' ?>" class="aura-btn bg-gray-100 rounded-lg p-2 border-2 <?= !$is_vip ? 'locked' : '' ?>">❌</button>
                <?php for($i=1; $i<=3; $i++): ?>
                    <button onclick="<?= $is_vip ? "setAura($i)" : '' ?>" class="aura-btn bg-gray-100 rounded-lg p-1 border-2 relative <?= !$is_vip ? 'locked' : '' ?>">
                        <div class="w-8 h-8 rounded-full bg-indigo-500 mx-auto flex items-center justify-center text-[8px] text-white font-bold">A<?= $i ?></div>
                        <?php if(!$is_vip): ?><div class="lock-icon">🔒</div><?php endif; ?>
                    </button>
                <?php endfor; ?>
            </div>
        </div>

        <button onclick="joinGame()" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black mt-6 shadow-xl active:scale-95 transition uppercase tracking-widest">
            Rejoindre
        </button>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        let hIdx = 1, oIdx = 1, aIdx = 0;

        function setHair(id) {
            hIdx = id; document.getElementById('prev-hair').src = `personnage/cheveux/cheveux${id}.png`;
            document.querySelectorAll('.hair-btn').forEach((b, i) => b.classList.toggle('selected', i+1 === id));
        }

        function setOutfit(id) {
            oIdx = id; document.getElementById('prev-outfit').src = `personnage/tenue/tenue${id}.png`;
            document.querySelectorAll('.outfit-btn').forEach((b, i) => b.classList.toggle('selected', i+1 === id));
        }

        function setAura(id) {
            aIdx = id; const img = document.getElementById('prev-aura');
            if(id === 0) { img.classList.add('hidden'); } 
            else { img.src = `personnage/aura/aura${id}.png`; img.classList.remove('hidden'); }
            document.querySelectorAll('.aura-btn').forEach((b, i) => b.classList.toggle('selected', i === id));
        }

        function joinGame() {
            const nick = document.getElementById('nick-input').value;
            if(!nick) return alert("Choisis un pseudo !");

            fetch(`api_live.php?action=join&pin=${pin}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname: nick, hair: hIdx, outfit: oIdx, aura: aIdx, is_member: <?= $is_vip ? 'true' : 'false' ?> })
            }).then(() => {
                document.getElementById('setup-ui').innerHTML = `
                    <div class="text-center py-10">
                        <h1 class='text-4xl font-black text-indigo-600 italic mb-4'>C'EST PARTI !</h1>
                        <p class='text-gray-500 font-bold'>Regarde l'écran du Maître...</p>
                        <div class='mt-6 text-5xl'>🎮</div>
                    </div>`;
            });
        }

        setInterval(() => {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'reveal' || data.status === 'playing') window.location.href = `game_screen.php?pin=${pin}`;
            });
        }, 1500);

        setHair(1); setOutfit(1);
    </script>
</body>
</html>