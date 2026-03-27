<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Master</title>
</head>
<body class="bg-indigo-700 min-h-screen text-white font-sans overflow-hidden">
    <div id="question-ui" class="flex flex-col h-screen">
        <div class="bg-white text-gray-900 p-6 text-center shadow-xl">
            <h1 id="q-text" class="text-3xl font-bold"></h1>
        </div>
        <div class="flex-grow flex items-center justify-around">
            <div class="text-6xl font-black bg-indigo-800 w-32 h-32 rounded-full flex items-center justify-center border-4 border-white" id="timer">0</div>
            <div class="text-center">
                <p id="ans-count" class="text-8xl font-black">0</p>
                <p class="text-xl font-bold text-indigo-200">RÉPONSES</p>
            </div>
        </div>
        <div id="answers-grid" class="grid grid-cols-2 gap-4 p-4 h-1/3">
            <div class="bg-red-500 p-4 rounded-lg flex items-center text-xl font-bold">▲ <span id="opt1" class="ml-4"></span></div>
            <div class="bg-blue-500 p-4 rounded-lg flex items-center text-xl font-bold">◆ <span id="opt2" class="ml-4"></span></div>
            <div class="bg-yellow-500 p-4 rounded-lg flex items-center text-xl font-bold">● <span id="opt3" class="ml-4"></span></div>
            <div class="bg-green-500 p-4 rounded-lg flex items-center text-xl font-bold">■ <span id="opt4" class="ml-4"></span></div>
        </div>
    </div>

    <div id="leaderboard-ui" class="h-screen hidden flex flex-col items-center justify-center">
        <h1 class="text-5xl font-black text-yellow-400 mb-8 italic">CLASSEMENT</h1>
        <div id="score-list" class="w-full max-w-xl space-y-2"></div>
        <button onclick="nextStep()" class="mt-8 bg-green-500 px-10 py-4 rounded-full font-bold text-xl hover:scale-110 transition">QUESTION SUIVANTE</button>
    </div>

    <script>
        const pin = "<?= $_GET['pin'] ?>";
        let status = "lobby";
        let timerVal = 0;
        let timerInterval;

        function update() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'playing') {
                    const count = Object.keys(data.answers[data.current_q_index] || {}).length;
                    document.getElementById('ans-count').innerText = count;
                    if (status !== 'playing') showQ(data);
                }
                if (data.status === 'leaderboard' && status !== 'leaderboard') showL(data.scores);
                status = data.status;
            });
        }

        function showQ(data) {
            const q = data.question;
            document.getElementById('leaderboard-ui').classList.add('hidden');
            document.getElementById('question-ui').classList.remove('hidden');
            document.getElementById('q-text').innerText = q.question_text;
            document.getElementById('opt1').innerText = q.opt1; document.getElementById('opt2').innerText = q.opt2;
            document.getElementById('opt3').innerText = q.opt3; document.getElementById('opt4').innerText = q.opt4;
            
            clearInterval(timerInterval);
            timerVal = parseInt(q.timer);
            timerInterval = setInterval(() => {
                document.getElementById('timer').innerText = timerVal;
                if (timerVal <= 0) {
                    clearInterval(timerInterval);
                    fetch(`api_live.php?action=show_leaderboard&pin=${pin}`);
                }
                timerVal--;
            }, 1000);
        }

        function showL(scores) {
            document.getElementById('question-ui').classList.add('hidden');
            document.getElementById('leaderboard-ui').classList.remove('hidden');
            const list = document.getElementById('score-list');
            list.innerHTML = "";
            Object.entries(scores).sort((a,b) => b[1]-a[1]).slice(0,5).forEach(([name, pts]) => {
                list.innerHTML += `<div class="bg-indigo-900 p-4 rounded-lg flex justify-between text-2xl font-bold"><span>${name}</span><span>${pts}</span></div>`;
            });
        }

        function nextStep() {
            fetch(`api_live.php?action=next_step&pin=${pin}`).then(() => status = "transition");
        }
        setInterval(update, 1500);
    </script>
</body>
</html>