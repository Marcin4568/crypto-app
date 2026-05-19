<?php

// =============================================
// Autoload alle klassen
// =============================================
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Coin.php';
require_once __DIR__ . '/../repositories/CoinRepository.php';
require_once __DIR__ . '/../services/CoinService.php';

$service = new CoinService();

// =============================================
// Controller: verwerk POST-acties
// =============================================
$feedback = null;  // ['type' => 'success'|'error', 'message' => '...']

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;

switch ($action) {
    case 'create':
        $result   = $service->createCoin(
            $_POST['name']        ?? '',
            $_POST['symbol']      ?? '',
            $_POST['description'] ?? ''
        );
        $feedback = ['type' => $result['success'] ? 'success' : 'error', 'message' => $result['message']];
        break;

    case 'update':
        $result   = $service->updateCoin(
            (int) ($_POST['id'] ?? 0),
            $_POST['name']        ?? '',
            $_POST['symbol']      ?? '',
            $_POST['description'] ?? ''
        );
        $feedback = ['type' => $result['success'] ? 'success' : 'error', 'message' => $result['message']];
        break;

    case 'delete':
        $result   = $service->deleteCoin($id);
        $feedback = ['type' => $result['success'] ? 'success' : 'error', 'message' => $result['message']];
        break;
}

// Data ophalen voor de weergave
$searchQuery = $_GET['search'] ?? '';
$coins       = $searchQuery ? $service->searchCoins($searchQuery) : $service->getAllCoins();
$editCoin    = ($action === 'edit' && $id) ? $service->getCoinById($id) : null;

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoApp</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        /* =============================================
           RESET & BASIS
        ============================================= */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:         #0a0a0f;
            --bg2:        #111118;
            --bg3:        #1a1a25;
            --border:     #2a2a3a;
            --accent:     #f0b429;
            --accent2:    #e05c2a;
            --green:      #22c55e;
            --red:        #ef4444;
            --text:       #e8e8f0;
            --muted:      #6b6b80;
            --radius:     8px;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Syne', sans-serif;
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(240,180,41,0.08), transparent),
                radial-gradient(ellipse 40% 30% at 90% 20%,  rgba(224,92,42,0.06),  transparent);
        }

        /* =============================================
           LAYOUT
        ============================================= */
        header {
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(17,17,24,0.8);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-family: 'Space Mono', monospace;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: -1px;
        }

        .logo span { color: var(--accent2); }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem;
        }

        .grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 2rem;
            align-items: start;
        }

        /* =============================================
           KAARTEN / PANELS
        ============================================= */
        .panel {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
        }

        .panel-title {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .panel-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* =============================================
           FORMULIER
        ============================================= */
        .form-group { margin-bottom: 1rem; }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.4rem;
        }

        input[type="text"], textarea {
            width: 100%;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 0.6rem 0.75rem;
            color: var(--text);
            font-family: 'Syne', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.2s;
            outline: none;
        }

        input[type="text"]:focus, textarea:focus {
            border-color: var(--accent);
        }

        textarea { resize: vertical; min-height: 80px; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 1.1rem;
            border-radius: var(--radius);
            border: none;
            font-family: 'Syne', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn:active { transform: scale(0.97); }
        .btn:hover  { opacity: 0.85; }

        .btn-primary { background: var(--accent);  color: #000; width: 100%; justify-content: center; }
        .btn-edit    { background: var(--bg3);      color: var(--text); border: 1px solid var(--border); }
        .btn-delete  { background: transparent;     color: var(--red);  border: 1px solid var(--red); }
        .btn-cancel  { background: var(--bg3);      color: var(--muted); border: 1px solid var(--border); display: block; text-align: center; margin-top: 0.5rem; }

        /* =============================================
           FEEDBACK
        ============================================= */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border-left: 3px solid;
        }

        .alert-success { background: rgba(34,197,94,0.1);  border-color: var(--green); color: var(--green); }
        .alert-error   { background: rgba(239,68,68,0.1);  border-color: var(--red);   color: var(--red); }

        /* =============================================
           COIN TABEL
        ============================================= */
        .search-bar {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .search-bar input { flex: 1; }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        thead tr {
            border-bottom: 2px solid var(--border);
        }

        th {
            text-align: left;
            padding: 0.6rem 0.75rem;
            font-size: 0.65rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        tbody tr:hover { background: var(--bg3); }

        td { padding: 0.8rem 0.75rem; vertical-align: middle; }

        .symbol-badge {
            display: inline-block;
            background: rgba(240,180,41,0.12);
            color: var(--accent);
            border: 1px solid rgba(240,180,41,0.3);
            border-radius: 4px;
            padding: 0.15rem 0.5rem;
            font-family: 'Space Mono', monospace;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .description-cell {
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--muted);
            font-size: 0.85rem;
        }

        .actions { display: flex; gap: 0.5rem; }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--muted);
        }

        .empty-state .icon { font-size: 2.5rem; margin-bottom: 0.75rem; }

        /* =============================================
           STATS BOVENAAN
        ============================================= */
        .stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            flex: 1;
        }

        .stat-label { font-size: 0.65rem; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); }
        .stat-value { font-size: 1.6rem; font-weight: 800; color: var(--accent); margin-top: 0.2rem; font-family: 'Space Mono', monospace; }
    </style>
</head>
<body>

<header>
    <div class="logo">crypto<span>APP</span></div>
</header>

<div class="container">

    <?php if ($feedback): ?>
        <div class="alert alert-<?= $feedback['type'] ?>">
            <?= htmlspecialchars($feedback['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats">
        <div class="stat">
            <div class="stat-label">Totaal coins</div>
            <div class="stat-value"><?= count($coins) ?></div>
        </div>
        <div class="stat">
            <div class="stat-label">Database</div>
            <div class="stat-value" style="font-size:1rem; padding-top:0.4rem; color:var(--green);">● Online</div>
        </div>
    </div>

    <div class="grid">

        <!-- ==================== FORMULIER ==================== -->
        <div>
            <div class="panel">
                <?php if ($editCoin): ?>
                    <!-- EDIT formulier -->
                    <div class="panel-title">Coin aanpassen</div>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id"     value="<?= $editCoin->getId() ?>">
                        <div class="form-group">
                            <label for="name">Naam</label>
                            <input type="text" id="name" name="name" required
                                   value="<?= htmlspecialchars($editCoin->getName()) ?>">
                        </div>
                        <div class="form-group">
                            <label for="symbol">Symbool</label>
                            <input type="text" id="symbol" name="symbol" required maxlength="10"
                                   placeholder="bijv. BTC"
                                   value="<?= htmlspecialchars($editCoin->getSymbol()) ?>">
                        </div>
                        <div class="form-group">
                            <label for="description">Beschrijving</label>
                            <textarea id="description" name="description"><?= htmlspecialchars($editCoin->getDescription()) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">💾 Opslaan</button>
                        <a href="index.php" class="btn btn-cancel">Annuleren</a>
                    </form>

                <?php else: ?>
                    <!-- NIEUW formulier -->
                    <div class="panel-title">Coin toevoegen</div>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="action" value="create">
                        <div class="form-group">
                            <label for="name">Naam</label>
                            <input type="text" id="name" name="name" required placeholder="bijv. Bitcoin">
                        </div>
                        <div class="form-group">
                            <label for="symbol">Symbool</label>
                            <input type="text" id="symbol" name="symbol" required maxlength="10" placeholder="bijv. BTC">
                        </div>
                        <div class="form-group">
                            <label for="description">Beschrijving</label>
                            <textarea id="description" name="description" placeholder="Korte beschrijving..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">＋ Toevoegen</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- ==================== TABEL ==================== -->
        <div>
            <div class="panel">
                <div class="panel-title">Alle coins</div>

                <!-- Zoekbalk -->
                <form method="GET" action="index.php" class="search-bar">
                    <input type="text" name="search" placeholder="Zoek op naam of symbool..."
                           value="<?= htmlspecialchars($searchQuery) ?>">
                    <button type="submit" class="btn btn-edit">Zoeken</button>
                    <?php if ($searchQuery): ?>
                        <a href="index.php" class="btn btn-edit">✕</a>
                    <?php endif; ?>
                </form>

                <div class="table-wrap">
                    <?php if (empty($coins)): ?>
                        <div class="empty-state">
                            <div class="icon">₿</div>
                            <p>Geen coins gevonden.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Naam</th>
                                    <th>Symbool</th>
                                    <th>Beschrijving</th>
                                    <th>Acties</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($coins as $coin): ?>
                                    <tr>
                                        <td style="color:var(--muted); font-family:'Space Mono',monospace; font-size:0.75rem;">
                                            <?= $coin->getId() ?>
                                        </td>
                                        <td style="font-weight:600;"><?= htmlspecialchars($coin->getName()) ?></td>
                                        <td><span class="symbol-badge"><?= htmlspecialchars($coin->getSymbol()) ?></span></td>
                                        <td class="description-cell"><?= htmlspecialchars($coin->getDescription()) ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="index.php?action=edit&id=<?= $coin->getId() ?>"
                                                   class="btn btn-edit">✏️</a>
                                                <a href="index.php?action=delete&id=<?= $coin->getId() ?>"
                                                   class="btn btn-delete"
                                                   onclick="return confirm('Weet je zeker dat je <?= htmlspecialchars($coin->getName()) ?> wilt verwijderen?')">🗑️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /.grid -->
</div><!-- /.container -->

</body>
</html>
