<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Master Live</title>
    <style>
        .zoom-question { animation: zoomIn 3s ease-out forwards; }
        @keyframes zoomIn { from { transform: scale(0.2); opacity: 0; } to { transform: scale(1.4); opacity: 1; } }
        
        /* Chute du podium */
        @keyframes physicsFall { 0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; } 100% { transform: translateY(120vh) rotate(20deg); opacity: 0; } }
        .falling-player { animation: physicsFall 2s ease-in forwards; position: absolute; top: 0; z-index: 50; }
        .podium-step { transition: all 1s ease-out; opacity: 0; transform: translateY(100px); }
        .podium-visible { opacity: 1; transform: translateY(0); }
        .winner-dark { filter: brightness(0); transition: filter 1.5s ease-in-out; }
    </style>
</head>
<body class="bg-indigo-700 min-h-screen text-white font-sans overflow-hidden">

    <div id="reveal-ui" class="hidden h-screen flex items-center justify-center p-10 bg-indigo-900">
        <h1 id="reveal-text" class="text-6xl font-black italic text-center zoom-question text-yellow-400 drop-shadow-lg"></h1>
    </div>

    <div id="question-ui" class="hidden flex flex-col h-screen">
        <div class="bg-white text-gray-900 p-4 text-center shadow-xl z-20">
            <h1 id="q-text" class="text-3xl font-black italic text-indigo-800"></h1>
        </div>
        
        <div class="flex-grow flex flex-col items-center justify-center relative p-4 bg-black bg-opacity-10">
            <div id="img-box" class="hidden mb-4 p-2 bg-white rounded-xl shadow-2xl">
                <img id="q-img" src="" class="max-h-56 rounded-lg object-contain">
            </div>
            <div id="classroom" class="flex flex-wrap justify-center gap-4 max-w-5xl"></div>
        </div>

        <div class="flex items-center justify-around py-4 bg-indigo-900 border-t border-indigo-500 z-20">
            <div id="timer" class="text-6xl font-black bg-white text-indigo-700 w-28 h-28 rounded-full flex items-center justify-center border-4 border-indigo-300 shadow-2xl">0</div>
            <div class="text-center">
                <p id="ans-count" class="text-7xl font-black text-yellow-400">0</p>
                <p class="text-sm font-bold opacity-70 uppercase tracking-widest">Réponses</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 p-2 h-1/4">
            <div class="bg-red-500 rounded-xl p-4 text-2xl font-bold flex items-center shadow-lg">▲ <span id="opt1" class="ml-4"></span></div>
            <div class="bg-blue-500 rounded-xl p-4 text-2xl font-bold flex items-center shadow-lg">◆ <span id="opt2" class="ml-4"></span></div>
            <div class="bg-yellow-500 rounded-xl p-4 text-2xl font-bold flex items-center shadow-lg">● <span id="opt3" class="ml-4"></span></div>
            <div class="bg-green-500 rounded-xl p-4 text-2xl font-bold flex items-center shadow-lg">■ <span id="opt4" class="ml-4"></span></div>
        </div>
    </div>

    <div id="leaderboard-ui" class="h-screen hidden flex flex-col items-center justify-center bg-indigo-900 p-10 relative">
        <div id="falling-zone" class="absolute inset-0 pointer-events-none"></div>
        <h1 id="l-title" class="text-6xl font-black text-yellow-400 mb-8 italic uppercase drop-shadow-md">Classement</h1>
        <div id="score-list" class="w-full max-w-xl space-y-3 z-10"></div>
        
        <div id="final-podium" class="hidden w-full flex justify-center items-end gap-6 mt-10">
            <div id="rank-2" class="podium-step flex flex-col items-center"><div id="av-2" class="mb-2"></div><div class="bg-gray-400 w-32 h-40 rounded-t-xl flex items-center justify-center font-black text-5xl text-gray-700 shadow-2xl">2</div></div>
            <div id="rank-1" class="podium-step flex flex-col items-center"><div id="av-1" class="mb-2 winner-dark"></div><div class="bg-yellow-400 w-40 h-56 rounded-t-xl flex items-center justify-center font-black text-7xl text-yellow-700 shadow-2xl border-t-8 border-yellow-200">1</div></div>
            <div id="rank-3" class="podium-step flex flex-col items-center"><div id="av-3" class="mb-2"></div><div class="bg-orange-600 w-32 h-24 rounded-t-xl flex items-center justify-center font-black text-5xl text-orange-800 shadow-2xl">3</div></div>
        </div>

        <div class="flex gap-4 mt-12 z-20">
            <button id="next-btn" onclick="nextStep()" class="bg-green-500 text-white px-14 py-5 rounded-full font-black text-2xl shadow-2xl hover:bg-green-400 transition transform hover:scale-105">SUIVANT</button>
            <a id="home-btn" href="dashboard.php" class="hidden bg-white text-indigo-900 px-14 py-5 rounded-full font-black text-2xl shadow-2xl hover:bg-gray-100 transition">QUITTER</a>
        </div>
    </div>

    <script>
        const pin = "<?= $_GET['pin'] ?>";
        let localStatus = ""; 
        let currentQIdx = -1; 
        let timerInterval; 
        let answeredList = [];

        // LA FONCTION MAGIQUE DE L'AVATAR (Aura statique en premier plan)
        function renderAv(p, s="w-16 h-16") {
            let aura = (p.aura && p.aura > 0) ? `<img src="personnage/aura/aura${p.aura}.png" class="absolute inset-[-15%] w-[130%] h-[130%] object-contain pointer-events-none" style="z-index: 30;">` : '';
            let badge = p.is_member ? `<div class="absolute -bottom-2 -right-2 bg-yellow-400 text-black text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full border-2 border-white" style="z-index: 40;">★</div>` : '';
            
            return `<div class="relative ${s} mx-auto">
                <img src="personnage/tenue/tenue${p.outfit}.png" class="absolute inset-0 w-full h-full object-contain" style="z-index: 10;">
                <img src="personnage/cheveux/cheveux${p.hair}.png" class="absolute inset-0 w-full h-full object-contain" style="z-index: 20;">
                ${aura}
                ${badge}
            </div>`;
        }

        function update() {
            fetch(`api_live.php?action=get_state&pin=${pin}`).then(r => r.json()).then(data => {
                // Détection de changement d'état ou de question
                if (data.status !== localStatus || data.current_q_index !== currentQIdx) {
                    localStatus = data.status; 
                    currentQIdx = data.current_q_index;
                    
                    if (data.status === 'reveal') showReveal(data);
                    if (data.status === 'playing') showQuestion(data);
                    if (data.status === 'leaderboard') showLeaderboard(data);
                    if (data.status === 'finished') startFinalPodium(data);
                }
                
                // Si on joue, on met à jour le compteur en direct
                if (data.status === 'playing') {
                    const answers = data.answers[data.current_q_index] || {};
                    const nicks = Object.keys(answers);
                    document.getElementById('ans-count').innerText = nicks.length;
                    
                    const classroom = document.getElementById('classroom');
                    nicks.forEach(n => {
                        if(!answeredList.includes(n)) {
                            const p = data.players.find(x => x.nickname === n);
                            if(p) {
                                classroom.innerHTML += `<div class="text-center p-2">${renderAv(p, "w-14 h-14")}<p class="text-[10px] font-bold mt-2 uppercase tracking-wide">${p.nickname}</p></div>`;
                            }
                            answeredList.push(n);
                        }
                    });
                }
            });
        }

        function showReveal(data) {
            document.getElementById('question-ui').classList.add('hidden');
            document.getElementById('leaderboard-ui').classList.add('hidden');
            document.getElementById('reveal-ui').classList.remove('hidden');
            document.getElementById('reveal-text').innerText = data.question.question_text;
            
            // On bascule sur le chrono après 3 secondes
            setTimeout(() => { fetch(`api_live.php?action=activate_playing&pin=${pin}`); }, 3000);
        }

        function showQuestion(data) {
            document.getElementById('reveal-ui').classList.add('hidden');
            document.getElementById('question-ui').classList.remove('hidden');
            document.getElementById('q-text').innerText = data.question.question_text;
            
            // Affichage de l'image si elle existe
            const imgBox = document.getElementById('img-box');
            if(data.question.image_url && data.question.image_url.trim() !== "") {
                document.getElementById('q-img').src = data.question.image_url;
                imgBox.classList.remove('hidden');
            } else {
                imgBox.classList.add('hidden');
            }

            document.getElementById('opt1').innerText = data.question.opt1;
            document.getElementById('opt2').innerText = data.question.opt2;
            document.getElementById('opt3').innerText = data.question.opt3;
            document.getElementById('opt4').innerText = data.question.opt4;
            
            answeredList = []; 
            document.getElementById('classroom').innerHTML = "";
            
            clearInterval(timerInterval);
            let timerVal = parseInt(data.question.timer);
            timerInterval = setInterval(() => {
                document.getElementById('timer').innerText = timerVal;
                if (timerVal <= 0) { 
                    clearInterval(timerInterval); 
                    fetch(`api_live.php?action=show_leaderboard&pin=${pin}`); 
                }
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
                list.innerHTML += `<div class="bg-indigo-800 p-4 rounded-2xl flex justify-between items-center border-l-8 border-yellow-400 shadow-md">
                    <div class="flex items-center gap-6">${renderAv(p, "w-12 h-12")} <span class="font-bold uppercase text-lg tracking-wider">${nick}</span></div>
                    <span class="text-3xl font-black text-yellow-300">${pts} pts</span></div>`;
            });
        }

        function startFinalPodium(data) {
            document.getElementById('l-title').innerText = "CLASSEMENT FINAL";
            document.getElementById('score-list').classList.add('hidden');
            document.getElementById('next-btn').classList.add('hidden');
            document.getElementById('final-podium').classList.remove('hidden');
            document.getElementById('home-btn').classList.remove('hidden');
            
            const sorted = Object.entries(data.scores).sort((a,b) => b[1]-a[1]);
            const top3 = sorted.slice(0, 3);
            const losers = sorted.slice(3);

            // Animation de chute pour les perdants
            losers.forEach((l, i) => {
                const p = data.players.find(x => x.nickname === l[0]);
                const div = document.createElement('div');
                div.className = "falling-player"; 
                div.style.left = (Math.random()*80+10)+"%";
                div.style.animationDelay = (i * 0.2) + "s";
                div.innerHTML = renderAv(p, "w-16 h-16");
                document.getElementById('falling-zone').appendChild(div);
            });

            // Apparition du podium : 3, puis 2, puis 1
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
                    setTimeout(() => av1.classList.remove('winner-dark'), 1500); // Révélation du vainqueur
                }, 6000);
            }
        }

        function nextStep() { fetch(`api_live.php?action=next_step&pin=${pin}`); }
        setInterval(update, 1500);
    </script>
</body>
</html>