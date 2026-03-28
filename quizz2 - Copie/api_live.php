<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$pin = $_GET['pin'] ?? '';
$gameStateFile = "sessions/game_" . $pin . ".json";

if (!is_dir('sessions')) { mkdir('sessions', 0755, true); }

if (file_exists($gameStateFile)) {
    $state = json_decode(file_get_contents($gameStateFile), true);
} else {
    $state = ['players' => [], 'scores' => [], 'answers' => [], 'status' => 'lobby', 'current_q_index' => -1];
}

switch ($action) {
    case 'join':
        $input = json_decode(file_get_contents('php://input'), true);
        $nick = htmlspecialchars($input['nickname'] ?? 'Anonyme');
        if (!isset($state['scores'][$nick])) {
            $state['players'][] = ['nickname' => $nick, 'hair' => (int)($input['hair'] ?? 1), 'outfit' => (int)($input['outfit'] ?? 1)];
            $state['scores'][$nick] = 0;
            file_put_contents($gameStateFile, json_encode($state));
        }
        break;

    case 'get_state':
        echo json_encode($state);
        exit;

    case 'start_game':
        $quiz_id = $_GET['quiz_id'];
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
        $stmt->execute([$quiz_id]);
        $qs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $state['status'] = 'playing';
        $state['questions_list'] = $qs;
        $state['current_q_index'] = 0;
        $state['question'] = $qs[0];
        $state['answers'] = array_fill(0, count($qs), []);
        file_put_contents($gameStateFile, json_encode($state));
        break;

    case 'submit_answer':
        $input = json_decode(file_get_contents('php://input'), true);
        $nick = $input['nickname'];
        $qIdx = (int)$state['current_q_index'];
        if (!isset($state['answers'][$qIdx][$nick])) {
            $state['answers'][$qIdx][$nick] = $input['answer_index'];
            if ($input['is_correct'] == true) {
                $pts = max(500, 1000 - (int)($input['response_time'] * 50));
                $state['scores'][$nick] += $pts;
            }
            file_put_contents($gameStateFile, json_encode($state));
        }
        break;

    case 'show_leaderboard':
        $state['status'] = 'leaderboard';
        file_put_contents($gameStateFile, json_encode($state));
        break;

    case 'next_step':
        $state['current_q_index']++;
        if ($state['current_q_index'] < count($state['questions_list'])) {
            $state['status'] = 'playing';
            $state['question'] = $state['questions_list'][$state['current_q_index']];
        } else {
            $state['status'] = 'finished';
        }
        file_put_contents($gameStateFile, json_encode($state));
        break;
}
echo json_encode(['status' => 'success']);