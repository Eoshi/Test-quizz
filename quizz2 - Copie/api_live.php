<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$pin = $_GET['pin'] ?? '';
$gameStateFile = "sessions/game_" . $pin . ".json";

// Création du dossier si inexistant
if (!is_dir('sessions')) { mkdir('sessions', 0755, true); }

if (file_exists($gameStateFile)) {
    $state = json_decode(file_get_contents($gameStateFile), true);
} else {
    $state = ['players' => [], 'scores' => [], 'answers' => [], 'status' => 'lobby', 'current_q_index' => -1, 'last_update' => time()];
}

switch ($action) {
    case 'join':
        $input = json_decode(file_get_contents('php://input'), true);
        $nick = htmlspecialchars($input['nickname'] ?? 'Anonyme');
        if (!isset($state['scores'][$nick])) {
            $state['players'][] = [
                'nickname' => $nick,
                'hair' => (int)($input['hair'] ?? 1),
                'outfit' => (int)($input['outfit'] ?? 1),
                'aura' => (int)($input['aura'] ?? 0),
                'is_member' => (bool)($input['is_member'] ?? false)
            ];
            $state['scores'][$nick] = 0;
        }
        break;

    case 'start_game':
        $quiz_id = $_GET['quiz_id'];
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
        $stmt->execute([$quiz_id]);
        $qs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $state['status'] = 'reveal';
        $state['questions_list'] = $qs;
        $state['current_q_index'] = 0;
        $state['question'] = $qs[0];
        
        // On prépare un objet vide pour chaque question pour éviter les bugs JS
        $state['answers'] = [];
        foreach ($qs as $k => $v) { $state['answers'][$k] = new stdClass(); }
        break;

    case 'activate_playing':
        $state['status'] = 'playing';
        break;

    case 'submit_answer':
        $input = json_decode(file_get_contents('php://input'), true);
        $nick = $input['nickname'] ?? '';
        $qIdx = (int)$state['current_q_index'];

        if ($nick && $qIdx >= 0) {
            if (!isset($state['answers'][$qIdx])) { $state['answers'][$qIdx] = []; }
            $currentAnswers = (array)$state['answers'][$qIdx];

            // Si le joueur n'a pas encore répondu à cette question
            if (!isset($currentAnswers[$nick])) {
                $currentAnswers[$nick] = $input['answer_index'];
                $state['answers'][$qIdx] = $currentAnswers;

                // Forcer la lecture en Booléen (Vrai/Faux)
                $isCorrect = filter_var($input['is_correct'], FILTER_VALIDATE_BOOLEAN);
                if ($isCorrect) {
                    $timeTaken = (float)($input['response_time'] ?? 0);
                    $pts = max(500, 1000 - (int)($timeTaken * 50));
                    $state['scores'][$nick] += $pts;
                }
            }
        }
        break;

    case 'show_leaderboard':
        $state['status'] = 'leaderboard';
        break;

    case 'next_step':
        $state['current_q_index']++;
        if ($state['current_q_index'] < count($state['questions_list'])) {
            $state['status'] = 'reveal';
            $state['question'] = $state['questions_list'][$state['current_q_index']];
        } else {
            $state['status'] = 'finished';
        }
        break;

    case 'get_state':
        echo json_encode($state);
        exit;
}

// Mise à jour du timestamp pour forcer le rafraîchissement des écrans
$state['last_update'] = time();
file_put_contents($gameStateFile, json_encode($state));
echo json_encode(['status' => 'success']);