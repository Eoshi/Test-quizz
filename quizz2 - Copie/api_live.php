<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$pin = $_GET['pin'] ?? '';
$gameStateFile = "sessions/game_" . $pin . ".json";

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
            $state['last_update'] = time();
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
        $state['answers'] = array_fill(0, count($qs), []);
        $state['last_update'] = time();
        break;

    case 'activate_playing':
        $state['status'] = 'playing';
        $state['last_update'] = time();
        break;

    case 'show_leaderboard':
        $state['status'] = 'leaderboard';
        $state['last_update'] = time();
        break;

    case 'next_step':
        $state['current_q_index']++;
        if ($state['current_q_index'] < count($state['questions_list'])) {
            $state['status'] = 'reveal';
            $state['question'] = $state['questions_list'][$state['current_q_index']];
        } else {
            $state['status'] = 'finished';
        }
        $state['last_update'] = time();
        break;

    case 'get_state':
        echo json_encode($state);
        exit;
}

file_put_contents($gameStateFile, json_encode($state));
echo json_encode(['status' => 'success']);