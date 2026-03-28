<?php
require_once 'db.php';
$pin = $_GET['pin'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Avatar - Bernard Quizz</title>
    <style>
        .preview-container { 
            width: 120px; 
            height: 120px; 
            position: relative; 
            margin: 0 auto 20px; 
            background: #f3f4f6;
            border-radius: 20px;
            overflow: hidden;
        }
        .preview-container img { 
            position: absolute; 
            inset: 0; 
            width: 100%; 
            height: 100%; 
            object-fit: contain; 
        }
        /* Scrollbar invisible pour les sélecteurs */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-indigo-600 min-h-screen text-white flex flex-col items-center p-4 font-sans">

    <h1 class="text-2xl font-black mb-4 uppercase tracking-widest">Crée ton Bernard</h1>

    <div class="bg-white text-gray-800 p-6 rounded-3xl shadow-2xl w-full max-w-md">
        
        <div class="preview-container shadow-inner border-4 border-indigo-100">
            <img id="prev-outfit" src="personnage/tenue/tenue1.png" style="z-index: 10;">
            <img id="prev-hair" src="personnage/cheveux/cheveux1.png" style="z-index: 20;">
            <div id="prev-aura-container"></div>
        </div>

        <div class="space-y-5">
            <input type="text" id="nick" maxlength="12" placeholder="TON PSEUDO" 
                   class="w-full p-4 bg-gray-100 border-none rounded-2xl font-black text-center text-indigo-600 focus:ring-4 focus:ring-indigo-200 outline-none transition-all">

            <div>
                <p class="text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Coupe de cheveux</p>
                <div class="flex gap-3 overflow-x-auto no-scrollbar py-1">
                    <?php for($i=1; $i<=10; $i++): ?>
                        <div onclick="setHair(<?= $i ?>)" class="flex-shrink-0 w-14 h-14 bg-gray-50 border-2 border-gray-100 rounded-xl cursor-pointer hover:border-indigo-400 transition-all p-1">
                            <img src="personnage/cheveux/cheveux<?= $i ?>.png" class="w-full h-full object-contain" onerror="this.parentElement.style.display='none'">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Style vestimentaire</p>
                <div class="flex gap-3 overflow-x-auto no-scrollbar py-1">
                    <?php for($i=1; $i<=10; $i++): ?>
                        <div onclick="setOutfit(<?= $i ?>)" class="flex-shrink-0 w-14 h-14 bg-gray-50 border-2 border-gray-100 rounded-xl cursor-pointer hover:border-indigo-400 transition-all p-1">
                            <img src="personnage/tenue/tenue<?= $i ?>.png" class="w-full h-full object-contain" onerror="this.parentElement.style.display='none'">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Aura magique</p>
                <div class="flex gap-3 overflow-x-auto no-scrollbar py-1">
                    <div onclick="setAura(0)" class="flex-shrink-0 w-14 h-14 bg-gray-100 border-2 border-dashed border-gray-300 rounded-xl flex items-center justify-center cursor-pointer font-bold text-gray-400">Ø</div>
                    <?php for($i=1; $i<=10; $i++): ?>
                        <div onclick="setAura(<?= $i ?>)" class="flex-shrink-0 w-14 h-14 bg-gray-50 border-2 border-gray-100 rounded-xl cursor-pointer hover:border-indigo-400 transition-all p-1">
                            <img src="personnage/aura/aura<?= $i ?>.png" class="w-full h-full object-contain" onerror="this.parentElement.style.display='none'">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <button onclick="join()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black text-lg shadow-lg transform active:scale-95 transition-all uppercase tracking-widest">
                C'est parti !
            </button>
        </div>
    </div>

    <script>
        let hair = 1, outfit = 1, aura = 0;

        function setHair(id) { 
            hair = id; 
            document.getElementById('prev-hair').src = `personnage/cheveux/cheveux${id}.png`; 
        }
        function setOutfit(id) { 
            outfit = id; 
            document.getElementById('prev-outfit').src = `personnage/tenue/tenue${id}.png`; 
        }
        function setAura(id) { 
            aura = id; 
            const cont = document.getElementById('prev-aura-container');
            cont.innerHTML = id > 0 ? `<img src="personnage/aura/aura${id}.png" class="animate-pulse" style="z-index: 30; transform: scale(1.3);">` : '';
        }

        function join() {
            const nick = document.getElementById('nick').value.trim();
            if(!nick) return alert("Hé ! Il nous faut ton pseudo !");

            fetch(`api_live.php?action=join&pin=<?= $pin ?>`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    nickname: nick, 
                    hair: hair, 
                    outfit: outfit, 
                    aura: aura, 
                    is_member: false 
                })
            })
            .then(r => r.json())
            .then(data => {
                if(data.status === 'success') {
                    window.location.href = `play.php?pin=<?= $pin ?>&nick=${encodeURIComponent(nick)}`;
                }
            });
        }
    </script>
</body>
</html>