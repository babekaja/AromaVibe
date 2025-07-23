<?php
/**
 * Fonctions utilitaires pour AromaVibe
 */

/**
 * Récupère tous les parfums avec pagination optionnelle
 */
function getAllParfums($pdo, $limit = null, $offset = 0) {
    $sql = "SELECT * FROM parfums ORDER BY date_creation DESC";
    if ($limit) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère un parfum par son ID
 */
function getParfumById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM parfums WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Recherche de parfums par critères
 */
function searchParfums($pdo, $search = '', $marque = '', $prix_min = 0, $prix_max = 1000) {
    $sql = "SELECT * FROM parfums WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (nom LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($marque)) {
        $sql .= " AND marque = ?";
        $params[] = $marque;
    }

    $sql .= " AND prix BETWEEN ? AND ?";
    $params[] = $prix_min;
    $params[] = $prix_max;

    $sql .= " ORDER BY date_creation DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les marques disponibles
 */
function getAllMarques($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT marque FROM parfums ORDER BY marque");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Vérifie si l'utilisateur est connecté en tant qu'admin
 */
function isLoggedIn() {
    return isset($_SESSION['admin']);
}

/**
 * Sécurise l'upload d'images
 *
 * @param array $files The $_FILES array for the image input.
 * @return array An array of uploaded image filenames relative to the assets/images directory, or empty array on failure.
 */
function uploadImages($files) {
    // Determine the absolute path to the assets/images directory
    // __DIR__ gives the directory of the current file (functions.php, which is in 'includes/')
    // We go up one level (../) to the root directory, then into 'assets/images/'
    $uploadDir = __DIR__ . '/../assets/images/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/gif']; // Added jpg and gif
    $maxSize = 5 * 1024 * 1024; // 5MB
    $uploadedFiles = [];

    // Create the directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create recursively and set full permissions
    }

    foreach ($files['tmp_name'] as $key => $tmpName) {
        // Check for upload errors
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $fileType = $files['type'][$key];
            $fileSize = $files['size'][$key];
            $originalFileName = $files['name'][$key];
            $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

            // Validate file type
            if (!in_array($fileType, $allowedTypes)) {
                error_log("Invalid file type attempted: {$originalFileName} (Type: {$fileType})");
                continue; // Skip this file
            }

            // Validate file size
            if ($fileSize > $maxSize) {
                error_log("File too large: {$originalFileName} (Size: {$fileSize} bytes)");
                continue; // Skip this file
            }

            // Generate a unique filename to prevent conflicts
            $newFileName = uniqid('parfum_', true) . '.' . $fileExtension;
            $targetPath = $uploadDir . $newFileName;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($tmpName, $targetPath)) {
                $uploadedFiles[] = $newFileName; // Store only the new filename
            } else {
                error_log("Failed to move uploaded file: {$tmpName} to {$targetPath}");
            }
        } else {
            // Log specific upload errors
            error_log("File upload error for {$files['name'][$key]}: " . $files['error'][$key]);
        }
    }

    return $uploadedFiles;
}

/**
 * Génère les données JSON pour l'IA Gemini
 */
function generateGeminiData($pdo) {
    $parfums = getAllParfums($pdo);
    $marques = getAllMarques($pdo);

    $data = [
        'site_info' => [
            'name' => 'AromaVibe by Jas',
            'description' => 'Boutique en ligne de parfums de luxe',
            'specialites' => ['Parfums de marque', 'Fragrances exclusives', 'Conseils personnalisés']
        ],
        'parfums' => $parfums,
        'marques' => $marques,
        'stats' => [
            'total_parfums' => count($parfums),
            'marques_disponibles' => count($marques)
        ]
    ];

    return json_encode($data, JSON_UNESCAPED_UNICODE);
}
?>
