<?php
require_once 'db.php';

// Sécurité : Seuls les Admins et Fondateurs peuvent entrer
if (!hasRole('admin')) { 
    header("Location: dashboard.php"); 
    exit; 
}

// ACTION : Changer le rôle d'un utilisateur
if (isset($_POST['update_role'])) {
    $target_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];

    // Sécurité supplémentaire : Un Admin ne peut pas rétrograder un Fondateur
    // Et seul un Fondateur peut nommer un autre Admin
    if ($_SESSION['role'] === 'fondateur' || ($new_role !== 'admin' && $new_role !== 'fondateur')) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$new_role, $target_id]);
        $success = "Rôle mis à jour !";
    } else {
        $error = "Permissions insuffisantes pour ce rang.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Gestion Utilisateurs - Bernard Quizz</title>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Gestion des Utilisateurs</h1>
            <a href="dashboard.php" class="text-indigo-600 hover:underline">← Retour</a>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4 font-bold text-gray-600">Pseudo</th>
                        <th class="p-4 font-bold text-gray-600">Rôle Actuel</th>
                        <th class="p-4 font-bold text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT id, username, role FROM users ORDER BY role DESC");
                    while($u = $stmt->fetch()):
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 font-medium"><?= htmlspecialchars($u['username']) ?></td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase 
                                <?= $u['role'] === 'fondateur' ? 'bg-purple-100 text-purple-700' : ($u['role'] === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') ?>">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <?php if($u['role'] !== 'fondateur'): ?>
                            <form method="POST" class="flex gap-2">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <select name="new_role" class="text-sm border rounded p-1">
                                    <option value="utilisateur" <?= $u['role'] == 'utilisateur' ? 'selected' : '' ?>>Utilisateur</option>
                                    <option value="createur" <?= $u['role'] == 'createur' ? 'selected' : '' ?>>Créateur</option>
                                    <?php if($_SESSION['role'] === 'fondateur'): ?>
                                    <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <?php endif; ?>
                                </select>
                                <button type="submit" name="update_role" class="bg-indigo-500 text-white px-3 py-1 rounded text-sm hover:bg-indigo-600">OK</button>
                            </form>
                            <?php else: ?>
                            <span class="text-gray-400 text-xs italic">Inaltérable</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>