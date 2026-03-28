<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Master</title>
    <style>
        .zoom-question { animation: zoomIn 3s ease-out forwards; }
        @keyframes zoomIn { from { transform: scale(0.2); opacity: 0; } to { transform: scale(1.4); opacity: 1; } }
        .av-container { position: relative; width: 60px; height: 60px; }
        .av-layer { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; z-index: 10; }
        .aura-master { position: absolute; inset: -15%; width: 130%; height: 130%; animation: spin 4s linear infinite; z-index: 1; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .badge-vip { position: absolute; bottom: -2px; right: -2px; background: gold; color: black; border-radius: 50%; width: 16px; height: 16px; font-size: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 1px solid white; z-index: 30; }
    </style>
</head>
<body class="bg-indigo-700 min-h-screen text-white font-sans overflow-hidden">

    <div id="reveal-ui" class="hidden h-screen flex items-center justify-center p-10 bg-indigo-900">
        <h1 id="reveal-text" class="text-6xl font-black italic text-center zoom-question"></h1>
    </div>

    <div id="question-ui" class="hidden flex flex-col h-screen">
        <div class="bg-white text-gray-900 p-6 text-center shadow-xl z-20"><h1 id="q-text" class="text-4xl font-black italic"></h1></div>
        <div id="classroom" class="flex-grow flex flex-wrap content-center justify-center gap-4 p-6 bg-black bg-opacity-10"></div>
        <div class="flex items-center justify-around py-6 bg-indigo-900 border-t border-indigo-400">
            <div id="timer" class="text-6xl font-black bg-white text-indigo-700 w-28 h-28 rounded-full flex items-center justify-center border-4 border-white shadow-2xl">0</div>
            <div class="text-center"><p id="ans-count" class="text-7xl font-black">0</p><p class="text-xs font-bold opacity-50 uppercase">Réponses</p></div>
        </div>
        <div class="grid grid-cols-2 gap-4 p-4 h-1/4">
            <div class="bg-red-500 rounded-xl p-4 text-2xl font-bold flex items-center shadow-lg">▲ <span id="opt1" class="ml-4"></span></div>
            <div class="bg-blue-500 rounded-xl p-4 text-2xl font-bold flex items-center shadow-lg">◆ <span id="opt2" class="ml-4"></span></div>
            <div class="bg-yellow-500 rounded-xl p-4 text-2xl font-bold flex items-center shadow-lg">● <span id="opt3" class="ml-4"></span></div>
            <div class="bg-green-500 rounded-xl p-4 text-2xl font-bold flex items-center shadow-lg">■ <span id="opt4" class="ml-4"></span></div>
        </div>
    </div>

    <div id="leaderboard-ui" class="h-screen hidden flex flex-col items-center justify-center bg-indigo-900 p-10">
        <h1 class="text-5xl font-black text-yellow-400 mb-8 italic uppercase">Classement</h1>
        <div id="score-list" class="w-full max-w-xl space-y-3"></div>
        <button onclick="nextStep()" class="mt-8 bg-green-500 text-white px-12 py-4 rounded-full font-black text-2xl shadow-2xl transition hover:scale-105">SUIVANT</button>
    </div>

    <script>
        const pin = "<?= $_GET['pin'] ?>";
        let localStatus = ""; let currentQIdx = -1; let timerInterval; let answeredList = [];

        function update() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                // Détection d'un changement d'état ou de question
                if (data.status !== localStatus || data.current_q_index !== currentQIdx) {
                    localStatus = data.status;
                    currentQIdx = data.current_q_index;
                    
                    if (data.status === 'reveal') {
                        document.getElementById('question-ui').classList.add('hidden');
                        document.getElementById('leaderboard-ui').classList.add('hidden');
                        document.getElementById('reveal-ui').classList.remove('hidden');
                        document.getElementById('reveal-text').innerText = data.question.question_text;
                        setTimeout(() => { fetch(`api_live.php?action=activate_playing&pin=${pin}`); }, 3000);
                    }
                    if (data.status === 'playing') {
                        document.getElementById('reveal-ui').classList.add('hidden');
                        document.getElementById('leaderboard-ui').classList.add('hidden');
                        document.getElementById('question-ui').classList.remove('hidden');
                        startChrono(data);
                    }
                    if (data.status === 'leaderboard') {
                        document.getElementById('question-ui').classList.add('hidden');
                        document.getElementById('leaderboard-ui').classList.remove('hidden');
                        showLeaderboard(data);
                    }
                }
                if (data.status === 'playing') updateClassroom(data);
            });
        }

        function startChrono(data) {
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

        function updateClassroom(data) {
            const nicks = Object.keys(data.answers[data.current_q_index] || {});
            document.getElementById('ans-count').innerText = nicks.length;
            const classroom = document.getElementById('classroom');
            nicks.forEach(n => {
                if(!answeredList.includes(n)) {
                    const p = data.players.find(x => x.nickname === n);
                    if(p) {
                        let aura = p.aura > 0 ? `<img src="personnage/aura/aura${p.aura}.png" class="aura-master">` : '';
                        let badge = p.is_member ? '<div class="badge-vip">★</div>' : '';
                        classroom.innerHTML += `<div class="text-center p-2">
                            <div class="av-container mx-auto">${aura}<img src="personnage/tenue/tenue${p.outfit}.png" class="av-layer"><img src="personnage/cheveux/cheveux${p.hair}.png" class="av-layer" style="z-index:20">${badge}</div>
                            <p class="text-[10px] font-bold mt-1 uppercase">${p.nickname}</p>
                        </div>`;
                    }
                    answeredList.push(n);
                }
            });
        }

        function showLeaderboard(data) {
            const list = document.getElementById('score-list');
            list.innerHTML = "";
            Object.entries(data.scores).sort((a,b) => b[1]-a[1]).slice(0,5).forEach(([nick, pts]) => {
                const p = data.players.find(x => x.nickname === nick);
                list.innerHTML += `<div class="bg-indigo-800 p-3 rounded-2xl flex justify-between items-center border-l-8 border-yellow-400">
                    <div class="flex items-center gap-4"><span class="font-bold uppercase italic">${nick}</span></div>
                    <span class="text-2xl font-black">${pts} pts</span></div>`;
            });
        }

        function nextStep() { fetch(`api_live.php?action=next_step&pin=${pin}`); }
        setInterval(update, 1500);
    </script>
</body>
</html>