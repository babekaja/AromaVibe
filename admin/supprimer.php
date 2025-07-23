<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: dashboard.php");
    exit;
}

$parfum = getParfumById($pdo, $id);
if (!$parfum) {
    header("Location: dashboard.php");
    exit;
}

// Supprimer le parfum et ses images
try {
    // Récupérer les images pour les supprimer physiquement
    $images = json_decode($parfum['images'], true) ?: [];
    
    // Supprimer le parfum de la base de données
    $stmt = $pdo->prepare("DELETE FROM parfums WHERE id = ?");
    $stmt->execute([$id]);
    
    // Supprimer les fichiers images
    foreach ($images as $image) {
        $filePath = "../assets/images/" . $image;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // Redirection avec message de succès
    header("Location: dashboard.php?deleted=1");
    exit;
    
} catch (PDOException $e) {
    // Redirection avec message d'erreur
    header("Location: dashboard.php?error=delete");
    exit;
}
?>