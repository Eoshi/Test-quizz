<?php
session_start();
$host = 'mysql-bthiery.alwaysdata.net';
$dbname = 'bthiery_quizz'; 
$user = 'bthiery'; 
$pass = 'Bthiery08082021&';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonction utilitaire pour vérifier les rôles
function hasRole($roleNeeded) {
    $roles = ['utilisateur' => 1, 'createur' => 2, 'admin' => 3, 'fondateur' => 4];
    if (!isset($_SESSION['role'])) return false;
    return $roles[$_SESSION['role']] >= $roles[$roleNeeded];
}