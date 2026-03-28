<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Master Podium</title>
    <style>
        /* Animation de chute avec rebond (Physics) */
        @keyframes physicsFall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            60% { transform: translateY(80vh) rotate(20deg); }
            75% { transform: translateY(70vh) rotate(-10deg); }
            90% { transform: translateY(80vh) rotate(5deg); }
            100% { transform: translateY(120vh) rotate(0deg); opacity: 0; }
        }
        .falling-player { animation: physicsFall 2.5s cubic-bezier(.36,.07,.19,.97) forwards; position: absolute; top: 0; z-index: 50; }
        .podium-step { transition: all 1s ease-out; opacity: 0; transform: translateY(100px); }
        .podium-visible { opacity: 1; transform: translateY(0); }
        .winner-dark { filter: brightness(0); transition: filter 1s ease-in-out; }
        .winner-bright { filter: brightness(1); }
        .av-container { position: relative; width: 80px; height: 80px; margin: 0 auto; }
        .av-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; }
        #merguez-box { animation: slideUp 1s ease-out forwards; opacity: 0; }
        @keyframes slideUp { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body class="bg-indigo-700 min-h-screen text-white font-sans overflow-hidden">

    <div id="question-ui" class="flex flex-col h-screen">
        <div class="bg-white text-gray-900 p-4 text-center shadow-xl z-20">
            <h1 id="q-text" class="text-2xl font-black italic text-indigo-800"></h1>
        </div>
        
        <div class="flex-grow flex flex-col items-center justify-center relative p-4">
            <div id="img-box" class="hidden mb-4 p-2 bg-white rounded-xl shadow-2xl max-h-64">
                <img id="q-img" src="" class="max-h-60 rounded-lg object-contain">
            </div>
            
            <div id="classroom" class="flex flex-wrap justify-center gap-2 max-w-4xl"></div>
        </div>

        <div class="flex items-center justify-around py-4 bg-indigo-900 border-t border-indigo-500 z-20">
            <div id="timer" class="text-5xl font-black bg-white text-indigo-700 w-24 h-24 rounded-full flex items-center justify-center border-4 border-indigo-300 shadow-2xl">0</div>
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

    <div id="podium-ui" class="hidden h-screen w-full relative bg-indigo-900 overflow-hidden">
        <div id="falling-zone" class="absolute inset-0 pointer-events-none"></div>
        
        <div class="absolute bottom-24 w-full flex justify-center items-end gap-4">
            <div id="rank-2" class="podium-step flex flex-col items-center">
                <div id="av-2" class="av-container mb-2"></div>
                <div class="bg-gray-400 w-40 h-48 rounded-t-lg flex flex-col items-center justify-center shadow-2xl">
                    <span class="text-6xl font-black">2</span>
                    <p id="name-2" class="font-bold mt-2 truncate w-full text-center px-2 text-gray-800"></p>
                </div>
            </div>
            <div id="rank-1" class="podium-step flex flex-col items-center">
                <div id="av-1" class="av-container mb-2 winner-dark"></div>
                <div class="bg-yellow-400 w-48 h-64 rounded-t-lg flex flex-col items-center justify-center shadow-2xl border-t-8 border-yellow-200">
                    <span class="text-8xl font-black text-yellow-700">1</span>
                    <p id="name-1" class="font-bold mt-2 truncate w-full text-center px-2 text-yellow-900 uppercase"></p>
                </div>
            </div>
            <div id="rank-3" class="podium-step flex flex-col items-center">
                <div id="av-3" class="av-container mb-2"></div>
                <div class="bg-orange-600 w-40 h-32 rounded-t-lg flex flex-col items-center justify-center shadow-2xl">
                    <span class="text-5xl font-black">3</span>
                    <p id="name-3" class="font-bold mt-2 truncate w-full text-center px-2 text-orange-200"></p>
                </div>
            </div>
        </div>

        <div id="merguez-box" class="absolute bottom-4 left-4 right-4 bg-red-600 bg-opacity-80 p-4 rounded-2xl flex items-center justify-between border-2 border-red-400 hidden">
            <div class="flex items-center gap-4">
                <div class="text-4xl">🌭</div>
                <div>
                    <p class="text-xs font-bold uppercase text-red-200">Le Bernard de bois (Merguez)</p>
                    <p id="merguez-name" class="text-xl font-black italic"></p>
                </div>
            </div>
            <div id="merguez-av" class="w-16 h-16"></div>
        </div>

        <div class="absolute top-10 w-full text-center">
            <h1 class="text-5xl font-black italic text-yellow-400 drop-shadow-lg">PODIUM FINAL</h1>
            <a href="dashboard.php" id="btn-quit" class="hidden mt-8 inline-block bg-white text-indigo-900 px-10 py-4 rounded-full font-black text-xl shadow-2xl hover:scale-110 transition">RETOUR ACCUEIL</a>
        </div>
    </div>

    <script>
        const pin = "<?= $_GET['pin'] ?>";
        let localStatus = "";
        let timerVal = 0; let timerInterval;
        let answeredList = [];

        function getAv(p, s="w-16 h-16") {
            return `<div class="relative ${s} mx-auto"><img src="personnage/tenue/tenue${p.outfit}.png" class="av-layer"><img src="personnage/cheveux/cheveux${p.hair}.png" class="av-layer"></div>`;
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
                            if(p) classroom.innerHTML += `<div class="text-center">${getAv(p, "w-12 h-12")}<p class="text-[8px] font-bold">${p.nickname}</p></div>`;
                            answeredList.push(n);
                        }
                    });
                }
                if (data.status !== localStatus) {
                    if (data.status === 'playing') { answeredList = []; document.getElementById('classroom').innerHTML = ""; showQuestion(data); }
                    if (data.status === 'finished') startFinalPodium(data);
                    localStatus = data.status;
                }
            });
        }

        function showQuestion(data) {
            const q = data.question;
            document.getElementById('podium-ui').classList.add('hidden');
            document.getElementById('question-ui').classList.remove('hidden');
            document.getElementById('q-text').innerText = q.question_text;
            
            // Correction Affichage Image
            if(q.image_url && q.image_url.trim() !== "") {
                document.getElementById('q-img').src = q.image_url;
                document.getElementById('img-box').classList.remove('hidden');
            } else {
                document.getElementById('img-box').classList.add('hidden');
            }

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

        function startFinalPodium(data) {
            document.getElementById('question-ui').classList.add('hidden');
            document.getElementById('podium-ui').classList.remove('hidden');

            const sorted = Object.entries(data.scores).sort((a,b) => b[1]-a[1]);
            const top3 = sorted.slice(0, 3);
            const losers = sorted.slice(3);
            const merguez = sorted.length > 3 ? sorted[sorted.length - 1] : null;

            // 1. Chute physique des perdants
            const fallZone = document.getElementById('falling-zone');
            losers.forEach((l, i) => {
                const p = data.players.find(x => x.nickname === l[0]);
                const div = document.createElement('div');
                div.className = "falling-player flex flex-col items-center";
                div.style.left = (Math.random() * 80 + 10) + "%";
                div.style.animationDelay = (i * 0.2) + "s";
                div.innerHTML = getAv(p, "w-16 h-16") + `<p class="font-bold text-[10px]">${p.nickname}</p>`;
                fallZone.appendChild(div);
            });

            // 2. Podium séquentiel
            if(top3[2]) {
                const p3 = data.players.find(x => x.nickname === top3[2][0]);
                document.getElementById('av-3').innerHTML = getAv(p3, "w-24 h-24");
                document.getElementById('name-3').innerText = p3.nickname;
                setTimeout(() => document.getElementById('rank-3').classList.add('podium-visible'), 1000);
            }
            if(top3[1]) {
                const p2 = data.players.find(x => x.nickname === top3[1][0]);
                document.getElementById('av-2').innerHTML = getAv(p2, "w-24 h-24");
                document.getElementById('name-2').innerText = p2.nickname;
                setTimeout(() => document.getElementById('rank-2').classList.add('podium-visible'), 3000);
            }
            if(top3[0]) {
                const p1 = data.players.find(x => x.nickname === top3[0][0]);
                const av1 = document.getElementById('av-1');
                av1.innerHTML = getAv(p1, "w-32 h-32");
                document.getElementById('name-1').innerText = p1.nickname;
                setTimeout(() => {
                    document.getElementById('rank-1').classList.add('podium-visible');
                    setTimeout(() => {
                        av1.classList.remove('winner-dark');
                        av1.classList.add('winner-bright');
                        document.getElementById('btn-quit').classList.remove('hidden');
                        
                        // Afficher la Merguez à la toute fin
                        if(merguez) {
                            const pm = data.players.find(x => x.nickname === merguez[0]);
                            document.getElementById('merguez-name').innerText = pm.nickname;
                            document.getElementById('merguez-av').innerHTML = getAv(pm, "w-16 h-16");
                            document.getElementById('merguez-box').classList.remove('hidden');
                        }
                    }, 1500);
                }, 8000);
            }
        }

        setInterval(update, 1500);
    </script>
</body>
</html>