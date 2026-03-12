<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_utente = $_SESSION['user_id'];

$host="localhost";
$user="root";
$pass="";
$db="my_saqlain";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Errore connessione");
}

// Get all orders for the user, ordered by date descending
$stmt = $conn->prepare("SELECT id_ordine, data_ordine, stato, metodo, data_ritiro FROM SB_ordine WHERE id_utente = ? ORDER BY data_ordine DESC");
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$res = $stmt->get_result();

$ordini = [];
while ($row = $res->fetch_assoc()) {
    $ordini[] = $row;
}
$stmt->close();

// For each order, get a summary of items and the total price
$stmt_items = $conn->prepare("
    SELECT p.nome, d.quantita, p.prezzo 
    FROM SB_dettaglio_ordine d 
    JOIN SB_prodotto p ON d.id_prodotto = p.id_prodotto 
    WHERE d.id_ordine = ?
");

foreach ($ordini as &$ordine) {
    $stmt_items->bind_param("i", $ordine['id_ordine']);
    $stmt_items->execute();
    $res_items = $stmt_items->get_result();
    
    $items = [];
    $totale = 0;
    while ($item = $res_items->fetch_assoc()) {
        $items[] = $item;
        $totale += $item['prezzo'] * $item['quantita'];
    }
    $ordine['items'] = $items;
    $ordine['totale'] = $totale;
}
$stmt_items->close();

function bgStato($stato) {
    switch(strtolower($stato)) {
        case 'in attesa': return 'rgba(245, 158, 11, 0.1)';
        case 'pronto': return 'rgba(16, 185, 129, 0.1)';
        case 'ritirato': return 'rgba(59, 130, 246, 0.1)';
        case 'cancellato': return 'rgba(239, 68, 68, 0.1)';
        default: return '#f3f4f6';
    }
}
function colStato($stato) {
    switch(strtolower($stato)) {
        case 'in attesa': return '#f59e0b';
        case 'pronto': return '#10b981';
        case 'ritirato': return '#3b82f6';
        case 'cancellato': return '#ef4444';
        default: return '#6b7280';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Miei Ordini - SpeedyBreak</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <style>
        .page-header { background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(16, 185, 129, 0.05)); padding: 40px 20px; text-align: center; border-bottom: 1px solid var(--color-border); }
        .page-header h1 { font-size: 32px; font-weight: 700; color: var(--color-text); }
        .page-header p { color: var(--color-text-muted); margin-top: 8px; font-size: 16px; }
        
        .orders-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        
        .order-card { background: white; border-radius: 16px; padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px; border: 1px solid var(--color-border); transition: transform 0.2s, box-shadow 0.2s; }
        .order-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        
        .order-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--color-border); flex-wrap: wrap; gap: 10px; }
        .order-id-date { display: flex; flex-direction: column; gap: 4px; }
        .order-id { font-size: 18px; font-weight: 700; color: var(--color-text); }
        .order-date { font-size: 14px; color: var(--color-text-muted); }
        
        .order-status .badge { display: inline-block; padding: 6px 14px; border-radius: 99px; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .order-body { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px; }
        .order-items { flex: 1; min-width: 250px; }
        .item-row { display: flex; justify-content: space-between; font-size: 15px; margin-bottom: 8px; color: var(--color-text-muted); }
        .item-qty-name { display: flex; gap: 8px; }
        .item-qty { font-weight: 600; color: var(--color-primary); }
        
        .order-summary-box { background: var(--color-background); padding: 16px; border-radius: 12px; min-width: 200px; display: flex; flex-direction: column; justify-content: center; align-items: flex-start; gap: 12px;}
        .summary-row { display: flex; justify-content: space-between; width: 100%; font-size: 14px; }
        .summary-total { display: flex; justify-content: space-between; width: 100%; font-size: 18px; font-weight: 700; color: var(--color-text); margin-top: 4px; padding-top: 8px; border-top: 1px solid rgba(0,0,0,0.1); }
        
        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed var(--color-border); }
        .empty-state svg { color: var(--color-text-muted); opacity: 0.5; margin-bottom: 16px; }
        .empty-state h3 { font-size: 20px; color: var(--color-text); margin-bottom: 8px; }
        .empty-state p { color: var(--color-text-muted); margin-bottom: 24px; }
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
                <li>
                    <a class="nav-icon-btn active" href="../auth/profile.php" title="Area Personale">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <header class="page-header">
        <h1>I Miei Ordini</h1>
        <p>Visualizza lo storico e lo stato dei tuoi ordini passati e correnti.</p>
    </header>

    <main class="main-content">
        <div class="orders-container">
            
            <?php if (empty($ordini)): ?>
                <div class="empty-state animate-fade-in">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <h3>Nessun ordine trovato</h3>
                    <p>Non hai ancora effettuato nessun ordine con noi.</p>
                    <a href="../creazione_ordine/index_order.php" class="btn btn-primary">Fai il tuo primo ordine</a>
                </div>
            <?php else: ?>

                <?php foreach ($ordini as $index => $ordine): ?>
                <div class="order-card animate-fade-in" style="animation-delay: <?= $index * 0.1 ?>s; opacity: 0; animation-fill-mode: forwards;">
                    <div class="order-header">
                        <div class="order-id-date">
                            <span class="order-id">Ordine #<?= str_pad($ordine['id_ordine'], 5, '0', STR_PAD_LEFT) ?></span>
                            <span class="order-date"><?= date('d M Y, H:i', strtotime($ordine['data_ordine'])) ?></span>
                        </div>
                        <div class="order-status" style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge" style="background: <?= bgStato($ordine['stato']) ?>; color: <?= colStato($ordine['stato']) ?>">
                                <?= htmlspecialchars($ordine['stato']) ?>
                            </span>
                            
                            <?php if (strtolower($ordine['stato']) === 'in attesa'): ?>
                                <button onclick="cancelMyOrder(<?= $ordine['id_ordine'] ?>)" class="btn btn-sm" style="background: #fee2e2; color: var(--color-error); border: 1px solid rgba(239, 68, 68, 0.3); padding: 4px 10px; font-size: 12px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                    Cancella
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-items">
                            <?php foreach ($ordine['items'] as $item): ?>
                            <div class="item-row">
                                <div class="item-qty-name">
                                    <span class="item-qty"><?= $item['quantita'] ?>x</span>
                                    <span><?= htmlspecialchars($item['nome']) ?></span>
                                </div>
                                <span>€<?= number_format($item['prezzo'] * $item['quantita'], 2) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-summary-box">
                            <div class="summary-row">
                                <span style="color: var(--color-text-muted);">Ritiro previsto:</span>
                                <span style="font-weight: 500;"><?= date('H:i', strtotime($ordine['data_ritiro'])) ?></span>
                            </div>
                            <div class="summary-row">
                                <span style="color: var(--color-text-muted);">Pagamento:</span>
                                <span style="font-weight: 500;"><?= htmlspecialchars($ordine['metodo']) ?></span>
                            </div>
                            <div class="summary-total">
                                <span>Totale:</span>
                                <span>€<?= number_format($ordine['totale'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </main>

    <footer class="global-footer mt-auto">
        <p>Progetto Speedy Break - 5CIN &copy; 2026</p>
    </footer>

    <script>
    function cancelMyOrder(orderId) {
        if (!confirm('Sei sicuro di voler annullare questo ordine?')) {
            return;
        }
        
        // Show loading state (optional) or just send
        fetch('delete_my_ordine.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_ordine: orderId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Ordine cancellato con successo.');
                window.location.reload();
            } else {
                alert('Errore: ' + (data.message || 'impossibile cancellare l\'ordine.'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Errore di connessione durante la cancellazione.');
        });
    }
    </script>
</body>
</html>
