<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Master</title>
    <style>
        .player-avatar { display: inline-block; text-align: center; margin: 10px; transition: all 0.5s; }
        .photo-classe { position: absolute; bottom: 20px; left: 0; right: 0; display: flex; justify-content: center; flex-wrap: wrap; pointer-events: none; opacity: 0.6; }
    </style>
</head>
<body class="bg-indigo-700 min-h-screen text-white font-sans overflow-hidden">

    <div id="question-ui" class="flex flex-col h-screen relative">
        <div class="bg-white text-gray-900 p-6 text-center shadow-xl z-10">
            <h1 id="q-text" class="text-3xl font-bold italic text-indigo-800"></h1>
        </div>
        
        <div class="flex-grow flex items-center justify-around z-10">
            <div id="timer" class="text-6xl font-black bg-indigo-900 w-32 h-32 rounded-full flex items-center justify-center border-4 border-white shadow-2xl">0</div>
            <div class="text-center">
                <p id="ans-count" class="text-8xl font-black">0</p>
                <p class="text-xl font-bold text-indigo-200 uppercase">Réponses</p>
            </div>
        </div>

        <div id="classroom-overlay" class="photo-classe"></div>

        <div class="grid grid-cols-2 gap-4 p-4 h-1/3 z-10">
            <div class="bg-red-500 p-6 rounded-lg flex items-center text-2xl font-bold shadow-lg">▲ <span id="opt1" class="ml-4"></span></div>
            <div class="bg-blue-500 p-6 rounded-lg flex items-center text-2xl font-bold shadow-lg">◆ <span id="opt2" class="ml-4"></span></div>
            <div class="bg-yellow-500 p-6 rounded-lg flex items-center text-2xl font-bold shadow-lg">● <span id="opt3" class="ml-4"></span></div>
            <div class="bg-green-500 p-6 rounded-lg flex items-center text-2xl font-bold shadow-lg">■ <span id="opt4" class="ml-4"></span></div>
        </div>
    </div>

    <div id="leaderboard-ui" class="h-screen hidden flex flex-col items-center justify-center bg-indigo-900 p-10">
        <h1 id="leaderboard-title" class="text-5xl font-black text-yellow-400 mb-10 italic uppercase">Classement</h1>
        <div id="score-list" class="w-full max-w-xl space-y-4"></div>
        <div class="flex gap-4">
            <button id="next-btn" onclick="nextStep()" class="mt-12 bg-green-500 text-white px-12 py-4 rounded-full font-black text-2xl hover:scale-110 transition shadow-2xl">SUIVANT</button>
            <a id="home-btn" href="dashboard.php" class="hidden mt-12 bg-white text-indigo-900 px-12 py-4 rounded-full font-black text-2xl hover:scale-110 transition shadow-2xl">QUITTER</a>
        </div>
    </div>

    <script>
        const pin = "<?= $_GET['pin'] ?>";
        const hairIcons = ["", "👨‍🦱", "👱", "👨‍🦳", "🧔"];
        const outfitIcons = ["", "👕", "🥋", "👔", "🧥", "👚"];
        
        let localStatus = "";
        let timerVal = 0;
        let timerInterval;

        function getAvatarHtml(player, size="text-2xl") {
            return `
                <div class="player-avatar">
                    <div class="${size}">${hairIcons[player.hair]}</div>
                    <div class="${size}" style="margin-top:-15px">${outfitIcons[player.outfit]}</div>
                    <div class="text-xs font-bold uppercase mt-1">${player.nickname}</div>
                </div>
            `;
        }

        function update() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                if (data.status === 'playing') {
                    const answers = data.answers[data.current_q_index] || {};
                    const answeredNicks = Object.keys(answers);
                    document.getElementById('ans-count').innerText = answeredNicks.length;
                    
                    // Photo de classe : On affiche les avatars de ceux qui ont répondu
                    const overlay = document.getElementById('classroom-overlay');
                    overlay.innerHTML = "";
                    data.players.forEach(p => {
                        if(answeredNicks.includes(p.nickname)) {
                            overlay.innerHTML += getAvatarHtml(p, "text-4xl");
                        }
                    });
                }

                if (data.status !== localStatus) {
                    if (data.status === 'playing') showQuestion(data);
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
                if (timerVal <= 0) {
                    clearInterval(timerInterval);
                    fetch(`api_live.php?action=show_leaderboard&pin=${pin}`);
                }
                timerVal--;
            }, 1000);
        }

        function showLeaderboard(data, title="CLASSEMENT") {
            document.getElementById('question-ui').classList.add('hidden');
            document.getElementById('leaderboard-ui').classList.remove('hidden');
            document.getElementById('leaderboard-title').innerText = title;
            
            if(title === "PODIUM FINAL") {
                document.getElementById('next-btn').classList.add('hidden');
                document.getElementById('home-btn').classList.remove('hidden');
            }

            const list = document.getElementById('score-list');
            list.innerHTML = "";
            
            // Trier les scores
            const sorted = Object.entries(data.scores).sort((a,b) => b[1]-a[1]).slice(0,5);
            
            sorted.forEach(([nick, pts]) => {
                const player = data.players.find(p => p.nickname === nick);
                list.innerHTML += `
                    <div class="bg-indigo-800 p-4 rounded-2xl flex items-center justify-between border-l-8 border-yellow-400">
                        <div class="flex items-center gap-4">
                            <div class="text-3xl">${hairIcons[player.hair]}${outfitIcons[player.outfit]}</div>
                            <span class="text-2xl font-bold">${nick}</span>
                        </div>
                        <span class="text-3xl font-black">${pts} pts</span>
                    </div>
                `;
            });
        }

        function nextStep() {
            fetch(`api_live.php?action=next_step&pin=${pin}`).then(() => update());
        }

        setInterval(update, 1500);
    </script>
</body>
</html>