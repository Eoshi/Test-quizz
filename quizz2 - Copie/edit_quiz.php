<?php
require_once 'db.php';
if (!hasRole('createur')) { header("Location: dashboard.php"); exit; }

$quiz_id = $_GET['id'] ?? null;
// Vérifier que le quiz appartient bien à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) { header("Location: manage_quizzes.php"); exit; }

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
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Editer Questions - Bernard Quizz</title>
</head>
<body class="bg-gray-100 p-6 font-sans">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold italic uppercase">Questions pour : <?= htmlspecialchars($quiz['title']) ?></h1>
            <a href="manage_quizzes.php" class="bg-white px-4 py-2 rounded-lg text-sm shadow">Retour</a>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border mb-6">
            <h2 class="font-bold mb-4">Ajouter une question</h2>
            <form method="POST" class="space-y-4">
                <input type="text" name="txt" placeholder="Intitulé (ex: De quelle couleur est...)" required class="w-full p-3 border rounded-xl">
                <input type="text" name="img" placeholder="Lien d'une image (ex: https://image.png)" class="w-full p-3 border rounded-xl">
                
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="o1" placeholder="Option 1 (ROUGE)" required class="p-3 border-l-8 border-red-500 rounded bg-red-50">
                    <input type="text" name="o2" placeholder="Option 2 (BLEU)" required class="p-3 border-l-8 border-blue-500 rounded bg-blue-50">
                    <input type="text" name="o3" placeholder="Option 3 (JAUNE)" required class="p-3 border-l-8 border-yellow-500 rounded bg-yellow-50">
                    <input type="text" name="o4" placeholder="Option 4 (VERT)" required class="p-3 border-l-8 border-green-500 rounded bg-green-50">
                </div>

                <div class="flex items-center gap-4 bg-indigo-50 p-4 rounded-xl">
                    <span class="font-bold text-sm">Réponse correcte :</span>
                    <?php for($i=1;$i<=4;$i++): ?>
                        <label class="flex items-center gap-1"><input type="radio" name="correct" value="<?= $i ?>" <?= $i==1?'checked':'' ?>> <?= $i ?></label>
                    <?php endfor; ?>
                    <select name="time" class="ml-auto p-1 border rounded">
                        <option value="10">10s</option>
                        <option value="20" selected>20s</option>
                        <option value="30">30s</option>
                    </select>
                </div>
                <button type="submit" name="add_q" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg">AJOUTER</button>
            </form>
        </div>

        <div class="space-y-2">
            <?php
            $qs = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
            $qs->execute([$quiz_id]);
            while($q = $qs->fetch()):
            ?>
            <div class="bg-white p-4 rounded-xl shadow-sm border flex justify-between items-center">
                <span><?= htmlspecialchars($q['question_text']) ?></span>
                <?php if($q['image_url']): ?> <span class="text-xs bg-gray-100 p-1 rounded">IMAGE ✅</span> <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>