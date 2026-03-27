<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Master</title>
    <style>
        .player-card { width: 70px; animation: pop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        @keyframes pop { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .av-container { position: relative; width: 60px; height: 60px; margin: 0 auto; }
        .av-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; }
    </style>
</head>
<body class="bg-indigo-700 min-h-screen text-white font-sans overflow-hidden">

    <div id="question-ui" class="flex flex-col h-screen">
        <div class="bg-white text-gray-900 p-6 text-center shadow-xl z-20">
            <h1 id="q-text" class="text-3xl font-black italic text-indigo-800"></h1>
        </div>
        
        <div id="classroom" class="flex-grow flex flex-wrap content-center justify-center gap-4 p-6 bg-black bg-opacity-10">
            </div>

        <div class="flex items-center justify-around py-4 bg-indigo-900 border-t border-indigo-500 z-20">
            <div id="timer" class="text-5xl font-black bg-white text-indigo-700 w-24 h-24 rounded-full flex items-center justify-center border-4 border-indigo-300 shadow-2xl">0</div>
            <div class="text-center">
                <p id="ans-count" class="text-6xl font-black">0</p>
                <p class="text-xs font-bold text-indigo-200 uppercase tracking-tighter">Réponses reçues</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 p-2 h-1/4 z-20">
            <div class="bg-red-500 rounded-xl flex items-center p-4 text-xl font-bold shadow-lg">▲ <span id="opt1" class="ml-3"></span></div>
            <div class="bg-blue-500 rounded-xl flex items-center p-4 text-xl font-bold shadow-lg">◆ <span id="opt2" class="ml-3"></span></div>
            <div class="bg-yellow-500 rounded-xl flex items-center p-4 text-xl font-bold shadow-lg">● <span id="opt3" class="ml-3"></span></div>
            <div class="bg-green-500 rounded-xl flex items-center p-4 text-xl font-bold shadow-lg">■ <span id="opt4" class="ml-3"></span></div>
        </div>
    </div>

    <div id="leaderboard-ui" class="h-screen hidden flex flex-col items-center justify-center bg-indigo-900 p-10">
        <h1 id="leaderboard-title" class="text-5xl font-black text-yellow-400 mb-10 italic uppercase">Classement</h1>
        <div id="score-list" class="w-full max-w-xl space-y-3"></div>
        <div class="flex gap-4">
            <button id="next-btn" onclick="nextStep()" class="mt-8 bg-green-500 text-white px-10 py-4 rounded-full font-black text-xl hover:scale-110 shadow-2xl transition">SUIVANT</button>
            <a id="home-btn" href="dashboard.php" class="hidden mt-8 bg-white text-indigo-900 px-10 py-4 rounded-full font-black text-xl shadow-2xl transition">QUITTER</a>
        </div>
    </div>

    <script>
        const pin = "<?= $_GET['pin'] ?>";
        let localStatus = "";
        let timerVal = 0; let timerInterval;
        let answeredList = [];

        function createAvatar(p) {
            return `
                <div class="player-card">
                    <div class="av-container">
                        <img src="personnage/tenue/tenue${p.outfit}.png" class="av-layer">
                        <img src="personnage/cheveux/cheveux${p.hair}.png" class="av-layer">
                    </div>
                    <p class="text-[10px] font-bold mt-1 truncate">${p.nickname}</p>
                </div>`;
        }

        function update() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'playing') {
                    const answers = data.answers[data.current_q_index] || {};
                    const nicks = Object.keys(answers);
                    document.getElementById('ans-count').innerText = nicks.length;
                    
                    const classroom = document.getElementById('classroom');
                    nicks.forEach(n => {
                        if(!answeredList.includes(n)) {
                            const p = data.players.find(x => x.nickname === n);
                            if(p) classroom.innerHTML += createAvatar(p);
                            answeredList.push(n);
                        }
                    });
                }

                if (data.status !== localStatus) {
                    if (data.status === 'playing') { 
                        answeredList = []; 
                        document.getElementById('classroom').innerHTML = ""; 
                        showQuestion(data); 
                    }
                    if (data.status === 'leaderboard') showLeaderboard(data);
                    if (data.status === 'finished') showLeaderboard(data, "PODIUM FINAL");
                    localStatus = data.status;
                }
            });
        }

        function showQuestion(data) {
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
                if (timerVal <= 0) { clearInterval(timerInterval); fetch(`api_live.php?action=show_leaderboard&pin=${pin}`); }
                timerVal--;
            }, 1000);
        }

        function showLeaderboard(data, title="CLASSEMENT") {
            document.getElementById('question-ui').classList.add('hidden');
            document.getElementById('leaderboard-ui').classList.remove('hidden');
            document.getElementById('leaderboard-title').innerText = title;
            if(title === "PODIUM FINAL") { document.getElementById('next-btn').classList.add('hidden'); document.getElementById('home-btn').classList.remove('hidden'); }

            const list = document.getElementById('score-list');
            list.innerHTML = "";
            const sorted = Object.entries(data.scores).sort((a,b) => b[1]-a[1]).slice(0,5);
            sorted.forEach(([nick, pts]) => {
                const p = data.players.find(x => x.nickname === nick);
                list.innerHTML += `
                    <div class="bg-indigo-800 p-3 rounded-2xl flex items-center justify-between border-l-8 border-yellow-400">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 relative bg-white bg-opacity-10 rounded-full">
                                <img src="personnage/tenue/tenue${p.outfit}.png" class="absolute inset-0 w-full h-full object-contain">
                                <img src="personnage/cheveux/cheveux${p.hair}.png" class="absolute inset-0 w-full h-full object-contain">
                            </div>
                            <span class="text-xl font-bold italic">${nick}</span>
                        </div>
                        <span class="text-2xl font-black">${pts} pts</span>
                    </div>`;
            });
        }

        function nextStep() { fetch(`api_live.php?action=next_step&pin=${pin}`).then(() => update()); }
        setInterval(update, 1500);
    </script>
</body>
</html>