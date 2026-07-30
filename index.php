<?php
require_once "config.php";

// Traitement du formulaire (si soumis)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($nom !== "" && $message !== "") {
        $stmt = $pdo->prepare("INSERT INTO messages (nom, message) VALUES (:nom, :message)");
        $stmt->execute([
            "nom" => $nom,
            "message" => $message
        ]);
        // Redirection pour éviter le renvoi du formulaire en rafraîchissant (pattern PRG)
        header("Location: index.php");
        exit;
    }
}

// Récupération de tous les messages, du plus récent au plus ancien
$stmt = $pdo->query("SELECT * FROM messages ORDER BY date_creation DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Livre d'Or</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>📖 Livre d'Or</h1>

        <form method="POST" action="index.php">
            <input type="text" name="nom" placeholder="Votre nom" required>
            <textarea name="message" placeholder="Votre message" required></textarea>
            <button type="submit">Envoyer</button>
        </form>

        <h2>Messages (<?= count($messages) ?>)</h2>
        <ul class="message-list">
            <?php foreach ($messages as $msg): ?>
                <li>
                    <strong><?= htmlspecialchars($msg["nom"]) ?></strong>
                    <span class="date"><?= $msg["date_creation"] ?></span>
                    <p><?= htmlspecialchars($msg["message"]) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>