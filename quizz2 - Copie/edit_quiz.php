<?php
require_once 'db.php';
if (!hasRole('createur')) { header("Location: dashboard.php"); exit; }

$quiz_id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) { header("Location: manage_quizzes.php"); exit; }

// --- ACTION : SUPPRIMER UNE QUESTION ---
if (isset($_GET['delete_q'])) {
    $pdo->prepare("DELETE FROM questions WHERE id = ? AND quiz_id = ?")->execute([$_GET['delete_q'], $quiz_id]);
    header("Location: edit_quiz.php?id=$quiz_id");
    exit;
}

// --- ACTION : AJOUTER OU MODIFIER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_q'])) {
    if (!empty($_POST['q_id'])) {
        // Modification
        $stmt = $pdo->prepare("UPDATE questions SET question_text=?, image_url=?, timer=?, opt1=?, opt2=?, opt3=?, opt4=?, correct_answer=? WHERE id=? AND quiz_id=?");
        $stmt->execute([$_POST['txt'], $_POST['img'], $_POST['time'], $_POST['o1'], $_POST['o2'], $_POST['o3'], $_POST['o4'], $_POST['correct'], $_POST['q_id'], $quiz_id]);
    } else {
        // Ajout
        $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, image_url, timer, opt1, opt2, opt3, opt4, correct_answer) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$quiz_id, $_POST['txt'], $_POST['img'], $_POST['time'], $_POST['o1'], $_POST['o2'], $_POST['o3'], $_POST['o4'], $_POST['correct']]);
    }
    header("Location: edit_quiz.php?id=$quiz_id");
    exit;
}

// Récupérer la question à modifier si besoin
$editQ = null;
if (isset($_GET['edit_q'])) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ? AND quiz_id = ?");
    $stmt->execute([$_GET['edit_q'], $quiz_id]);
    $editQ = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Questions - Bernard Quizz</title>
</head>
<body class="bg-gray-100 p-6 font-sans">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold italic uppercase">Questions : <?= htmlspecialchars($quiz['title']) ?></h1>
            <a href="manage_quizzes.php" class="bg-white px-4 py-2 rounded-lg text-sm shadow">Terminer</a>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border mb-8">
            <h2 class="font-bold mb-4 text-indigo-700"><?= $editQ ? "Modifier la question" : "Ajouter une question" ?></h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="q_id" value="<?= $editQ['id'] ?? '' ?>">
                <input type="text" name="txt" placeholder="Question..." value="<?= $editQ['question_text'] ?? '' ?>" required class="w-full p-3 border rounded-xl">
                <input type="text" name="img" placeholder="URL Image" value="<?= $editQ['image_url'] ?? '' ?>" class="w-full p-3 border rounded-xl">
                
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="o1" placeholder="Option 1" value="<?= $editQ['opt1'] ?? '' ?>" required class="p-3 border-l-8 border-red-500 rounded bg-red-50">
                    <input type="text" name="o2" placeholder="Option 2" value="<?= $editQ['opt2'] ?? '' ?>" required class="p-3 border-l-8 border-blue-500 rounded bg-blue-50">
                    <input type="text" name="o3" placeholder="Option 3" value="<?= $editQ['opt3'] ?? '' ?>" required class="p-3 border-l-8 border-yellow-500 rounded bg-yellow-50">
                    <input type="text" name="o4" placeholder="Option 4" value="<?= $editQ['opt4'] ?? '' ?>" required class="p-3 border-l-8 border-green-500 rounded bg-green-50">
                </div>

                <div class="flex items-center gap-4 bg-indigo-50 p-4 rounded-xl">
                    <span class="font-bold text-sm">Correcte :</span>
                    <?php for($i=1;$i<=4;$i++): ?>
                        <label><input type="radio" name="correct" value="<?= $i ?>" <?= ($editQ['correct_answer'] ?? 1) == $i ? 'checked' : '' ?>> <?= $i ?></label>
                    <?php endfor; ?>
                    <select name="time" class="ml-auto p-1 border rounded">
                        <option value="10" <?= ($editQ['timer'] ?? 20) == 10 ? 'selected' : '' ?>>10s</option>
                        <option value="20" <?= ($editQ['timer'] ?? 20) == 20 ? 'selected' : '' ?>>20s</option>
                        <option value="30" <?= ($editQ['timer'] ?? 20) == 30 ? 'selected' : '' ?>>30s</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" name="save_q" class="flex-grow bg-indigo-600 text-white py-3 rounded-xl font-bold">ENREGISTRER</button>
                    <?php if($editQ): ?><a href="edit_quiz.php?id=<?= $quiz_id ?>" class="bg-gray-200 px-6 py-3 rounded-xl">Annuler</a><?php endif; ?>
                </div>
            </form>
        </div>

        <div class="space-y-3">
            <?php
            $qs = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
            $qs->execute([$quiz_id]);
            while($q = $qs->fetch()):
            ?>
            <div class="bg-white p-4 rounded-xl shadow-sm border flex justify-between items-center group">
                <div class="flex items-center gap-4">
                    <span class="text-gray-400 font-bold">#<?= $q['id'] ?></span>
                    <span class="font-medium"><?= htmlspecialchars($q['question_text']) ?></span>
                </div>
                <div class="flex gap-4 opacity-0 group-hover:opacity-100 transition">
                    <a href="?id=<?= $quiz_id ?>&edit_q=<?= $q['id'] ?>" class="text-blue-500 text-sm font-bold">Modifier</a>
                    <a href="?id=<?= $quiz_id ?>&delete_q=<?= $q['id'] ?>" onclick="return confirm('Supprimer cette question ?')" class="text-red-500 text-sm font-bold">Supprimer</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>