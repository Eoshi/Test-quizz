<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Master</title>
    <style>
        .zoom-question { animation: zoomIn 3s ease-out forwards; }
        @keyframes zoomIn { from { transform: scale(0.2); opacity: 0; } to { transform: scale(1.5); opacity: 1; } }
        .av-container { position: relative; width: 60px; height: 60px; }
        .av-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; z-index: 10; }
        .aura-layer { position: absolute; top: -10px; left: -10px; width: 80px; height: 80px; z-index: 1; animation: rot 4s linear infinite; }
        @keyframes rot { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-indigo-700 min-h-screen text-white font-sans overflow-hidden">

    <div id="reveal-ui" class="hidden h-screen flex items-center justify-center p-10 bg-indigo-800">
        <h1 id="reveal-text" class="text-6xl font-black italic text-center zoom-question"></h1>
    </div>

    <div id="question-ui" class="hidden flex flex-col h-screen">
        <div class="bg-white text-gray-900 p-6 text-center shadow-xl">
            <h1 id="q-text" class="text-4xl font-black italic"></h1>
        </div>
        <div id="classroom" class="flex-grow flex flex-wrap content-center justify-center gap-4 p-6"></div>
        <div class="flex items-center justify-around py-6 bg-indigo-900 border-t border-indigo-400">
            <div id="timer" class="text-6xl font-black bg-white text-indigo-700 w-28 h-28 rounded-full flex items-center justify-center border-4 border-white shadow-2xl">0</div>
            <div class="text-center"><p id="ans-count" class="text-7xl font-black">0</p><p class="text-sm font-bold opacity-50 uppercase">Réponses</p></div>
        </div>
        <div class="grid grid-cols-2 gap-4 p-4 h-1/4">
            <div class="bg-red-500 rounded-xl p-4 text-2xl font-bold flex items-center">▲ <span id="opt1" class="ml-4"></span></div>
            <div class="bg-blue-500 rounded-xl p-4 text-2xl font-bold flex items-center">◆ <span id="opt2" class="ml-4"></span></div>
            <div class="bg-yellow-500 rounded-xl p-4 text-2xl font-bold flex items-center">● <span id="opt3" class="ml-4"></span></div>
            <div class="bg-green-500 rounded-xl p-4 text-2xl font-bold flex items-center">■ <span id="opt4" class="ml-4"></span></div>
        </div>
    </div>

    <script>
        const pin = "<?= $_GET['pin'] ?>";
        let localStatus = ""; let currentQIdx = -1; let timerVal = 0; let timerInterval; let answeredList = [];

        function renderAv(p, s="w-16 h-16") {
            let aura = p.aura > 0 ? `<img src="personnage/aura/aura${p.aura}.png" class="aura-layer">` : '';
            return `<div class="av-container ${s} mx-auto">${aura}<img src="personnage/tenue/tenue${p.outfit}.png" class="av-layer"><img src="personnage/cheveux/cheveux${p.hair}.png" class="av-layer" style="z-index:20"></div>`;
        }

        function update() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status !== localStatus) {
                    localStatus = data.status;
                    if (data.status === 'reveal') showReveal(data);
                    if (data.status === 'playing') showQuestion(data);
                }
                if (data.status === 'playing') {
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
            });
        }

        function showReveal(data) {
            document.getElementById('question-ui').classList.add('hidden');
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
            timerVal = parseInt(data.question.timer);
            timerInterval = setInterval(() => {
                document.getElementById('timer').innerText = timerVal;
                if (timerVal <= 0) { clearInterval(timerInterval); fetch(`api_live.php?action=show_leaderboard&pin=${pin}`); }
                timerVal--;
            }, 1000);
        }

        setInterval(update, 1500);
    </script>
</body>
</html>