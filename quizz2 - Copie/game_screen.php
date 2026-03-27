<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Play</title>
</head>
<body class="bg-indigo-900 text-white font-sans flex flex-col h-screen overflow-hidden">
    <div id="status-bar" class="p-4 bg-black bg-opacity-30 text-center font-bold"></div>
    
    <div id="msg" class="flex-grow flex items-center justify-center text-3xl font-black p-6 text-center italic">Attente...</div>
    
    <div id="grid" class="hidden grid grid-cols-2 gap-3 p-3 h-2/3">
        <button onclick="submitAnswer(1)" class="bg-red-500 rounded-2xl text-5xl shadow-xl active:scale-95 transition">▲</button>
        <button onclick="submitAnswer(2)" class="bg-blue-500 rounded-2xl text-5xl shadow-xl active:scale-95 transition">◆</button>
        <button onclick="submitAnswer(3)" class="bg-yellow-500 rounded-2xl text-5xl shadow-xl active:scale-95 transition">●</button>
        <button onclick="submitAnswer(4)" class="bg-green-500 rounded-2xl text-5xl shadow-xl active:scale-95 transition">■</button>
    </div>

    <div id="finish-area" class="hidden p-10 text-center">
        <h2 class="text-4xl font-black mb-6">TERMINE !</h2>
        <a href="index.php" class="inline-block bg-white text-indigo-900 px-10 py-4 rounded-full font-bold text-xl shadow-2xl">RETOUR ACCUEIL</a>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        const nick = localStorage.getItem('quiz_nickname');
        document.getElementById('status-bar').innerText = nick;

        let currentQ = null;
        let answered = false;
        let startTime = 0;

        const slowRoasts = [
            "T'es lent, on dirait un papi...",
            "Réveille-toi, tu dors ou quoi ?",
            "Même une tortue va plus vite que toi."
        ];
        const fastRoasts = [
            "Rapide ! Ta copine doit être triste...",
            "Flash McQueen dans le salon !",
            "Calme-toi l'excité du clic."
        ];

        function sync() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'playing') {
                    if (!currentQ || currentQ.id !== data.question.id) {
                        currentQ = data.question; answered = false;
                        document.getElementById('grid').classList.add('hidden');
                        document.getElementById('msg').innerText = "Préparez-vous...";
                        setTimeout(() => {
                            document.getElementById('grid').classList.remove('hidden');
                            document.getElementById('msg').innerText = "VITE !";
                            startTime = Date.now();
                        }, 2000);
                    }
                } else if (data.status === 'leaderboard') {
                    currentQ = null;
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerText = "Regardez l'écran !";
                } else if (data.status === 'finished') {
                    document.getElementById('msg').classList.add('hidden');
                    document.getElementById('finish-area').classList.remove('hidden');
                }
            });
        }

        function submitAnswer(num) {
            if (answered) return;
            answered = true;
            const responseTime = (Date.now() - startTime) / 1000;
            const isCorrect = (num == currentQ.correct_answer);

            fetch(`api_live.php?action=submit_answer&pin=${pin}`, {
                method: 'POST',
                body: JSON.stringify({ nickname: nick, is_correct: isCorrect, response_time: responseTime, answer_index: num })
            });

            document.getElementById('grid').classList.add('hidden');
            
            if(responseTime < 3) {
                document.getElementById('msg').innerText = fastRoasts[Math.floor(Math.random()*fastRoasts.length)];
            } else {
                document.getElementById('msg').innerText = slowRoasts[Math.floor(Math.random()*slowRoasts.length)];
            }
        }
        setInterval(sync, 1500);
    </script>
</body>
</html>