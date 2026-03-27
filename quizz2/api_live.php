<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$pin = $_GET['pin'] ?? '';

if (!is_dir('sessions')) { mkdir('sessions', 0755, true); }
$gameStateFile = "sessions/game_" . $pin . ".json";

// Charger l'état ou créer un défaut
if (file_exists($gameStateFile)) {
    $state = json_decode(file_get_contents($gameStateFile), true);
} else {
    $state = [
        'players' => [], 
        'scores' => [], 
        'answers' => [], 
        'status' => 'lobby', 
        'current_q_index' => -1,
        'question' => null,
        'questions_list' => []
    ];
}

if ($action === 'join') {
    // Correction ici : lecture du flux JSON envoyé par lobby.php
    $input = json_decode(file_get_contents('php://input'), true);
    $nickname = htmlspecialchars($input['nickname'] ?? 'Anonyme');
    
    if (!in_array($nickname, $state['players'])) {
        $state['players'][] = $nickname;
        $state['scores'][$nickname] = 0;
        file_put_contents($gameStateFile, json_encode($state));
    }
    echo json_encode(['status' => 'success', 'player' => $nickname]);
    exit;
}

if ($action === 'get_players' || $action === 'get_state') {
    echo json_encode($state);
    exit;
}

if ($action === 'start_game') {
    $quiz_id = $_GET['quiz_id'];
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
    $stmt->execute([$quiz_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $state['status'] = 'playing';
    $state['questions_list'] = $questions;
    $state['current_q_index'] = 0;
    $state['question'] = $questions[0];
    $state['answers'] = array_fill(0, count($questions), (object)[]);
    
    file_put_contents($gameStateFile, json_encode($state));
    echo json_encode(['status' => 'success']);
    exit;
}

// Ajout des actions manquantes pour la suite du jeu
if ($action === 'show_leaderboard') {
    $state['status'] = 'leaderboard';
    file_put_contents($gameStateFile, json_encode($state));
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'next_step') {
    $state['current_q_index']++;
    if ($state['current_q_index'] < count($state['questions_list'])) {
        $state['status'] = 'playing';
        $state['question'] = $state['questions_list'][$state['current_q_index']];
    } else {
        $state['status'] = 'finished';
    }
    file_put_contents($gameStateFile, json_encode($state));
    echo json_encode(['status' => 'success']);
    exit;
}