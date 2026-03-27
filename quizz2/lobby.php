<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Lobby - Bernard Quizz</title>
</head>
<body class="bg-indigo-900 text-white flex flex-col items-center justify-center min-h-screen">
    <h1 id="msg" class="text-3xl font-bold">Connexion au salon...</h1>
    <script>
        const pin = new URLSearchParams(window.location.search).get('pin');
        let nick = localStorage.getItem('quiz_nickname') || prompt("Ton pseudo :");
        if(nick) localStorage.setItem('quiz_nickname', nick);

        fetch(`api_live.php?action=join&pin=${pin}`, {
            method: 'POST',
            body: JSON.stringify({ nickname: nick })
        }).then(() => {
            document.getElementById('msg').innerText = "Prêt, " + nick + " ! Attend le Maître...";
        });

        function check() {
            fetch(`api_live.php?action=get_state&pin=${pin}`)
            .then(r => r.json()).then(data => {
                if(data.status === 'playing') window.location.href = `game_screen.php?pin=${pin}`;
            });
        }
        setInterval(check, 2000);
    </script>
</body>
</html>