<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Play</title>
</head>
<body class="bg-indigo-900 text-white font-sans flex flex-col h-screen">
    <div id="msg" class="flex-grow flex items-center justify-center text-2xl font-bold p-6 text-center">Attente...</div>
    <div id="grid" class="hidden grid grid-cols-2 gap-2 p-2 h-2/3">
        <button onclick="ans(1)" class="bg-red-500 rounded-lg text-4xl">▲</button>
        <button onclick="ans(2)" class="bg-blue-500 rounded-lg text-4xl">◆</button>
        <button onclick="ans(3)" class="bg-yellow-500 rounded-lg text-4xl">●</button>
        <button onclick="ans(4)" class="bg-green-500 rounded-lg text-4xl">■</button>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        const nick = localStorage.getItem('quiz_nickname');
        let currentQ = null;
        let answered = false;
        let start = 0;

        function sync() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'playing' && (!currentQ || currentQ.id !== data.question.id)) {
                    currentQ = data.question; answered = false;
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerText = "Préparez-vous...";
                    setTimeout(() => {
                        document.getElementById('grid').classList.remove('hidden');
                        document.getElementById('msg').innerText = "RÉPONDEZ !";
                        start = Date.now();
                    }, 2000);
                } else if (data.status === 'leaderboard') {
                    currentQ = null;
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerText = "Regardez l'écran !";
                }
            });
        }

        function ans(num) {
            if (answered) return;
            answered = true;
            const time = (Date.now() - start) / 1000;
            const correct = (num == currentQ.correct_answer);
            fetch(`api_live.php?action=submit_answer&pin=${pin}`, {
                method: 'POST',
                body: JSON.stringify({ nickname: nick, is_correct: correct, response_time: time, answer_index: num })
            });
            document.getElementById('grid').classList.add('hidden');
            document.getElementById('msg').innerText = correct ? "BIEN JOUÉ !" : "FAUX...";
        }
        setInterval(sync, 1500);
    </script>
</body>
</html>