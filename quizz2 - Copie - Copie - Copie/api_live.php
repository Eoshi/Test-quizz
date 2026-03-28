<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$pin = $_GET['pin'] ?? '';

// CHEMIN ABSOLU : On pointe vers le dossier sessions
$chemin_dossier = __DIR__ . '/sessions';
$gameStateFile = $chemin_dossier . '/game_' . $pin . '.json';

if (file_exists($gameStateFile)) {
    $state = json_decode(file_get_contents($gameStateFile), true);
} else {
    $state = ['players' => [], 'scores' => new stdClass(), 'answers' => new stdClass(), 'status' => 'lobby', 'current_q_index' => -1, 'last_update' => time()];
}

switch ($action) {
    case 'join':
        $input = json_decode(file_get_contents('php://input'), true);
        $nick = htmlspecialchars($input['nickname'] ?? 'Anonyme');
        
        $scoresArr = (array)$state['scores'];
        if (!isset($scoresArr[$nick])) {
            $state['players'][] = [
                'nickname' => $nick,
                'hair' => (int)($input['hair'] ?? 1),
                'outfit' => (int)($input['outfit'] ?? 1),
                'aura' => (int)($input['aura'] ?? 0),
                'is_member' => (bool)($input['is_member'] ?? false)
            ];
            $scoresArr[$nick] = 0;
            $state['scores'] = (object)$scoresArr;
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
        $state['answers'] = new stdClass();
        break;

    case 'activate_playing':
        $state['status'] = 'playing';
        break;

    case 'submit_answer':
        $input = json_decode(file_get_contents('php://input'), true);
        $nick = $input['nickname'] ?? '';
        $qIdx = (int)$state['current_q_index'];
        
        $allAnswers = (array)$state['answers'];
        if (!isset($allAnswers[$qIdx])) { $allAnswers[$qIdx] = []; }
        $currentQAnswers = (array)$allAnswers[$qIdx];

        if ($nick && !isset($currentQAnswers[$nick])) {
            $currentQAnswers[$nick] = $input['answer_index'];
            $allAnswers[$qIdx] = (object)$currentQAnswers;
            $state['answers'] = (object)$allAnswers;

            $isCorrect = filter_var($input['is_correct'], FILTER_VALIDATE_BOOLEAN);
            if ($isCorrect) {
                $timeTaken = (float)($input['response_time'] ?? 0);
                $pts = max(500, 1000 - (int)($timeTaken * 50));
                
                $scoresArr = (array)$state['scores'];
                $scoresArr[$nick] = ($scoresArr[$nick] ?? 0) + $pts;
                $state['scores'] = (object)$scoresArr;
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

$state['last_update'] = time();
file_put_contents($gameStateFile, json_encode($state));
echo json_encode(['status' => 'success']);