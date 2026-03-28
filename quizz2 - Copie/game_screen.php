<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Play</title>
</head>
<body class="bg-indigo-900 text-white font-sans flex flex-col h-screen overflow-hidden">
    <div id="status-bar" class="p-4 bg-black bg-opacity-30 text-center font-bold italic"></div>
    <div id="msg" class="flex-grow flex items-center justify-center text-3xl font-black p-6 text-center italic">Attente...</div>
    
    <div id="grid" class="hidden grid grid-cols-2 gap-3 p-3 h-2/3">
        <button onclick="submitAnswer(1)" class="bg-red-500 rounded-2xl text-5xl shadow-xl active:scale-95 transition">▲</button>
        <button onclick="submitAnswer(2)" class="bg-blue-500 rounded-2xl text-5xl shadow-xl active:scale-95 transition">◆</button>
        <button onclick="submitAnswer(3)" class="bg-yellow-500 rounded-2xl text-5xl shadow-xl active:scale-95 transition">●</button>
        <button onclick="submitAnswer(4)" class="bg-green-500 rounded-2xl text-5xl shadow-xl active:scale-95 transition">■</button>
    </div>

    <div id="finish-area" class="hidden flex-grow flex flex-col items-center justify-center p-10 text-center">
        <h2 class="text-5xl font-black mb-10 italic uppercase tracking-tighter">Partie Finie !</h2>
        <a href="index.php" class="bg-white text-indigo-900 px-12 py-4 rounded-full font-black text-2xl shadow-2xl">RETOUR</a>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        const nick = localStorage.getItem('quiz_nickname');
        document.getElementById('status-bar').innerText = nick;

        let lastQIndex = -1; 
        let currentCorrectAns = 1;
        let answered = false; 
        let startTime = 0;

        function sync() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'playing') {
                    if (lastQIndex !== data.current_q_index) {
                        lastQIndex = data.current_q_index;
                        currentCorrectAns = data.question.correct_answer;
                        answered = false;
                        document.getElementById('grid').classList.add('hidden');
                        document.getElementById('finish-area').classList.add('hidden');
                        document.getElementById('msg').classList.remove('hidden');
                        document.getElementById('msg').innerText = "Préparez-vous...";
                        
                        setTimeout(() => {
                            document.getElementById('grid').classList.remove('hidden');
                            document.getElementById('msg').innerText = "VITE !";
                            startTime = Date.now();
                        }, 2000);
                    }
                } else if (data.status === 'leaderboard') {
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerText = "Regardez le Maître !";
                } else if (data.status === 'finished') {
                    document.getElementById('msg').classList.add('hidden');
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('finish-area').classList.remove('hidden');
                }
            });
        }

        function submitAnswer(num) {
            if (answered) return;
            answered = true;
            const responseTime = (Date.now() - startTime) / 1000;
            const isCorrect = (num == currentCorrectAns);

            fetch(`api_live.php?action=submit_answer&pin=${pin}`, {
                method: 'POST',
                body: JSON.stringify({ nickname: nick, is_correct: isCorrect, response_time: responseTime, answer_index: num })
            });

            document.getElementById('grid').classList.add('hidden');
            const funnyPhrases = ["Rapide ! Ta copine doit être triste...", "T'es lent, on dirait un papi...", "Flash McQueen !", "Calme-toi l'excité."];
            document.getElementById('msg').innerText = funnyPhrases[Math.floor(Math.random()*funnyPhrases.length)];
        }
        setInterval(sync, 1500);
    </script>
</body>
</html>