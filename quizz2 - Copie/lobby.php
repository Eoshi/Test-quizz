<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Lobby - Bernard Quizz</title>
    <style>
        .selected { border: 4px solid #6366f1 !important; transform: scale(1.1); }
        .avatar-view { position: relative; width: 140px; height: 140px; margin: 0 auto; background: #f3f4f6; border-radius: 50%; border: 4px solid #ddd; }
        .av-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; }
    </style>
</head>
<body class="bg-indigo-900 text-white flex flex-col items-center justify-center min-h-screen p-4 font-sans">
    
    <div id="setup-ui" class="bg-white text-gray-800 p-6 rounded-3xl shadow-2xl max-w-sm w-full">
        <h2 class="text-xl font-black mb-4 text-center text-indigo-700 uppercase">Personnalise ton Bernardo</h2>
        
        <div class="avatar-view mb-6">
            <img id="prev-outfit" src="personnage/tenue/tenue1.png" class="av-layer">
            <img id="prev-hair" src="personnage/cheveux/cheveux1.png" class="av-layer">
        </div>

        <input type="text" id="nick-input" placeholder="Ton Pseudo..." class="w-full p-3 border-2 rounded-xl mb-6 outline-none focus:border-indigo-500 font-bold text-center">

        <p class="font-bold text-xs mb-2 uppercase text-gray-400">Cheveux</p>
        <div class="grid grid-cols-4 gap-2 mb-4">
            <?php for($i=1; $i<=4; $i++): ?>
                <button onclick="setHair(<?= $i ?>)" class="hair-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent">
                    <img src="personnage/cheveux/cheveux<?= $i ?>.png" class="h-8 mx-auto object-contain">
                </button>
            <?php endfor; ?>
        </div>

        <p class="font-bold text-xs mb-2 uppercase text-gray-400">Tenue</p>
        <div class="grid grid-cols-5 gap-2 mb-8">
            <?php for($i=1; $i<=5; $i++): ?>
                <button onclick="setOutfit(<?= $i ?>)" class="outfit-btn bg-gray-100 rounded-lg p-1 border-2 border-transparent">
                    <img src="personnage/tenue/tenue<?= $i ?>.png" class="h-8 mx-auto object-contain">
                </button>
            <?php endfor; ?>
        </div>

        <button onclick="joinGame()" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-xl hover:bg-indigo-700 shadow-xl transition">REJOINDRE</button>
    </div>

    <div id="wait-ui" class="hidden text-center">
        <h1 class="text-4xl font-black mb-4 animate-pulse">OK !</h1>
        <p class="text-indigo-200">Attend que le Maître lance le quiz...</p>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        let hIdx = 1; let oIdx = 1;

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

        function joinGame() {
            const nick = document.getElementById('nick-input').value;
            if(!nick) return alert("Mets un pseudo !");
            localStorage.setItem('quiz_nickname', nick);
            fetch(`api_live.php?action=join&pin=${pin}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname: nick, hair: hIdx, outfit: oIdx })
            }).then(() => {
                document.getElementById('setup-ui').classList.add('hidden');
                document.getElementById('wait-ui').classList.remove('hidden');
            });
        }

        setInterval(() => {
            fetch(`api_live.php?action=get_state&pin=${pin}`)
            .then(r => r.json()).then(data => { if(data.status === 'playing') window.location.href = `game_screen.php?pin=${pin}`; });
        }, 2000);
        
        setHair(1); setOutfit(1);
    </script>
</body>
</html>