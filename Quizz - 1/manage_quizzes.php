<?php
require_once 'db.php';

if (!hasRole('createur')) { header("Location: dashboard.php"); exit; }

// --- ACTION : SUPPRESSION SÉCURISÉE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $userId = $_SESSION['user_id'];
    $isAdmin = ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'fondateur');

    // Vérification de propriété
    $check = $pdo->prepare("SELECT id FROM quizzes WHERE id = ? " . ($isAdmin ? "" : "AND user_id = ?"));
    $params = $isAdmin ? [$id] : [$id, $userId];
    $check->execute($params);

    if ($check->fetch()) {
        try {
            $pdo->beginTransaction();
            // 1. Supprimer les questions liées
            $pdo->prepare("DELETE FROM questions WHERE quiz_id = ?")->execute([$id]);
            // 2. Supprimer les sessions liées (la cause de ton erreur)
            $pdo->prepare("DELETE FROM game_sessions WHERE quiz_id = ?")->execute([$id]);
            // 3. Enfin, supprimer le quiz
            $pdo->prepare("DELETE FROM quizzes WHERE id = ?")->execute([$id]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Erreur lors de la suppression : " . $e->getMessage());
        }
    }
    header("Location: manage_quizzes.php?deleted=1");
    exit;
}

// --- ACTION : CRÉATION ---
if (isset($_POST['create_quiz'])) {
    $title = trim($_POST['title']);
    if (!empty($title)) {
        $stmt = $pdo->prepare("INSERT INTO quizzes (user_id, title, description, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $_POST['description'], $_POST['image_url']]);
        header("Location: manage_quizzes.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script>
    <title>Mes Quiz - Bernard Quizz</title>
</head>
<body class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-indigo-800">Mes Bibliothèques</h1>
            <a href="dashboard.php" class="text-gray-500 hover:underline">← Dashboard</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border h-fit">
                <h2 class="font-bold mb-4">Nouveau Quiz</h2>
                <form method="POST" class="space-y-4">
                    <input type="text" name="title" placeholder="Titre" required class="w-full p-2 border rounded-lg">
                    <input type="text" name="image_url" placeholder="URL Image" class="w-full p-2 border rounded-lg">
                    <textarea name="description" placeholder="Description" class="w-full p-2 border rounded-lg"></textarea>
                    <button type="submit" name="create_quiz" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-bold">CRÉER</button>
                </form>
            </div>

            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php
                $stmt = ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'fondateur') ? 
                        $pdo->query("SELECT * FROM quizzes ORDER BY id DESC") : 
                        $pdo->prepare("SELECT * FROM quizzes WHERE user_id = ? ORDER BY id DESC");
                if (!($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'fondateur')) $stmt->execute([$_SESSION['user_id']]);

                while($q = $stmt->fetch()):
                ?>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden flex flex-col">
                    <div class="h-32 bg-gray-200">
                        <?php if($q['image_url']): ?> <img src="<?= $q['image_url'] ?>" class="w-full h-full object-cover"> <?php endif; ?>
                    </div>
                    <div class="p-4 flex-grow">
                        <h3 class="font-bold text-lg"><?= htmlspecialchars($q['title']) ?></h3>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <a href="edit_quiz.php?id=<?= $q['id'] ?>" class="bg-blue-500 text-white text-center py-2 rounded-lg text-xs font-bold">MODIFIER</a>
                            <a href="host_game.php?quiz_id=<?= $q['id'] ?>" class="bg-green-500 text-white text-center py-2 rounded-lg text-xs font-bold">LANCER</a>
                            <a href="?delete=<?= $q['id'] ?>" onclick="return confirm('Supprimer ?')" class="col-span-2 text-center text-red-500 text-xs mt-2">Supprimer définitivement</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>