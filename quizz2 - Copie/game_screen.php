<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Play</title>
</head>
<body class="bg-indigo-900 text-white font-sans flex flex-col h-screen overflow-hidden p-4">
    <div id="msg" class="flex-grow flex items-center justify-center text-3xl font-black text-center italic uppercase">Concentrez-vous...</div>
    
    <div id="grid" class="hidden grid grid-cols-1 gap-2 h-3/4">
        <button onclick="submitAns(1)" class="bg-red-500 rounded-xl p-4 text-xl font-bold flex items-center shadow-lg"><span class="text-3xl mr-4">▲</span><span id="txt1"></span></button>
        <button onclick="submitAns(2)" class="bg-blue-500 rounded-xl p-4 text-xl font-bold flex items-center shadow-lg"><span class="text-3xl mr-4">◆</span><span id="txt2"></span></button>
        <button onclick="submitAns(3)" class="bg-yellow-500 rounded-xl p-4 text-xl font-bold flex items-center shadow-lg"><span class="text-3xl mr-4">●</span><span id="txt3"></span></button>
        <button onclick="submitAns(4)" class="bg-green-500 rounded-xl p-4 text-xl font-bold flex items-center shadow-lg"><span class="text-3xl mr-4">■</span><span id="txt4"></span></button>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        const nick = localStorage.getItem('quiz_nickname');
        let answered = false, lastIdx = -1, start = 0, correct = 1;

        function sync() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'reveal') {
                    if(lastIdx !== data.current_q_index) {
                        lastIdx = data.current_q_index; answered = false; start = 0;
                        correct = data.question.correct_answer;
                    }
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerText = "CONCENTREZ-VOUS...";
                } else if (data.status === 'playing' && !answered) {
                    document.getElementById('msg').innerText = "VITE !";
                    document.getElementById('txt1').innerText = data.question.opt1;
                    document.getElementById('txt2').innerText = data.question.opt2;
                    document.getElementById('txt3').innerText = data.question.opt3;
                    document.getElementById('txt4').innerText = data.question.opt4;
                    document.getElementById('grid').classList.remove('hidden');
                    if(start === 0) start = Date.now();
                } else if (data.status === 'leaderboard') {
                    document.getElementById('grid').classList.add('hidden');
                    document.getElementById('msg').innerText = "REGARDEZ LE MAÎTRE !";
                }
            });
        }

        function submitAns(num) {
            if(answered) return;
            answered = true;
            const time = (Date.now() - start) / 1000;
            fetch(`api_live.php?action=submit_answer&pin=${pin}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname: nick, is_correct: (num == correct), response_time: time, answer_index: num })
            });
            document.getElementById('grid').classList.add('hidden');
            document.getElementById('msg').innerText = "ENVOYÉ !";
        }
        setInterval(sync, 1500);
    </script>
</body>
</html>