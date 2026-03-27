<?php
require_once 'db.php';

// Sécurité : Seuls les utilisateurs avec le rang 'createur' ou plus peuvent accéder
if (!hasRole('createur')) { 
    header("Location: dashboard.php"); 
    exit; 
}

// --- ACTION : CRÉATION D'UN NOUVEAU QUIZ ---
if (isset($_POST['create_quiz'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $img = trim($_POST['image_url']);
    $user_id = $_SESSION['user_id'];

    if (!empty($title)) {
        $stmt = $pdo->prepare("INSERT INTO quizzes (user_id, title, description, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $desc, $img]);
        header("Location: manage_quizzes.php?success=1");
        exit;
    }
}

// --- ACTION : SUPPRESSION D'UN QUIZ ---
if (isset($_GET['delete'])) {
    // On vérifie que le quiz appartient bien à l'utilisateur (ou qu'il est admin/fondateur)
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'fondateur') {
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    }
    header("Location: manage_quizzes.php?deleted=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Mes Quiz - Bernard Quizz</title>
</head>
<body class="bg-gray-50 min-h-screen font-sans">

    <nav class="bg-white shadow-sm p-4 border-b">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-black text-indigo-700 italic">BERNARD QUIZZ</h1>
            <a href="dashboard.php" class="text-gray-600 hover:text-indigo-600 font-medium">← Retour Dashboard</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto py-10 px-4">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-10">
                    <h2 class="text-xl font-bold mb-6 text-gray-800">Nouveau Quiz</h2>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Titre</label>
                            <input type="text" name="title" placeholder="Ex: Culture G" required 
                                   class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">URL Image Couverture</label>
                            <input type="text" name="image_url" placeholder="https://..." 
                                   class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" placeholder="De quoi parle votre quiz ?" 
                                      class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                        </div>
                        <button type="submit" name="create_quiz" 
                                class="w-full bg-indigo-600 text-white p-4 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                            CRÉER LE QUIZ
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Mes Bibliothèques</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php
                    // On récupère les quiz de l'utilisateur (ou tous si Admin/Fondateur)
                    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'fondateur') {
                        $stmt = $pdo->query("SELECT * FROM quizzes ORDER BY created_at DESC");
                    } else {
                        $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE user_id = ? ORDER BY created_at DESC");
                        $stmt->execute([$_SESSION['user_id']]);
                    }

                    if ($stmt->rowCount() === 0): ?>
                        <div class="col-span-2 text-center py-20 bg-white rounded-2xl border-2 border-dashed border-gray-200">
                            <p class="text-gray-400 italic">Vous n'avez pas encore créé de quiz.</p>
                        </div>
                    <?php endif;

                    while($quiz = $stmt->fetch()):
                    ?>
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 flex flex-col hover:shadow-md transition">
                        <div class="h-40 bg-gray-200 relative">
                            <?php if(!empty($quiz['image_url'])): ?>
                                <img src="<?= htmlspecialchars($quiz['image_url']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-400 font-bold">PAS D'IMAGE</div>
                            <?php endif; ?>
                            <div class="absolute top-2 right-2 bg-white px-2 py-1 rounded-md text-xs font-bold shadow-sm">
                                <?php 
                                    $qCount = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE quiz_id = ?");
                                    $qCount->execute([$quiz['id']]);
                                    echo $qCount->fetchColumn();
                                ?> Questions
                            </div>
                        </div>

                        <div class="p-5 flex-grow flex flex-col">
                            <h3 class="text-lg font-bold text-gray-800 mb-1"><?= htmlspecialchars($quiz['title']) ?></h3>
                            <p class="text-gray-500 text-sm mb-6 line-clamp-2"><?= htmlspecialchars($quiz['description']) ?></p>
                            
                            <div class="grid grid-cols-2 gap-2 mt-auto">
                                <a href="edit_quiz.php?id=<?= $quiz['id'] ?>" 
                                   class="text-center bg-gray-100 text-gray-700 py-3 rounded-xl font-bold text-xs hover:bg-gray-200 transition">
                                    MODIFIER
                                </a>
                                <a href="host_game.php?quiz_id=<?= $quiz['id'] ?>" 
                                   class="text-center bg-green-500 text-white py-3 rounded-xl font-bold text-xs hover:bg-green-600 transition shadow-md shadow-green-100">
                                    LANCER LIVE
                                </a>
                                <a href="?delete=<?= $quiz['id'] ?>" 
                                   onclick="return confirm('Attention : cela supprimera définitivement le quiz et ses questions. Continuer ?')"
                                   class="col-span-2 text-center text-red-400 text-xs py-2 hover:text-red-600 transition">
                                    Supprimer le quiz
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

        </div>
    </div>

</body>
</html>