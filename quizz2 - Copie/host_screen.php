<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Master Live</title>
    <style>
        .zoom-question { animation: zoomIn 3s ease-out forwards; }
        @keyframes zoomIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1.2); opacity: 1; } }
        .av-container { position: relative; width: 60px; height: 60px; }
        .av-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; }
        .aura-layer { position: absolute; top: -10px; left: -10px; width: 80px; height: 80px; z-index: 0; animation: rotate 4s linear infinite; }
        @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .badge-v { position: absolute; bottom: 0; right: 0; background: #fbbf24; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 2px solid white; color: white; z-index: 10; }
    </style>
</head>
<body class="bg-indigo-700 min-h-screen text-white font-sans overflow-hidden">

    <div id="reveal-ui" class="hidden h-screen flex items-center justify-center p-10">
        <h1 id="reveal-text" class="text-6xl font-black italic text-center zoom-question"></h1>
    </div>

    <div id="question-ui" class="hidden flex flex-col h-screen">
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
        <div id="grid-ans" class="grid grid-cols-2 gap-2 p-2 h-1/4 z-20">
            <div class="bg-red-500 rounded-xl flex items-center p-4 text-xl font-bold">▲ <span id="opt1" class="ml-3"></span></div>
            <div class="bg-blue-500 rounded-xl flex items-center p-4 text-xl font-bold">◆ <span id="opt2" class="ml-3"></span></div>
            <div class="bg-yellow-500 rounded-xl flex items-center p-4 text-xl font-bold">● <span id="opt3" class="ml-3"></span></div>
            <div class="bg-green-500 rounded-xl flex items-center p-4 text-xl font-bold">■ <span id="opt4" class="ml-3"></span></div>
        </div>
    </div>

    <div id="leaderboard-ui" class="h-screen hidden flex flex-col items-center justify-center bg-indigo-900 p-10 relative">
        <h1 id="l-title" class="text-5xl font-black text-yellow-400 mb-8 italic uppercase text-center">Classement</h1>
        <div id="score-list" class="w-full max-w-xl space-y-3"></div>
        <button onclick="nextStep()" id="next-btn" class="mt-8 bg-green-500 text-white px-12 py-4 rounded-full font-black text-2xl shadow-2xl transition">SUIVANT</button>
    </div>

    <script>
        const pin = "<?= $_GET['pin'] ?>";
        let localStatus = ""; let currentQIdx = -1; let timerVal = 0; let timerInterval; let answeredList = [];

        function renderAv(p, s="w-16 h-16") {
            let auraImg = p.aura > 0 ? `<img src="personnage/aura/aura${p.aura}.png" class="aura-layer">` : '';
            let badge = p.is_member ? `<div class="badge-v">★</div>` : '';
            return `<div class="av-container ${s} mx-auto">
                ${auraImg}
                <img src="personnage/tenue/tenue${p.outfit}.png" class="av-layer">
                <img src="personnage/cheveux/cheveux${p.hair}.png" class="av-layer" style="z-index:2">
                ${badge}
            </div>`;
        }

        function update() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'reveal' && localStatus !== 'reveal') {
                    localStatus = 'reveal';
                    startReveal(data);
                }
                if (data.status === 'playing') {
                    const nicks = Object.keys(data.answers[data.current_q_index] || {});
                    document.getElementById('ans-count').innerText = nicks.length;
                    const classroom = document.getElementById('classroom');
                    nicks.forEach(n => {
                        if(!answeredList.includes(n)) {
                            const p = data.players.find(x => x.nickname === n);
                            if(p) classroom.innerHTML += `<div class="text-center">${renderAv(p, "w-12 h-12")}<p class="text-[8px] font-bold uppercase mt-1">${p.nickname}</p></div>`;
                            answeredList.push(n);
                        }
                    });
                }
                if (data.status !== localStatus && data.status !== 'reveal') {
                    if (data.status === 'playing') showQuestion(data);
                    if (data.status === 'leaderboard') showLeaderboard(data);
                    localStatus = data.status;
                }
            });
        }

        function startReveal(data) {
            document.getElementById('question-ui').classList.add('hidden');
            document.getElementById('leaderboard-ui').classList.add('hidden');
            document.getElementById('reveal-ui').classList.remove('hidden');
            document.getElementById('reveal-text').innerText = data.question.question_text;
            
            // On attend 3 secondes avant d'activer le chrono via l'API
            setTimeout(() => {
                fetch(`api_live.php?action=activate_playing&pin=${pin}`);
            }, 3000);
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
            timerVal = parseInt(data.question.timer);
            timerInterval = setInterval(() => {
                document.getElementById('timer').innerText = timerVal;
                if (timerVal <= 0) { clearInterval(timerInterval); fetch(`api_live.php?action=show_leaderboard&pin=${pin}`); }
                timerVal--;
            }, 1000);
        }

        function showLeaderboard(data) {
            document.getElementById('question-ui').classList.add('hidden');
            document.getElementById('leaderboard-ui').classList.remove('hidden');
            const list = document.getElementById('score-list');
            list.innerHTML = "";
            Object.entries(data.scores).sort((a,b) => b[1]-a[1]).slice(0,5).forEach(([nick, pts]) => {
                const p = data.players.find(x => x.nickname === nick);
                list.innerHTML += `<div class="bg-indigo-800 p-3 rounded-2xl flex justify-between items-center border-l-8 border-yellow-400">
                    <div class="flex items-center gap-4">${renderAv(p, "w-12 h-12")} <span class="font-bold uppercase italic">${nick}</span></div>
                    <span class="text-2xl font-black">${pts} pts</span></div>`;
            });
        }

        function nextStep() { fetch(`api_live.php?action=next_step&pin=${pin}`); }
        setInterval(update, 1500);
    </script>
</body>
</html>