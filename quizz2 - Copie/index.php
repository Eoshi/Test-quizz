<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Bernard Quizz - Accueil</title>
</head>
<body class="bg-indigo-900 flex items-center justify-center min-h-screen text-white font-sans">

    <div class="max-w-md w-full p-6 bg-white rounded-xl shadow-2xl text-gray-900">
        <h1 class="text-4xl font-black text-center text-indigo-700 mb-8 italic">BERNARD QUIZZ</h1>
        
        <div class="mb-8">
            <input type="text" id="pin" placeholder="Code PIN à 6 chiffres" 
                   class="w-full p-4 border-2 border-gray-200 rounded-lg text-center text-2xl font-bold tracking-widest focus:border-indigo-500 outline-none">
            <button onclick="joinGame()" class="w-full mt-4 bg-gray-800 text-white p-4 rounded-lg font-bold hover:bg-gray-700 transition">
                REJOINDRE LE SALON
            </button>
        </div>

        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="px-3 text-gray-500 text-sm">OU</span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>

        <div class="flex flex-col gap-3">
            <a href="login.php" class="text-center p-3 bg-indigo-100 text-indigo-700 rounded-lg font-semibold hover:bg-indigo-200 transition">
                Se connecter
            </a>
            <a href="register.php" class="text-center text-gray-500 text-sm hover:underline">
                Créer un compte créateur
            </a>
        </div>
    </div>

    <script>
        function joinGame() {
            const pin = document.getElementById('pin').value;
            if(pin.length === 6) {
                window.location.href = "lobby.php?pin=" + pin;
            } else {
                alert("Veuillez entrer un code à 6 chiffres.");
            }
        }
    </script>
</body>
</html>