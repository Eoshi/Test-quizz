<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Master</title>
    <style>
        @keyframes physicsFall { 0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; } 100% { transform: translateY(120vh) rotate(20deg); opacity: 0; } }
        .falling-player { animation: physicsFall 2s ease-in forwards; position: absolute; top: 0; z-index: 50; }
        .podium-step { transition: all 1s ease-out; opacity: 0; transform: translateY(100px); }
        .podium-visible { opacity: 1; transform: translateY(0); }
        .winner-dark { filter: brightness(0); transition: filter 1s ease-in-out; }
        .av-container { position: relative; width: 60px; height: 60px; margin: 0 auto; }
        .av-layer { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; }
        /* Aura en PREMIER PLAN (z-30) et STATIQUE */
        .aura-static { position: absolute; inset: -10%; width: 120%; height: 120%; z-index: 30; pointer-events: none; }
    </style>
</head>
<body class="bg-indigo-700 min-h-screen text-white font-sans overflow-hidden">

    <div id="reveal-ui" class="hidden h-screen flex items-center justify-center p-10 bg-indigo-900">
        <h1 id="reveal-text" class="text-6xl font-black italic text-center zoom-question"></h1>
    </div>

    <div id="question-ui" class="hidden flex flex-col h-screen">
        <div class="bg-white text-gray-900 p-4 text-center shadow-xl z-20"><h1 id="q-text" class="text-3xl font-black italic"></h1></div>
        <div id="classroom" class="flex-grow flex flex-wrap content-center justify-center gap-4 p-6"></div>
        <div class="flex items-center justify-around py-4 bg-indigo-900 border-t border-indigo-500 z-20">
            <div id="timer" class="text-5xl font-black bg-white text-indigo-700 w-24 h-24 rounded-full flex items-center justify-center border-4 border-indigo-300 shadow-2xl">0</div>
            <div class="text-center"><p id="ans-count" class="text-6xl font-black">0</p><p class="text-xs font-bold opacity-50">Réponses</p></div>
        </div>
        <div class="grid grid-cols-2 gap-2 p-2 h-1/4">
            <div class="bg-red-500 rounded-xl p-4 text-xl font-bold italic shadow-lg">▲ <span id="opt1" class="ml-3"></span></div>
            <div class="bg-blue-500 rounded-xl p-4 text-xl font-bold italic shadow-lg">◆ <span id="opt2" class="ml-3"></span></div>
            <div class="bg-yellow-500 rounded-xl p-4 text-xl font-bold italic shadow-lg">● <span id="opt3" class="ml-3"></span></div>
            <div class="bg-green-500 rounded-xl p-4 text-xl font-bold italic shadow-lg">■ <span id="opt4" class="ml-3"></span></div>
        </div>
    </div>

    <div id="leaderboard-ui" class="h-screen hidden flex flex-col items-center justify-center bg-indigo-900 p-10 relative">
        <div id="falling-zone" class="absolute inset-0 pointer-events-none"></div>
        <h1 id="l-title" class="text-5xl font-black text-yellow-400 mb-8 italic uppercase">Classement</h1>
        <div id="score-list" class="w-full max-w-xl space-y-3 z-10"></div>
        
        <div id="final-podium" class="hidden w-full flex justify-center items-end gap-4 mt-10">
            <div id="rank-2" class="podium-step flex flex-col items-center"><div id="av-2" class="av-container"></div><div class="bg-gray-400 w-32 h-40 rounded-t-lg flex items-center justify-center font-black text-4xl text-gray-700 shadow-2xl">2</div></div>
            <div id="rank-1" class="podium-step flex flex-col items-center"><div id="av-1" class="av-container winner-dark"></div><div class="bg-yellow-400 w-40 h-56 rounded-t-lg flex items-center justify-center font-black text-6xl text-yellow-700 shadow-2xl">1</div></div>
            <div id="rank-3" class="podium-step flex flex-col items-center"><div id="av-3" class="av-container"></div><div class="bg-orange-600 w-32 h-24 rounded-t-lg flex items-center justify-center font-black text-4xl text-orange-800 shadow-2xl">3</div></div>
        </div>

        <div class="flex gap-4 mt-10 z-20">
            <button id="next-btn" onclick="nextStep()" class="bg-green-500 text-white px-12 py-4 rounded-full font-black text-2xl shadow-2xl hover:scale-110 transition">SUIVANT</button>
            <a id="home-btn" href="dashboard.php" class="hidden bg-white text-indigo-900 px-12 py-4 rounded-full font-black text-2xl shadow-2xl">QUITTER</a>
        </div>
    </div>

    <script>
        const pin = "<?= $_GET['pin'] ?>";
        let localStatus = ""; let currentQIdx = -1; let timerInterval; let answeredList = [];

        function renderAv(p, s="w-16 h-16") {
            let aura = p.aura > 0 ? `<img src="personnage/aura/aura${p.aura}.png" class="aura-static">` : '';
            return `<div class="av-container ${s} mx-auto">
                <img src="personnage/tenue/tenue${p.outfit}.png" class="av-layer" style="z-index:10">
                <img src="personnage/cheveux/cheveux${p.hair}.png" class="av-layer" style="z-index:20">
                ${aura}
            </div>`;
        }

        function update() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status !== localStatus || data.current_q_index !== currentQIdx) {
                    localStatus = data.status; currentQIdx = data.current_q_index;
                    if (data.status === 'reveal') showReveal(data);
                    if (data.status === 'playing') showQuestion(data);
                    if (data.status === 'leaderboard') showLeaderboard(data);
                    if (data.status === 'finished') startFinalPodium(data);
                }
                if (data.status === 'playing') updateLiveAnswers(data);
            });
        }

        function showReveal(data) {
            document.getElementById('question-ui').classList.add('hidden');
            document.getElementById('leaderboard-ui').classList.add('hidden');
            document.getElementById('reveal-ui').classList.remove('hidden');
            document.getElementById('reveal-text').innerText = data.question.question_text;
            setTimeout(() => { fetch(`api_live.php?action=activate_playing&pin=${pin}`); }, 3000);
        }

        function showQuestion(data) {
            document.getElementById('reveal-ui').classList.add('hidden');
            document.getElementById('question-ui').classList.remove('hidden');
            document.getElementById('q-text').innerText = data.question.question_text;
            document.getElementById('opt1').innerText = data.question.opt1;
            document.getElementById('opt2').innerText = data.question.opt2;
            document.getElementById('opt3').innerText = data.question.opt3;
            document.getElementById('opt4').innerText = data.question.opt4;
            answeredList = []; document.getElementById('classroom').innerHTML = "";
            clearInterval(timerInterval);
            let timerVal = parseInt(data.question.timer);
            timerInterval = setInterval(() => {
                document.getElementById('timer').innerText = timerVal;
                if (timerVal <= 0) { clearInterval(timerInterval); fetch(`api_live.php?action=show_leaderboard&pin=${pin}`); }
                timerVal--;
            }, 1000);
        }

        function updateLiveAnswers(data) {
            const nicks = Object.keys(data.answers[data.current_q_index] || {});
            document.getElementById('ans-count').innerText = nicks.length;
            const classroom = document.getElementById('classroom');
            nicks.forEach(n => {
                if(!answeredList.includes(n)) {
                    const p = data.players.find(x => x.nickname === n);
                    if(p) classroom.innerHTML += `<div class="text-center">${renderAv(p, "w-12 h-12")}<p class="text-[10px] font-bold mt-1 uppercase">${p.nickname}</p></div>`;
                    answeredList.push(n);
                }
            });
        }

        function showLeaderboard(data) {
            document.getElementById('question-ui').classList.add('hidden');
            document.getElementById('leaderboard-ui').classList.remove('hidden');
            const list = document.getElementById('score-list');
            list.innerHTML = "";
            Object.entries(data.scores).sort((a,b) => b[1]-a[1]).slice(0,5).forEach(([nick, pts]) => {
                const p = data.players.find(x => x.nickname === nick);
                list.innerHTML += `<div class="bg-indigo-800 p-3 rounded-2xl flex justify-between items-center border-l-8 border-yellow-400">
                    <div class="flex items-center gap-4">${renderAv(p, "w-10 h-10")} <span class="font-bold uppercase italic">${nick}</span></div>
                    <span class="text-2xl font-black">${pts} pts</span></div>`;
            });
        }

        function startFinalPodium(data) {
            document.getElementById('l-title').innerText = "PODIUM FINAL";
            document.getElementById('score-list').classList.add('hidden');
            document.getElementById('next-btn').classList.add('hidden');
            document.getElementById('final-podium').classList.remove('hidden');
            document.getElementById('home-btn').classList.remove('hidden');
            
            const sorted = Object.entries(data.scores).sort((a,b) => b[1]-a[1]);
            const top3 = sorted.slice(0, 3);
            const losers = sorted.slice(3);

            losers.forEach((l, i) => {
                const p = data.players.find(x => x.nickname === l[0]);
                const div = document.createElement('div');
                div.className = "falling-player"; div.style.left = (Math.random()*80+10)+"%";
                div.innerHTML = renderAv(p, "w-16 h-16");
                document.getElementById('falling-zone').appendChild(div);
            });

            if(top3[2]) {
                const p3 = data.players.find(x => x.nickname === top3[2][0]);
                document.getElementById('av-3').innerHTML = renderAv(p3, "w-24 h-24");
                setTimeout(() => document.getElementById('rank-3').classList.add('podium-visible'), 1000);
            }
            if(top3[1]) {
                const p2 = data.players.find(x => x.nickname === top3[1][0]);
                document.getElementById('av-2').innerHTML = renderAv(p2, "w-24 h-24");
                setTimeout(() => document.getElementById('rank-2').classList.add('podium-visible'), 3000);
            }
            if(top3[0]) {
                const p1 = data.players.find(x => x.nickname === top3[0][0]);
                const av1 = document.getElementById('av-1');
                av1.innerHTML = renderAv(p1, "w-32 h-32");
                setTimeout(() => {
                    document.getElementById('rank-1').classList.add('podium-visible');
                    setTimeout(() => av1.classList.remove('winner-dark'), 1500);
                }, 8000);
            }
        }

        function nextStep() { fetch(`api_live.php?action=next_step&pin=${pin}`); }
        setInterval(update, 1500);
    </script>
</body>
</html>