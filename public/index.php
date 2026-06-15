<?php

// =============================================
// Autoload
// =============================================
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Coin.php';
require_once __DIR__ . '/../repositories/CoinRepository.php';
require_once __DIR__ . '/../services/CoinService.php';

$service = new CoinService();

// =============================================
// Controller
// =============================================
$feedback = null;

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;

switch ($action) {

    case 'create':
        $result = $service->createCoin(
                $_POST['name'] ?? '',
                $_POST['symbol'] ?? '',
                $_POST['description'] ?? ''
        );
        $feedback = $result;
        break;

    case 'update':
        $result = $service->updateCoin(
                (int)($_POST['id'] ?? 0),
                $_POST['name'] ?? '',
                $_POST['symbol'] ?? '',
                $_POST['description'] ?? ''
        );
        $feedback = $result;
        break;

    case 'delete':
        $result = $service->deleteCoin($id);
        $feedback = $result;
        break;
}

// =============================================
// DATA
// =============================================
$searchQuery = $_GET['search'] ?? '';

$data = $service->getCoinsWithPrices();

$editCoin = ($action === 'edit' && $id)
        ? $service->getCoinById($id)
        : null;

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>CryptoApp</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

<header>
    <div class="logo">crypto<span>APP</span></div>
</header>

<div class="container">

    <?php if ($feedback): ?>
        <div class="alert alert-<?= $feedback['success'] ? 'success' : 'error' ?>">
            <?= htmlspecialchars($feedback['message']) ?>
        </div>
    <?php endif; ?>

    <!-- ================= STATS ================= -->
    <div class="stats">
        <div class="stat">
            <div class="stat-label">Totaal coins</div>
            <div class="stat-value"><?= count($data) ?></div>
        </div>
    </div>

    <div class="grid">

        <!-- ================= FORM ================= -->
        <div>
            <div class="panel">

                <?php if ($editCoin): ?>

                    <div class="panel-title">Coin aanpassen</div>

                    <form method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editCoin->getId() ?>">

                        <input type="text" name="name" value="<?= htmlspecialchars($editCoin->getName()) ?>" required>
                        <input type="text" name="symbol" value="<?= htmlspecialchars($editCoin->getSymbol()) ?>" required>
                        <textarea name="description"><?= htmlspecialchars($editCoin->getDescription()) ?></textarea>

                        <button type="submit">Opslaan</button>
                        <a href="index.php">Annuleren</a>
                    </form>

                <?php else: ?>

                    <div class="panel-title">Coin toevoegen</div>

                    <form method="POST">
                        <input type="hidden" name="action" value="create">

                        <input type="text" name="name" placeholder="Bitcoin" required>
                        <input type="text" name="symbol" placeholder="BTC" required>
                        <textarea name="description"></textarea>

                        <button type="submit">Toevoegen</button>
                    </form>

                <?php endif; ?>

            </div>
        </div>

        <!-- ================= TABLE ================= -->
        <div>
            <div class="panel">

                <div class="panel-title">Alle coins</div>

                <form method="GET">
                    <input type="text" name="search" placeholder="Zoek..." value="<?= htmlspecialchars($searchQuery) ?>">
                    <button type="submit">Zoek</button>
                </form>

                <table>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Naam</th>
                        <th>Symbool</th>
                        <th>Beschrijving</th>
                        <th>Prijs</th>
                        <th>24h</th>
                        <th>Acties</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($data as $row):
                        $coin = $row['coin'];
                        ?>
                        <tr>
                            <td><?= $coin->getId() ?></td>

                            <td><?= htmlspecialchars($coin->getName()) ?></td>

                            <td><?= htmlspecialchars($coin->getSymbol()) ?></td>

                            <td><?= htmlspecialchars($coin->getDescription()) ?></td>

                            <td>
                                <?= $row['price'] !== null
                                        ? '€' . number_format($row['price'], 2, ',', '.')
                                        : '-' ?>
                            </td>

                            <td>
                                <?php if ($row['change'] !== null): ?>
                                    <span style="color:<?= $row['change'] >= 0 ? 'green' : 'red' ?>">
                    <?= number_format($row['change'], 2) ?>%
                </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>

                            <td class="actions">
                                <a href="index.php?action=edit&id=<?= $coin->getId() ?>">✏️</a>
                                <a href="index.php?action=delete&id=<?= $coin->getId() ?>"
                                   onclick="return confirm('Verwijderen?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
