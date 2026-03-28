<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Play</title>
</head>
<body class="bg-indigo-900 text-white font-sans flex flex-col h-screen overflow-hidden p-4">
    <div id="status-bar" class="p-2 bg-black bg-opacity-30 text-center text-xs font-bold italic mb-4 rounded-full">
        Chargement...
    </div>

    <div id="msg" class="flex-grow flex items-center justify-center text-3xl font-black text-center italic uppercase p-6">
        Concentrez-vous...
    </div>
    
    <div id="grid" class="hidden grid grid-cols-1 gap-3 h-3/4">
        <button onclick="submitAns(1)" class="bg-red-500 rounded-2xl p-4 text-xl font-bold shadow-xl active:scale-95 transition flex items-center">
            <span class="text-3xl mr-4">▲</span><span id="txt1"></span>
        </button>
        <button onclick="submitAns(2)" class="bg-blue-500 rounded-2xl p-4 text-xl font-bold shadow-xl active:scale-95 transition flex items-center">
            <span class="text-3xl mr-4">◆</span><span id="txt2"></span>
        </button>
        <button onclick="submitAns(3)" class="bg-yellow-500 rounded-2xl p-4 text-xl font-bold shadow-xl active:scale-95 transition flex items-center">
            <span class="text-3xl mr-4">●</span><span id="txt3"></span>
        </button>
        <button onclick="submitAns(4)" class="bg-green-500 rounded-2xl p-4 text-xl font-bold shadow-xl active:scale-95 transition flex items-center">
            <span class="text-3xl mr-4">■</span><span id="txt4"></span>
        </button>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const pin = urlParams.get('pin');
        const nick = localStorage.getItem('quiz_nickname');
        
        document.getElementById('status-bar').innerText = `${nick} | PIN: ${pin}`;

        let answered = false;
        let lastQIndex = -1; 
        let startTime = 0;
        let correctAnsId = 1;

        // Phrases aléatoires Bernard Style
        const funnyPhrases = [
            "Rapide ! Ta copine doit être triste...",
            "T'es lent, on dirait un papi...",
            "Flash McQueen dans le salon !",
            "Calme-toi l'excité du clic.",
            "Même ma grand-mère clique plus vite."
        ];

        function sync() {
            fetch(`api_live.php?action=get_state&pin=${pin}`)
            .then(r => r.json())
            .then(data => {
                // CAS 1 : REVEAL (Le zoom sur la question)
                if (data.status === 'reveal') {
                    // Si on change de question, on reset l'état local
                    if(lastQIndex !== data.current_q_index) {
                        lastQIndex = data.current_q_index;
                        answered = false;
                        startTime = 0;
                        correctAnsId = data.question.correct_answer;
                    }
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerText = "CONCENTREZ-VOUS...";
                } 
                
                // CAS 2 : PLAYING (Le chrono tourne)
                else if (data.status === 'playing') {
                    if (!answered) {
                        document.getElementById('msg').innerText = "VITE !";
                        // On injecte le texte des réponses sur les boutons
                        document.getElementById('txt1').innerText = data.question.opt1;
                        document.getElementById('txt2').innerText = data.question.opt2;
                        document.getElementById('txt3').innerText = data.question.opt3;
                        document.getElementById('txt4').innerText = data.question.opt4;
                        
                        document.getElementById('grid').classList.remove('hidden');
                        
                        // On lance le chrono local au premier affichage
                        if (startTime === 0) startTime = Date.now();
                    }
                } 
                
                // CAS 3 : LEADERBOARD (Classement entre questions)
                else if (data.status === 'leaderboard') {
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerText = "REGARDEZ LE MAÎTRE !";
                }

                // CAS 4 : FINISHED (Podium final)
                else if (data.status === 'finished') {
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerHTML = "<div class='text-center'>PARTIE FINIE !<br><span class='text-sm font-normal'>Redirection...</span></div>";
                    setTimeout(() => { window.location.href = "index.php"; }, 5000);
                }
            })
            .catch(err => console.error("Erreur synchro:", err));
        }

        function submitAns(num) {
            if (answered) return;
            answered = true;

            const responseTime = (Date.now() - startTime) / 1000;
            const isCorrect = (num == correctAnsId);

            // Envoi de la réponse en JSON propre
            fetch(`api_live.php?action=submit_answer&pin=${pin}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    nickname: nick, 
                    is_correct: isCorrect, 
                    response_time: responseTime, 
                    answer_index: num 
                })
            })
            .then(r => r.json())
            .then(res => {
                document.getElementById('grid').classList.add('hidden');
                // On affiche une phrase de Bernard au pif
                const msgRandom = funnyPhrases[Math.floor(Math.random() * funnyPhrases.length)];
                document.getElementById('msg').innerText = msgRandom;
            });
        }

        // Vérification toutes les 1.5 secondes
        setInterval(sync, 1500);
        // Premier appel immédiat
        sync();
    </script>
</body>
</html>