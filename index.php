<?php
require_once "config.php";

class MessageManager
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function ajouter(string $nom, string $message): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO messages (nom, message) VALUES (:nom, :message)");
        return $stmt->execute([
            "nom" => $nom,
            "message" => $message
        ]);
    }

    public function supprimer(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM messages WHERE id = :id");
        return $stmt->execute(["id" => $id]);
    }

    public function compterTous(string $recherche = ""): int
    {
        if ($recherche !== "") {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM messages WHERE nom LIKE :recherche");
            $stmt->execute(["recherche" => "%$recherche%"]);
        } else {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM messages");
        }
        return (int) $stmt->fetchColumn();
    }

    public function recuperer(string $recherche = "", int $limite = 5, int $decalage = 0): array
    {
        if ($recherche !== "") {
            $stmt = $this->pdo->prepare("SELECT * FROM messages WHERE nom LIKE :recherche ORDER BY date_creation DESC LIMIT :limite OFFSET :decalage");
            $stmt->bindValue("recherche", "%$recherche%", PDO::PARAM_STR);
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM messages ORDER BY date_creation DESC LIMIT :limite OFFSET :decalage");
        }
        $stmt->bindValue("limite", $limite, PDO::PARAM_INT);
        $stmt->bindValue("decalage", $decalage, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$manager = new MessageManager($pdo);
$erreur = "";
$succes = "";

// Suppression d'un message
if (isset($_GET["supprimer"])) {
    $id = (int) $_GET["supprimer"];
    $manager->supprimer($id);
    header("Location: index.php");
    exit;
}

// Traitement du formulaire (si soumis)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $message = trim($_POST["message"] ?? "");
    $piege = trim($_POST["site_web"] ?? ""); // Honeypot anti-spam

    if ($piege !== "") {
        // Un robot a rempli le champ invisible : on ignore silencieusement
        header("Location: index.php");
        exit;
    }

    if ($nom === "" || $message === "") {
        $erreur = "Le nom et le message sont obligatoires.";
    } elseif (mb_strlen($nom) > 100) {
        $erreur = "Le nom ne doit pas dépasser 100 caractères.";
    } elseif (mb_strlen($message) < 5) {
        $erreur = "Le message doit contenir au moins 5 caractères.";
    } elseif (mb_strlen($message) > 1000) {
        $erreur = "Le message ne doit pas dépasser 1000 caractères.";
    } else {
        $manager->ajouter($nom, $message);
        header("Location: index.php?succes=1");
        exit;
    }
}

if (isset($_GET["succes"])) {
    $succes = "Votre message a été ajouté avec succès !";
}

// Recherche via $_GET
$recherche = trim($_GET["recherche"] ?? "");

// Pagination
$parPage = 5;
$page = max(1, (int) ($_GET["page"] ?? 1));
$total = $manager->compterTous($recherche);
$totalPages = (int) ceil($total / $parPage);
$decalage = ($page - 1) * $parPage;

$messages = $manager->recuperer($recherche, $parPage, $decalage);
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

        <?php if ($erreur): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <input type="text" name="nom" placeholder="Votre nom" maxlength="100" required>
            <textarea name="message" placeholder="Votre message" maxlength="1000" required></textarea>
            <!-- Champ piège invisible pour les robots (honeypot) -->
            <input type="text" name="site_web" class="honeypot" tabindex="-1" autocomplete="off">
            <button type="submit">Envoyer</button>
        </form>

        <form method="GET" action="index.php" class="search-form">
            <input type="text" name="recherche" placeholder="Rechercher par nom..." value="<?= htmlspecialchars($recherche) ?>">
            <button type="submit">Rechercher</button>
        </form>

        <h2>Messages (<?= $total ?>)</h2>
        <ul class="message-list">
            <?php foreach ($messages as $msg): ?>
                <li>
                    <strong><?= htmlspecialchars($msg["nom"]) ?></strong>
                    <span class="date"><?= $msg["date_creation"] ?></span>
                    <p><?= htmlspecialchars($msg["message"]) ?></p>
                    <a href="index.php?supprimer=<?= $msg["id"] ?>" class="delete-link" onclick="return confirm('Supprimer ce message ?');">Supprimer</a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?page=<?= $i ?>&recherche=<?= urlencode($recherche) ?>"
                       class="<?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>