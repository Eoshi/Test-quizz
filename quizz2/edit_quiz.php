<?php
require_once 'db.php';
if (!hasRole('createur')) { header("Location: dashboard.php"); exit; }

$quiz_id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND (user_id = ? OR 'fondateur' = ? OR 'admin' = ?)");
$stmt->execute([$quiz_id, $_SESSION['user_id'], $_SESSION['role'], $_SESSION['role']]);
$quiz = $stmt->fetch();

if (!$quiz) { header("Location: manage_quizzes.php"); exit; }

// Ajout Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_q'])) {
    $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, image_url, timer, opt1, opt2, opt3, opt4, correct_answer) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $quiz_id, $_POST['txt'], $_POST['img'], $_POST['time'],
        $_POST['o1'], $_POST['o2'], $_POST['o3'], $_POST['o4'], $_POST['correct']
    ]);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Questions - <?= htmlspecialchars($quiz['title']) ?></title>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold">Questions : <?= htmlspecialchars($quiz['title']) ?></h1>
            <a href="manage_quizzes.php" class="bg-white px-4 py-2 rounded shadow text-sm">Terminer</a>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm mb-10">
            <h2 class="text-lg font-bold mb-6 text-indigo-700">Nouvelle Question</h2>
            <form method="POST" class="space-y-6">
                <input type="text" name="txt" placeholder="Intitulé de la question" required class="w-full p-4 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="img" placeholder="URL Image (Optionnel)" class="p-3 bg-gray-50 border rounded-xl">
                    <select name="time" class="p-3 bg-gray-50 border rounded-xl">
                        <option value="5">5 secondes</option>
                        <option value="10" selected>10 secondes</option>
                        <option value="20">20 secondes</option>
                        <option value="30">30 secondes</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="o1" placeholder="Réponse Rouge" required class="p-3 border-l-8 border-red-500 rounded bg-red-50">
                    <input type="text" name="o2" placeholder="Réponse Bleue" required class="p-3 border-l-8 border-blue-500 rounded bg-blue-50">
                    <input type="text" name="o3" placeholder="Réponse Jaune" required class="p-3 border-l-8 border-yellow-500 rounded bg-yellow-50">
                    <input type="text" name="o4" placeholder="Réponse Verte" required class="p-3 border-l-8 border-green-500 rounded bg-green-50">
                </div>

                <div class="bg-indigo-50 p-4 rounded-xl">
                    <label class="block text-sm font-bold text-indigo-700 mb-2">Bonne réponse :</label>
                    <div class="flex justify-between">
                        <?php for($i=1; $i<=4; $i++): ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="correct" value="<?= $i ?>" <?= $i==1?'checked':'' ?> class="w-5 h-5">
                                <span>Option <?= $i ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <button type="submit" name="add_q" class="w-full bg-indigo-600 text-white p-4 rounded-xl font-bold hover:bg-indigo-700 shadow-lg">AJOUTER LA QUESTION</button>
            </form>
        </div>

        <div class="space-y-3">
            <?php
            $qs = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
            $qs->execute([$quiz_id]);
            while($q = $qs->fetch()):
            ?>
            <div class="bg-white p-4 rounded-xl flex items-center justify-between border-l-4 border-indigo-500 shadow-sm">
                <span class="font-medium"><?= htmlspecialchars($q['question_text']) ?></span>
                <span class="text-xs font-bold text-gray-400"><?= $q['timer'] ?>s</span>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>