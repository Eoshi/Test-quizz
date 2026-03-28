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
        <h2 class="text-5xl font-black mb-10 italic">FINI !</h2>
        <a href="index.php" class="bg-white text-indigo-900 px-12 py-4 rounded-full font-black text-2xl shadow-2xl">RETOUR</a>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        const nick = localStorage.getItem('quiz_nickname');
        document.getElementById('status-bar').innerText = nick;

        let lastQId = null; let answered = false; let startTime = 0;

        function sync() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'playing') {
                    // Si c'est une nouvelle question
                    if (lastQId !== data.question.id) {
                        lastQId = data.question.id;
                        answered = false;
                        document.getElementById('grid').classList.add('hidden');
                        document.getElementById('msg').innerText = "Préparez-vous...";
                        setTimeout(() => {
                            document.getElementById('grid').classList.remove('hidden');
                            document.getElementById('msg').innerText = "VITE !";
                            startTime = Date.now();
                        }, 2000);
                    }
                } else if (data.status === 'leaderboard') {
                    lastQId = null; // On force le reset du cache
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
            const time = (Date.now() - startTime) / 1000;
            const correct = (num == lastQId); // On peut simplifier la vérification côté serveur
            
            // On récupère l'info de correction directement de l'état local ou on l'envoie pour calcul
            fetch(`api_live.php?action=submit_answer&pin=${pin}`, {
                method: 'POST',
                body: JSON.stringify({ nickname: nick, is_correct: (num == data_cache_question_correct), response_time: time, answer_index: num })
            });
            // Pour Bernard Quizz, on envoie juste la réponse, api_live fera le calcul
            document.getElementById('grid').classList.add('hidden');
            document.getElementById('msg').innerText = "Réponse envoyée !";
        }
        
        // Petite correction sur submitAnswer pour être sur de la réponse correcte
        function submitAnswer(num) {
            if (answered) return;
            answered = true;
            const time = (Date.now() - startTime) / 1000;
            
            // On a besoin de savoir si c'est bon. On va le chercher dans l'objet question reçu.
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                const correct = (num == data.question.correct_answer);
                fetch(`api_live.php?action=submit_answer&pin=${pin}`, {
                    method: 'POST',
                    body: JSON.stringify({ nickname: nick, is_correct: correct, response_time: time, answer_index: num })
                });
                document.getElementById('msg').innerText = time < 3 ? "Rapide ! Ta copine doit être triste..." : "T'es lent, on dirait un papi...";
            });
            document.getElementById('grid').classList.add('hidden');
        }

        setInterval(sync, 1500);
    </script>
</body>
</html>