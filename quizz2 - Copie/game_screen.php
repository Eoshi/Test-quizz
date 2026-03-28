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
    
    <div id="grid" class="hidden grid grid-cols-1 gap-2 p-2 h-4/5">
        <button onclick="submitAnswer(1)" class="bg-red-500 rounded-xl text-xl font-bold p-4 shadow-xl active:scale-95 transition flex items-center"><span class="mr-4 text-3xl">▲</span> <span id="txt1"></span></button>
        <button onclick="submitAnswer(2)" class="bg-blue-500 rounded-xl text-xl font-bold p-4 shadow-xl active:scale-95 transition flex items-center"><span class="mr-4 text-3xl">◆</span> <span id="txt2"></span></button>
        <button onclick="submitAnswer(3)" class="bg-yellow-500 rounded-xl text-xl font-bold p-4 shadow-xl active:scale-95 transition flex items-center"><span class="mr-4 text-3xl">●</span> <span id="txt3"></span></button>
        <button onclick="submitAnswer(4)" class="bg-green-500 rounded-xl text-xl font-bold p-4 shadow-xl active:scale-95 transition flex items-center"><span class="mr-4 text-3xl">■</span> <span id="txt4"></span></button>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        const nick = localStorage.getItem('quiz_nickname');
        document.getElementById('status-bar').innerText = nick;

        let lastQId = null; let currentCorrectAns = 1; let answered = false; let startTime = 0;

        function sync() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'reveal') {
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerText = "CONCENTREZ-VOUS...";
                    lastQId = data.question.id;
                    currentCorrectAns = data.question.correct_answer;
                } else if (data.status === 'playing') {
                    if(!answered) {
                        document.getElementById('msg').innerText = "VITE !";
                        document.getElementById('txt1').innerText = data.question.opt1;
                        document.getElementById('txt2').innerText = data.question.opt2;
                        document.getElementById('txt3').innerText = data.question.opt3;
                        document.getElementById('txt4').innerText = data.question.opt4;
                        document.getElementById('grid').classList.remove('hidden');
                        if(startTime === 0) startTime = Date.now();
                    }
                } else if (data.status === 'leaderboard') {
                    answered = false; startTime = 0;
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerText = "REGARDEZ LE MAÎTRE !";
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
            document.getElementById('msg').innerText = "RÉPONSE ENVOYÉE !";
        }
        setInterval(sync, 1500);
    </script>
</body>
</html>