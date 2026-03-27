<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Lobby - Bernard Quizz</title>
</head>
<body class="bg-indigo-900 text-white flex flex-col items-center justify-center min-h-screen font-sans">
    <div class="text-center">
        <h1 id="msg" class="text-3xl font-bold mb-4">Connexion au salon...</h1>
        <div class="animate-bounce text-6xl mb-4">🎮</div>
        <p id="player-display" class="text-indigo-300 font-mono"></p>
    </div>

    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        let nick = localStorage.getItem('quiz_nickname');

        if(!nick || nick === "null") {
            nick = prompt("Choisis ton pseudo :");
            if(!nick) nick = "Joueur" + Math.floor(Math.random() * 1000);
            localStorage.setItem('quiz_nickname', nick);
        }

        document.getElementById('player-display').innerText = nick;

        // Envoi au serveur
        fetch(`api_live.php?action=join&pin=${pin}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nickname: nick })
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('msg').innerText = "Tu es dans le salon !";
        });

        // Vérifier si le jeu commence
        function checkStatus() {
            fetch(`api_live.php?action=get_state&pin=${pin}`)
            .then(r => r.json())
            .then(data => {
                if(data.status === 'playing') {
                    window.location.href = `game_screen.php?pin=${pin}`;
                }
            });
        }
        setInterval(checkStatus, 2000);
    </script>
</body>
</html>