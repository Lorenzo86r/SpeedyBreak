<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$is_admin = ($_SESSION['ruolo'] ?? '') === 'admin';

require_once __DIR__ . '/../config.php';

$conn = get_mysqli();

// 1. Incassi della giornata corrente (solo admin)
$totale_oggi = 0;
if ($is_admin) {
    $stmt_incassi = $conn->prepare("
        SELECT SUM(p.prezzo * d.quantita) as totale_oggi 
        FROM SB_ordine o
        JOIN SB_dettaglio_ordine d ON o.id_ordine = d.id_ordine
        JOIN SB_prodotto p ON d.id_prodotto = p.id_prodotto
        WHERE DATE(o.data_ordine) = CURDATE() AND o.stato != 'Cancellato'
    ");
    $stmt_incassi->execute();
    $res_incassi = $stmt_incassi->get_result();
    $totale_oggi = $res_incassi->fetch_assoc()['totale_oggi'] ?? 0;
    $stmt_incassi->close();
}

// 2. Classifica di chi ha fatto più ordini in assoluto (visibile a tutti)
$top_utenti = [];
$stmt_top_utenti = $conn->prepare("
    SELECT u.username, u.email, COUNT(o.id_ordine) as num_ordini
    FROM SB_utente u
    JOIN SB_ordine o ON u.id_utente = o.id_utente
    WHERE o.stato != 'Cancellato'
    GROUP BY u.id_utente
    ORDER BY num_ordini DESC
    LIMIT 10
");
$stmt_top_utenti->execute();
$res_utenti = $stmt_top_utenti->get_result();
while ($r = $res_utenti->fetch_assoc()) {
    $top_utenti[] = $r;
}
$stmt_top_utenti->close();

// 3. Classifica prodotti più venduti
$stmt_top_prod = $conn->prepare("
    SELECT p.nome, SUM(d.quantita) as totale_venduti
    FROM SB_prodotto p
    JOIN SB_dettaglio_ordine d ON p.id_prodotto = d.id_prodotto
    JOIN SB_ordine o ON d.id_ordine = o.id_ordine
    WHERE o.stato != 'Cancellato'
    GROUP BY p.id_prodotto
    ORDER BY totale_venduti DESC
    LIMIT 10
");
$stmt_top_prod->execute();
$res_prod = $stmt_top_prod->get_result();
$top_prodotti = [];
while ($r = $res_prod->fetch_assoc()) {
    $top_prodotti[] = $r;
}
$stmt_top_prod->close();

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiche - Amministrazione</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(8, 145, 178, 0.05), rgba(79, 70, 229, 0.05));
            padding: 40px 20px;
            text-align: center;
            border-bottom: 1px solid var(--color-border);
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-text);
        }

        .page-header p {
            color: var(--color-text-muted);
            margin-top: 8px;
            font-size: 16px;
        }

        .dashboard-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .kpi-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .kpi-value {
            font-size: 48px;
            font-weight: 700;
            color: #10b981;
            line-height: 1;
            margin: 15px 0;
        }

        .kpi-label {
            font-size: 18px;
            color: var(--color-text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .stats-card {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .stats-header {
            background: var(--color-background);
            padding: 20px;
            border-bottom: 1px solid var(--color-border);
        }

        .stats-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--color-text);
        }

        .stats-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .stats-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid var(--color-border);
        }

        .stats-item:last-child {
            border-bottom: none;
        }

        .stats-rank {
            width: 30px;
            height: 30px;
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            margin-right: 15px;
        }

        .stats-name {
            flex: 1;
            font-weight: 500;
        }

        .stats-value {
            font-weight: 700;
            color: var(--color-text);
            background: rgba(0, 0, 0, 0.05);
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="nav-container container">
            <a href="../../index.php" class="brand">
                <img src="../../Assets/Images/logo.png" alt="Logo Speedy Break">
                <span>Speedy Break</span>
            </a>
            <ul class="nav-links">
                <li><a class="nav-item" href="../../index.php">Home</a></li>
                <li><a class="nav-item" href="../creazione_ordine/index_order.php">Ordina</a></li>
                <li><a class="nav-item" href="../ordini/my_ordini.php">I Miei Ordini</a></li>

                <?php if (isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a class="nav-item" href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
                <?php endif; ?>

                <?php if (isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a class="nav-item" href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a class="nav-item active" href="statistiche.php">Statistiche</a></li>
                <li>
                    <a class="nav-icon-btn" href="../auth/profile.php" title="Area Personale">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <header class="page-header">
        <h1>Dashboard Statistiche</h1>
        <p>Panoramica delle vendite e degli ordini dell'istituto.</p>
    </header>

    <main class="main-content">
        <div class="dashboard-container">

            <?php if ($is_admin): ?>
                <div class="kpi-card animate-fade-in">
                    <div class="kpi-label">Incassi di Oggi</div>
                    <div class="kpi-value">€<?= number_format($totale_oggi, 2) ?></div>
                    <div style="color: var(--color-text-muted); font-size: 14px;">Basato sugli ordini non cancellati di data
                        odierna.</div>
                </div>
            <?php endif; ?>

            <div class="grid-2">
                <div class="stats-card animate-fade-in"
                    style="animation-delay: 0.1s; opacity: 0; animation-fill-mode: forwards;">
                    <div class="stats-header">
                        <h3>🏆 Top Clienti (Più ordini)</h3>
                    </div>
                    <ul class="stats-list">
                        <?php if (empty($top_utenti)): ?>
                            <li class="stats-item"><span style="color: var(--color-text-muted);">Nessun dato
                                    disponibile</span></li>
                        <?php else: ?>
                            <?php foreach ($top_utenti as $i => $u): ?>
                                <li class="stats-item">
                                    <div style="display: flex; align-items: center; flex: 1;">
                                        <div class="stats-rank"><?= $i + 1 ?></div>
                                        <div class="stats-name">
                                            <?= htmlspecialchars($u['username'] ?? 'Utente Anonimo') ?>
                                            <div style="font-size: 12px; color: var(--color-text-muted); font-weight: 400;">
                                                <?= htmlspecialchars($u['email']) ?></div>
                                        </div>
                                    </div>
                                    <div class="stats-value"><?= $u['num_ordini'] ?> ordini</div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="stats-card animate-fade-in"
                    style="animation-delay: 0.2s; opacity: 0; animation-fill-mode: forwards;">
                    <div class="stats-header">
                        <h3>🍔 Prodotti Più Venduti</h3>
                    </div>
                    <ul class="stats-list">
                        <?php if (empty($top_prodotti)): ?>
                            <li class="stats-item"><span style="color: var(--color-text-muted);">Nessun dato
                                    disponibile</span></li>
                        <?php else: ?>
                            <?php foreach ($top_prodotti as $i => $p): ?>
                                <li class="stats-item">
                                    <div style="display: flex; align-items: center; flex: 1;">
                                        <div class="stats-rank" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                            <?= $i + 1 ?></div>
                                        <div class="stats-name"><?= htmlspecialchars($p['nome']) ?></div>
                                    </div>
                                    <div class="stats-value"><?= $p['totale_venduti'] ?> unità</div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

        </div>
    </main>

    <footer class="global-footer mt-auto">
        <p>Progetto Speedy Break - 5CIN &copy; 2026 | Admin Dashboard</p>
    </footer>

</body>

</html>