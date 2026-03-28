<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Master</title>
    <style>
        @keyframes fall { 0% { transform: translateY(-100vh); opacity: 1; } 100% { transform: translateY(100vh); opacity: 0; } }
        .falling-player { animation: fall 1.5s ease-in forwards; position: absolute; top: 0; }
        .podium-step { transition: all 1s ease-out; opacity: 0; transform: translateY(50px); }
        .podium-visible { opacity: 1; transform: translateY(0); }
        .winner-dark { filter: brightness(0); transition: filter 0.5s ease-in-out; }
        .winner-bright { filter: brightness(1); }
        .av-container { position: relative; width: 100px; height: 100px; margin: 0 auto; }
        .av-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; }
    </style>
</head>
<body class="bg-indigo-700 min-h-screen text-white font-sans overflow-hidden">

    <div id="question-ui" class="flex flex-col h-screen">
        <div class="bg-white text-gray-900 p-4 text-center shadow-xl z-20">
            <h1 id="q-text" class="text-2xl font-black italic text-indigo-800"></h1>
        </div>
        <div id="classroom" class="flex-grow flex flex-wrap content-center justify-center gap-4 p-6 bg-black bg-opacity-10"></div>
        <div class="flex items-center justify-around py-4 bg-indigo-900 border-t border-indigo-500 z-20">
            <div id="timer" class="text-5xl font-black bg-white text-indigo-700 w-24 h-24 rounded-full flex items-center justify-center border-4 border-indigo-300">0</div>
            <div class="text-center">
                <p id="ans-count" class="text-6xl font-black">0</p>
                <p class="text-xs font-bold text-indigo-200 uppercase">Réponses</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 p-2 h-1/4 z-20">
            <div class="bg-red-500 rounded-xl flex items-center p-4 text-xl font-bold">▲ <span id="opt1" class="ml-3"></span></div>
            <div class="bg-blue-500 rounded-xl flex items-center p-4 text-xl font-bold">◆ <span id="opt2" class="ml-3"></span></div>
            <div class="bg-yellow-500 rounded-xl flex items-center p-4 text-xl font-bold">● <span id="opt3" class="ml-3"></span></div>
            <div class="bg-green-500 rounded-xl flex items-center p-4 text-xl font-bold">■ <span id="opt4" class="ml-3"></span></div>
        </div>
    </div>

    <div id="leaderboard-ui" class="h-screen hidden flex flex-col items-center justify-center bg-indigo-900 p-10 relative">
        <div id="falling-zone" class="absolute inset-0 pointer-events-none"></div>
        <h1 id="l-title" class="text-5xl font-black text-yellow-400 mb-8 italic uppercase">Classement</h1>
        <div id="score-list" class="w-full max-w-xl space-y-3 z-10"></div>
        
        <div id="final-podium" class="hidden w-full flex justify-center items-end gap-4 mt-10">
            <div id="rank-2" class="podium-step flex flex-col items-center"><div id="av-2" class="av-container"></div><div class="bg-gray-400 w-32 h-40 rounded-t-lg flex items-center justify-center font-black text-4xl text-gray-700">2</div></div>
            <div id="rank-1" class="podium-step flex flex-col items-center"><div id="av-1" class="av-container winner-dark"></div><div class="bg-yellow-400 w-40 h-56 rounded-t-lg flex items-center justify-center font-black text-6xl text-yellow-700">1</div></div>
            <div id="rank-3" class="podium-step flex flex-col items-center"><div id="av-3" class="av-container"></div><div class="bg-orange-600 w-32 h-24 rounded-t-lg flex items-center justify-center font-black text-4xl text-orange-800">3</div></div>
        </div>

        <div class="flex gap-4 z-20 mt-10">
            <button id="next-btn" onclick="nextStep()" class="bg-green-500 text-white px-12 py-4 rounded-full font-black text-2xl hover:scale-110 shadow-2xl transition">SUIVANT</button>
            <a id="home-btn" href="dashboard.php" class="hidden bg-white text-indigo-900 px-12 py-4 rounded-full font-black text-2xl shadow-2xl transition">QUITTER</a>
        </div>
    </div>

    <script>
        const pin = "<?= $_GET['pin'] ?>";
        let localStatus = "";
        let timerVal = 0; let timerInterval;
        let answeredList = [];

        function renderAv(p, s="w-16 h-16") {
            return `<div class="relative ${s}"><img src="personnage/tenue/tenue${p.outfit}.png" class="av-layer"><img src="personnage/cheveux/cheveux${p.hair}.png" class="av-layer"></div>`;
        }

        function update() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'playing') {
                    const nicks = Object.keys(data.answers[data.current_q_index] || {});
                    document.getElementById('ans-count').innerText = nicks.length;
                    const classroom = document.getElementById('classroom');
                    nicks.forEach(n => {
                        if(!answeredList.includes(n)) {
                            const p = data.players.find(x => x.nickname === n);
                            if(p) classroom.innerHTML += `<div class="text-center">${renderAv(p)}<p class="text-[10px] font-bold">${p.nickname}</p></div>`;
                            answeredList.push(n);
                        }
                    });
                }
                if (data.status !== localStatus) {
                    localStatus = data.status;
                    if (data.status === 'playing') { answeredList = []; document.getElementById('classroom').innerHTML = ""; showQuestion(data); }
                    if (data.status === 'leaderboard') showLeaderboard(data);
                    if (data.status === 'finished') startFinalPodium(data);
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

        function showLeaderboard(data) {
            document.getElementById('question-ui').classList.add('hidden');
            document.getElementById('leaderboard-ui').classList.remove('hidden');
            document.getElementById('next-btn').classList.remove('hidden');
            const list = document.getElementById('score-list');
            list.innerHTML = "";
            Object.entries(data.scores).sort((a,b) => b[1]-a[1]).slice(0,5).forEach(([nick, pts]) => {
                const p = data.players.find(x => x.nickname === nick);
                list.innerHTML += `<div class="bg-indigo-800 p-3 rounded-xl flex justify-between items-center border-l-4 border-yellow-400">
                    <div class="flex items-center gap-2">${renderAv(p, "w-10 h-10")} <span class="font-bold">${nick}</span></div>
                    <span class="font-black">${pts} pts</span></div>`;
            });
        }

        function startFinalPodium(data) {
            document.getElementById('l-title').innerText = "PODIUM FINAL";
            document.getElementById('score-list').classList.add('hidden');
            document.getElementById('next-btn').classList.add('hidden');
            document.getElementById('home-btn').classList.remove('hidden');
            document.getElementById('final-podium').classList.remove('hidden');
            
            const sorted = Object.entries(data.scores).sort((a,b) => b[1]-a[1]);
            const top3 = sorted.slice(0, 3);
            
            if(top3[2]) {
                const p3 = data.players.find(x => x.nickname === top3[2][0]);
                document.getElementById('av-3').innerHTML = renderAv(p3, "w-24 h-24");
                setTimeout(() => document.getElementById('rank-3').classList.add('podium-visible'), 500);
            }
            if(top3[1]) {
                const p2 = data.players.find(x => x.nickname === top3[1][0]);
                document.getElementById('av-2').innerHTML = renderAv(p2, "w-24 h-24");
                setTimeout(() => document.getElementById('rank-2').classList.add('podium-visible'), 2500);
            }
            if(top3[0]) {
                const p1 = data.players.find(x => x.nickname === top3[0][0]);
                const av1 = document.getElementById('av-1');
                av1.innerHTML = renderAv(p1, "w-32 h-32");
                setTimeout(() => {
                    document.getElementById('rank-1').classList.add('podium-visible');
                    setTimeout(() => av1.classList.remove('winner-dark'), 1000);
                }, 7500);
            }
        }

        function nextStep() { fetch(`api_live.php?action=next_step&pin=${pin}`); }
        setInterval(update, 1500);
    </script>
</body>
</html>