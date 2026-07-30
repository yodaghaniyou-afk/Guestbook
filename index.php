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
        header("Location: index.php");
        exit;
    }
}

// Recherche via $_GET (ex: index.php?recherche=Yoda)
$recherche = trim($_GET["recherche"] ?? "");

if ($recherche !== "") {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE nom LIKE :recherche ORDER BY date_creation DESC");
    $stmt->execute(["recherche" => "%$recherche%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY date_creation DESC");
}

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

        <form method="GET" action="index.php" class="search-form">
            <input type="text" name="recherche" placeholder="Rechercher par nom..." value="<?= htmlspecialchars($recherche) ?>">
            <button type="submit">Rechercher</button>
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