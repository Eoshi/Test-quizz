<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Lobby - Bernard Quizz</title>
    <style>
        .avatar-preview { font-size: 50px; line-height: 1; }
        .selected { border: 4px solid #6366f1; transform: scale(1.1); }
    </style>
</head>
<body class="bg-indigo-900 text-white flex flex-col items-center justify-center min-h-screen p-4 font-sans">
    
    <div id="setup-ui" class="bg-white text-gray-800 p-8 rounded-3xl shadow-2xl max-w-md w-full">
        <h2 class="text-2xl font-black mb-6 text-center text-indigo-700 uppercase">Ton Personnage</h2>
        
        <input type="text" id="nick-input" placeholder="Ton Pseudo..." class="w-full p-4 border-2 rounded-xl mb-6 outline-none focus:border-indigo-500 font-bold text-center">

        <p class="font-bold mb-2">Coupe de cheveux :</p>
        <div class="grid grid-cols-4 gap-2 mb-6">
            <button onclick="setHair(1)" class="hair-btn p-2 bg-gray-100 rounded-xl text-2xl">👨‍🦱</button>
            <button onclick="setHair(2)" class="hair-btn p-2 bg-gray-100 rounded-xl text-2xl">👱</button>
            <button onclick="setHair(3)" class="hair-btn p-2 bg-gray-100 rounded-xl text-2xl">👨‍🦳</button>
            <button onclick="setHair(4)" class="hair-btn p-2 bg-gray-100 rounded-xl text-2xl">🧔</button>
        </div>

        <p class="font-bold mb-2">Tenue :</p>
        <div class="grid grid-cols-5 gap-2 mb-8">
            <button onclick="setOutfit(1)" class="outfit-btn p-2 bg-gray-100 rounded-xl text-2xl">👕</button>
            <button onclick="setOutfit(2)" class="outfit-btn p-2 bg-gray-100 rounded-xl text-2xl">🥋</button>
            <button onclick="setOutfit(3)" class="outfit-btn p-2 bg-gray-100 rounded-xl text-2xl">👔</button>
            <button onclick="setOutfit(4)" class="outfit-btn p-2 bg-gray-100 rounded-xl text-2xl">🧥</button>
            <button onclick="setOutfit(5)" class="outfit-btn p-2 bg-gray-100 rounded-xl text-2xl">👚</button>
        </div>

        <button onclick="joinGame()" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-xl hover:bg-indigo-700 transition">ENTRER DANS LE SALON</button>
    </div>

    <div id="wait-ui" class="hidden text-center">
        <h1 class="text-4xl font-black mb-4 italic">C'est parti !</h1>
        <p class="text-indigo-300">Attends que Bernard lance les hostilités...</p>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        let selectedHair = 1;
        let selectedOutfit = 1;

        function setHair(id) {
            selectedHair = id;
            document.querySelectorAll('.hair-btn').forEach((b, i) => b.classList.toggle('selected', i+1 === id));
        }
        function setOutfit(id) {
            selectedOutfit = id;
            document.querySelectorAll('.outfit-btn').forEach((b, i) => b.classList.toggle('selected', i+1 === id));
        }

        function joinGame() {
            const nick = document.getElementById('nick-input').value;
            if(!nick) return alert("Choisis un pseudo !");
            
            localStorage.setItem('quiz_nickname', nick);

            fetch(`api_live.php?action=join&pin=${pin}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname: nick, hair: selectedHair, outfit: selectedOutfit })
            }).then(() => {
                document.getElementById('setup-ui').classList.add('hidden');
                document.getElementById('wait-ui').classList.remove('hidden');
            });
        }

        setInterval(() => {
            fetch(`api_live.php?action=get_state&pin=${pin}`)
            .then(r => r.json()).then(data => {
                if(data.status === 'playing') window.location.href = `game_screen.php?pin=${pin}`;
            });
        }, 2000);
        
        setHair(1); setOutfit(1);
    </script>
</body>
</html>