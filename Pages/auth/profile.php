<?php 
session_start(); 

// --- CONFIGURAZIONE DATABASE PER STATISTICHE ---
$host = "localhost";
$user = "root";
$pass = "";
$db = "my_saqlain";

$totale_ordini = 0;
$spesa_totale = 0;

if (isset($_SESSION["user_id"])) {
    $conn = new mysqli($host, $user, $pass, $db);
    if (!$conn->connect_error) {
        $user_id = intval($_SESSION["user_id"]);
        
        // Fetch user details (nome, cognome, email)
        $sql_user = "SELECT nome, cognome, email, saldo FROM SB_utente WHERE id_utente = $user_id";
        $res_user = $conn->query($sql_user);
        if ($res_user && $row_u = $res_user->fetch_assoc()) {
            $db_nome = $row_u['nome'] ?? '';
            $db_cognome = $row_u['cognome'] ?? '';
            $db_email = $row_u['email'] ?? '';
            $db_saldo = (float)($row_u['saldo'] ?? 0);
            // Sync session
            $_SESSION['nome'] = $db_nome;
            $_SESSION['cognome'] = $db_cognome;
        }

        // Check if org email
        $org_domains = ['aldini.istruzioneer.it', 'avbo.it'];
        $user_email = $_SESSION['email'] ?? '';
        $email_domain = substr(strrchr($user_email, '@'), 1);
        $is_org = in_array(strtolower($email_domain), $org_domains);
        $profile_incomplete = empty($db_nome) || empty($db_cognome);

        // Calcola totale ordini
        $sql_ordini = "SELECT COUNT(*) as tot FROM SB_ordine WHERE id_utente = $user_id";
        $res_ordini = $conn->query($sql_ordini);
        if ($res_ordini && $row = $res_ordini->fetch_assoc()) {
            $totale_ordini = $row['tot'];
        }
        
        // Calcola spesa totale
        $sql_spesa = "
            SELECT SUM(d.quantita * p.prezzo) as spesa
            FROM SB_ordine o
            JOIN SB_dettaglio_ordine d ON o.id_ordine = d.id_ordine
            JOIN SB_prodotto p ON d.id_prodotto = p.id_prodotto
            WHERE o.id_utente = $user_id AND o.stato != 'Annullato'
        ";
        $res_spesa = $conn->query($sql_spesa);
        if ($res_spesa && $row = $res_spesa->fetch_assoc()) {
            $spesa_totale = $row['spesa'] ? (float)$row['spesa'] : 0;
        }
        
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Area Personale - SpeedyBreak</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  </head>
  <body>
    <!-- Nav -->
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
                <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a class="nav-item" href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a class="nav-item" href="../amministrazione/admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a class="nav-item" href="../amministrazione/statistiche.php">Statistiche</a></li>
                <li>
                    <a class="nav-icon-btn active" href="profile.php" title="Area Personale">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <main class="main-content container" style="padding-top: var(--space-8); padding-bottom: var(--space-12);">
      <div class="animate-fade-in" style="max-width: 800px; margin: 0 auto;">
          <?php if(isset($_SESSION["user_id"])): ?>
              <header style="background: linear-gradient(135deg, var(--color-primary), #f59e0b); border-radius: var(--radius-xl); padding: var(--space-12) var(--space-8); text-align: center; color: white; margin-bottom: var(--space-8); position: relative; overflow: hidden; box-shadow: var(--shadow-lg);">
                  <!-- Decorative circle for pattern -->
                  <div style="position: absolute; top: -100px; right: -50px; width: 300px; height: 300px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                  <div style="position: absolute; bottom: -50px; left: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                  
                  <?php 
                     $username = $_SESSION['username'] ?? 'Utente';
                     $nome = $_SESSION['nome'] ?? '';
                     $cognome = $_SESSION['cognome'] ?? '';
                     $full_name = trim("$nome $cognome");
                     $display_name = $full_name ?: $username;
                     $initial = strtoupper(substr($display_name, 0, 1)); 
                     $ruolo = $_SESSION['ruolo'] ?? 'studente';
                  ?>
                  
                  <div style="position: relative; z-index: 1;">
                      <div style="width: 100px; height: 100px; background: white; color: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: 700; margin: 0 auto var(--space-4) auto; box-shadow: 0 8px 20px rgba(0,0,0,0.15);">
                          <?php echo $initial; ?>
                      </div>
                      <h2 style="color: white; font-size: 32px; margin-bottom: var(--space-2); letter-spacing: -0.02em; font-weight: 700;">Bentornato, <?php echo htmlspecialchars($display_name); ?>!</h2>
                      <p style="opacity: 0.9; margin: 0; font-size: var(--font-size-md); text-transform: capitalize; font-weight: 500;">Ruolo: <?php echo htmlspecialchars($ruolo); ?></p>
                  </div>
              </header>
              
              <?php if(isset($_GET['msg']) && $_GET['msg'] == 'pwd_success'): ?>
                  <div class="alert alert-success mb-6 justify-center" style="border-radius: var(--radius-md);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Password aggiornata con successo!
                  </div>
              <?php endif; ?>
              <?php if(isset($_GET['msg']) && $_GET['msg'] == 'profile_saved'): ?>
                  <div class="alert alert-success mb-6 justify-center" style="border-radius: var(--radius-md);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Profilo aggiornato con successo!
                  </div>
              <?php endif; ?>

              <?php if (!$is_org && $profile_incomplete): ?>
              <!-- BANNER COMPLETA PROFILO -->
              <div style="background: #fffbeb; border: 1px solid #fbbf24; border-left: 4px solid #f59e0b; border-radius: var(--radius-md); padding: var(--space-5) var(--space-6); margin-bottom: var(--space-6); display: flex; align-items: flex-start; gap: 12px;">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                  <div style="flex: 1;">
                      <strong style="color: #92400e; font-size: 15px;">Completa il tuo profilo</strong>
                      <p style="color: #92400e; font-size: 13px; margin: 4px 0 12px; opacity: 0.85;">Inserisci il tuo nome e cognome per completare la registrazione.</p>
                      <form action="save_profile.php" method="POST" style="display: flex; gap: 8px; flex-wrap: wrap;">
                          <input type="text" name="nome" placeholder="Nome" required class="form-control" style="flex: 1; min-width: 120px; height: 38px; font-size: 14px;" value="<?= htmlspecialchars($db_nome) ?>">
                          <input type="text" name="cognome" placeholder="Cognome" required class="form-control" style="flex: 1; min-width: 120px; height: 38px; font-size: 14px;" value="<?= htmlspecialchars($db_cognome) ?>">
                          <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 20px;">Salva</button>
                      </form>
                  </div>
              </div>
              <?php endif; ?>

              <!-- Statistiche -->
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
                  
                  <!-- Card Ordini -->
                  <div style="background: white; border-radius: var(--radius-xl); padding: var(--space-8); border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s;" class="hover:shadow-md hover:-translate-y-1">
                      <div>
                          <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: var(--space-2);">Ordini Totali</p>
                          <h3 style="font-size: 2.5rem; color: var(--color-secondary); font-weight: 800; line-height: 1;"><?= $totale_ordini ?></h3>
                      </div>
                      <div style="width: 72px; height: 72px; background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(249, 115, 22, 0.2)); color: var(--color-primary); border-radius: 20px; display: flex; align-items: center; justify-content: center; transform: rotate(-5deg);">
                          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                      </div>
                  </div>

                  <!-- Card Spesa -->
                  <div style="background: white; border-radius: var(--radius-xl); padding: var(--space-8); border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s;" class="hover:shadow-md hover:-translate-y-1">
                      <div>
                          <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: var(--space-2);">Spesa Totale</p>
                          <h3 style="font-size: 2.5rem; color: var(--color-secondary); font-weight: 800; line-height: 1;">€<?= number_format($spesa_totale, 2, ',', '.') ?></h3>
                      </div>
                      <div style="width: 72px; height: 72px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.2)); color: #16a34a; border-radius: 20px; display: flex; align-items: center; justify-content: center; transform: rotate(5deg);">
                          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                      </div>
                  </div>

                  <!-- Card Saldo -->
                  <div style="background: linear-gradient(135deg, #1e3a5f, #2563eb); border-radius: var(--radius-xl); padding: var(--space-8); border: none; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25); display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s;" class="hover:shadow-md hover:-translate-y-1">
                      <div>
                          <p style="color: rgba(255,255,255,0.7); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: var(--space-2);">Il Tuo Saldo</p>
                          <h3 id="saldo-display" style="font-size: 2.5rem; color: white; font-weight: 800; line-height: 1;">€<?= number_format($db_saldo, 2, ',', '.') ?></h3>
                      </div>
                      <div style="width: 72px; height: 72px; background: rgba(255,255,255,0.15); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; transform: rotate(-5deg);">
                          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path><path d="M18 12a2 2 0 0 0 0 4h4v-4z"></path></svg>
                      </div>
                  </div>

              </div>
              
              <!-- Dati Personali -->
              <div style="background: white; border-radius: var(--radius-xl); border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: var(--space-6);">
                  <div style="padding: var(--space-6) var(--space-8); border-bottom: 1px solid var(--color-border);">
                      <h3 style="font-size: var(--font-size-lg); color: var(--color-secondary); font-weight: 700; margin: 0;">Dati Personali</h3>
                  </div>
                  <div style="padding: var(--space-6) var(--space-8);">
                      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                          <div>
                              <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Nome</p>
                              <p style="font-weight: 600; font-size: 16px; color: var(--color-secondary);"><?= htmlspecialchars($db_nome ?: '—') ?></p>
                          </div>
                          <div>
                              <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Cognome</p>
                              <p style="font-weight: 600; font-size: 16px; color: var(--color-secondary);"><?= htmlspecialchars($db_cognome ?: '—') ?></p>
                          </div>
                          <div>
                              <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Email</p>
                              <p style="font-weight: 600; font-size: 16px; color: var(--color-secondary);"><?= htmlspecialchars($_SESSION['email'] ?? '—') ?></p>
                          </div>
                          <div>
                              <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Username</p>
                              <p style="font-weight: 600; font-size: 16px; color: var(--color-secondary);"><?= htmlspecialchars($_SESSION['username'] ?? '—') ?></p>
                          </div>
                      </div>
                      <?php if ($is_org): ?>
                      <p style="color: var(--color-text-muted); font-size: 12px; margin-top: 16px; display: flex; align-items: center; gap: 6px;">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                          Nome e cognome derivati dall'email istituzionale e non modificabili.
                      </p>
                      <?php endif; ?>
                  </div>
              </div>

              <!-- Ricarica Saldo -->
              <div style="background: white; border-radius: var(--radius-xl); border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: var(--space-6);">
                  <div style="padding: var(--space-6) var(--space-8); border-bottom: 1px solid var(--color-border);">
                      <h3 style="font-size: var(--font-size-lg); color: var(--color-secondary); font-weight: 700; margin: 0;">Ricarica Saldo</h3>
                  </div>
                  <div style="padding: var(--space-6) var(--space-8);">
                      <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); margin-bottom: var(--space-4);">Seleziona un metodo di pagamento e l'importo da ricaricare.</p>
                      
                      <!-- Payment Method Icons -->
                      <div style="display: flex; gap: 12px; margin-bottom: var(--space-5); flex-wrap: wrap;">
                          <label style="flex: 1; min-width: 100px; cursor: pointer;">
                              <input type="radio" name="recharge-method" value="gpay" checked style="display: none;">
                              <div class="recharge-method-card" style="border: 2px solid var(--color-border); border-radius: var(--radius-md); padding: 14px 12px; text-align: center; transition: all 0.2s; background: white;">
                                  <svg width="32" height="32" viewBox="0 0 24 24" style="margin: 0 auto 6px;">
                                      <rect width="24" height="24" rx="4" fill="#f3f4f6"/>
                                      <text x="12" y="16" text-anchor="middle" font-size="9" font-weight="700" fill="#4285F4">G</text>
                                  </svg>
                                  <span style="font-size: 12px; font-weight: 600; color: var(--color-text-muted);">Google Pay</span>
                              </div>
                          </label>
                          <label style="flex: 1; min-width: 100px; cursor: pointer;">
                              <input type="radio" name="recharge-method" value="apple" style="display: none;">
                              <div class="recharge-method-card" style="border: 2px solid var(--color-border); border-radius: var(--radius-md); padding: 14px 12px; text-align: center; transition: all 0.2s; background: white;">
                                  <svg width="32" height="32" viewBox="0 0 24 24" style="margin: 0 auto 6px;">
                                      <rect width="24" height="24" rx="4" fill="#f3f4f6"/>
                                      <text x="12" y="16" text-anchor="middle" font-size="10" font-weight="700" fill="#000">A</text>
                                  </svg>
                                  <span style="font-size: 12px; font-weight: 600; color: var(--color-text-muted);">Apple Pay</span>
                              </div>
                          </label>
                          <label style="flex: 1; min-width: 100px; cursor: pointer;">
                              <input type="radio" name="recharge-method" value="card" style="display: none;">
                              <div class="recharge-method-card" style="border: 2px solid var(--color-border); border-radius: var(--radius-md); padding: 14px 12px; text-align: center; transition: all 0.2s; background: white;">
                                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="1.5" style="margin: 0 auto 6px;">
                                      <rect x="1" y="4" width="22" height="16" rx="3"/>
                                      <line x1="1" y1="10" x2="23" y2="10"/>
                                  </svg>
                                  <span style="font-size: 12px; font-weight: 600; color: var(--color-text-muted);">Carta</span>
                              </div>
                          </label>
                      </div>

                      <!-- Quick Amount Buttons -->
                      <div style="display: flex; gap: 8px; margin-bottom: var(--space-4); flex-wrap: wrap;">
                          <button type="button" onclick="setRechargeAmount(5)" class="btn btn-secondary" style="padding: 6px 16px; font-size: 14px;">€5</button>
                          <button type="button" onclick="setRechargeAmount(10)" class="btn btn-secondary" style="padding: 6px 16px; font-size: 14px;">€10</button>
                          <button type="button" onclick="setRechargeAmount(20)" class="btn btn-secondary" style="padding: 6px 16px; font-size: 14px;">€20</button>
                          <button type="button" onclick="setRechargeAmount(50)" class="btn btn-secondary" style="padding: 6px 16px; font-size: 14px;">€50</button>
                      </div>

                      <div style="display: flex; gap: 10px; align-items: center;">
                          <input type="number" id="recharge-amount" min="0.01" max="500" step="0.01" placeholder="Importo (€)" class="form-control" style="flex: 1; height: 44px; font-size: 16px;">
                          <button type="button" onclick="rechargeSaldo()" class="btn btn-primary" style="padding: 10px 24px; white-space: nowrap; height: 44px;" id="recharge-btn">Ricarica</button>
                      </div>
                      <div id="recharge-msg" style="margin-top: 10px;"></div>
                  </div>
              </div>

              <!-- Trasferisci Saldo -->
              <div style="background: white; border-radius: var(--radius-xl); border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: var(--space-6);">
                  <div style="padding: var(--space-6) var(--space-8); border-bottom: 1px solid var(--color-border);">
                      <h3 style="font-size: var(--font-size-lg); color: var(--color-secondary); font-weight: 700; margin: 0;">Trasferisci Saldo</h3>
                  </div>
                  <div style="padding: var(--space-6) var(--space-8);">
                      <p style="color: var(--color-text-muted); font-size: var(--font-size-sm); margin-bottom: var(--space-4);">Invia saldo a un altro utente inserendo il suo username o email.</p>
                      <div style="display: flex; flex-direction: column; gap: 10px;">
                          <input type="text" id="transfer-recipient" placeholder="Username o email del destinatario" class="form-control" style="height: 44px; font-size: 15px;">
                          <div style="display: flex; gap: 10px; align-items: center;">
                              <input type="number" id="transfer-amount" min="0.01" step="0.01" placeholder="Importo (€)" class="form-control" style="flex: 1; height: 44px; font-size: 16px;">
                              <button type="button" onclick="transferSaldo()" class="btn btn-primary" style="padding: 10px 24px; white-space: nowrap; height: 44px;" id="transfer-btn">Trasferisci</button>
                          </div>
                      </div>
                      <div id="transfer-msg" style="margin-top: 10px;"></div>
                  </div>
              </div>

              <!-- Impostazioni Account -->
              <div style="background: white; border-radius: var(--radius-xl); border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden;">
                  <div style="padding: var(--space-6) var(--space-8); border-bottom: 1px solid var(--color-border);">
                      <h3 style="font-size: var(--font-size-lg); color: var(--color-secondary); font-weight: 700; margin: 0;">Impostazioni Account</h3>
                  </div>
                  <div class="flex flex-col">
                      <a href="update_password.php" style="padding: var(--space-6) var(--space-8); display: flex; align-items: center; text-decoration: none; border-bottom: 1px solid var(--color-border); transition: background 0.2s;" class="hover:bg-gray-50">
                          <div style="width: 48px; height: 48px; background: rgba(249, 115, 22, 0.1); color: var(--color-primary); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-right: var(--space-4);">
                              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                              </svg>
                          </div>
                          <div>
                              <span style="display: block; color: var(--color-secondary); font-weight: 600; font-size: var(--font-size-md); margin-bottom: 2px;">Cambia Password</span>
                              <span style="display: block; color: var(--color-text-muted); font-size: var(--font-size-sm);">Aggiorna le tue credenziali di accesso</span>
                          </div>
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: auto; color: var(--color-text-light);"><polyline points="9 18 15 12 9 6"></polyline></svg>
                      </a>
                      <a href="logout.php" style="padding: var(--space-6) var(--space-8); display: flex; align-items: center; text-decoration: none; transition: background 0.2s;" class="hover:bg-red-50">
                          <div style="width: 48px; height: 48px; background: #fef2f2; color: var(--color-error); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-right: var(--space-4);">
                              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                  <polyline points="16 17 21 12 16 7"></polyline>
                                  <line x1="21" y1="12" x2="9" y2="12"></line>
                              </svg>
                          </div>
                          <div>
                              <span style="display: block; color: var(--color-error); font-weight: 600; font-size: var(--font-size-md); margin-bottom: 2px;">Disconnetti</span>
                              <span style="display: block; color: rgba(239, 68, 68, 0.8); font-size: var(--font-size-sm);">Esci dal tuo account in modo sicuro</span>
                          </div>
                      </a>
                  </div>
              </div>
          <?php else: ?>
              <!-- Unauthenticated State -->
              <div class="auth-card" style="padding: var(--space-12) var(--space-8); text-align: center; border-radius: var(--radius-xl);">
                  <header style="margin-bottom: var(--space-8);">
                      <div style="width: 80px; height: 80px; background: var(--color-primary-light); color: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-6) auto;">
                         <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                      </div>
                      <h2 style="font-size: 32px; color: var(--color-secondary); margin-bottom: var(--space-3); letter-spacing: -0.02em; font-weight: 700;">Area Personale</h2>
                      <p style="color: var(--color-text-muted); font-size: var(--font-size-lg);">Accedi o registrati per gestire i tuoi ordini e visualizzare le tue statistiche.</p>
                  </header>

                  <div class="flex gap-4 justify-center" style="max-width: 500px; margin: 0 auto; flex-wrap: wrap;">
                      <a href="login.php" class="btn btn-primary flex-1 p-4 justify-center" style="font-size: var(--font-size-md);">
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: var(--space-2);">
                              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                              <polyline points="10 17 15 12 10 7"></polyline>
                              <line x1="15" y1="12" x2="3" y2="12"></line>
                           </svg>
                          <span>Login</span>
                      </a>
                      <a href="signup.php" class="btn btn-secondary flex-1 p-4 justify-center" style="font-size: var(--font-size-md);">
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: var(--space-2);">
                              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                              <circle cx="8.5" cy="7" r="4"></circle>
                              <line x1="20" y1="8" x2="20" y2="14"></line>
                              <line x1="23" y1="11" x2="17" y2="11"></line>
                          </svg>
                          <span>Registrati</span>
                      </a>
                  </div>
              </div>
          <?php endif; ?>
      </div>
    </main>
    
    <footer class="global-footer mt-auto">
        <p>&copy; 2026 SpeedyBreak. Tutti i diritti riservati.</p>
    </footer>

    <style>
        /* Recharge method card selection styling */
        input[name="recharge-method"]:checked + .recharge-method-card {
            border-color: #2563eb;
            background: rgba(37, 99, 235, 0.05);
            box-shadow: 0 0 0 1px #2563eb;
        }
        .recharge-method-card:hover {
            border-color: #93c5fd;
            background: #f8fafc;
        }
    </style>

    <script>
    function setRechargeAmount(val) {
        document.getElementById('recharge-amount').value = val;
    }

    function updateSaldoDisplay(saldo) {
        const el = document.getElementById('saldo-display');
        if (el) el.textContent = '€' + saldo.toFixed(2).replace('.', ',');
    }

    function showMsg(elementId, message, isSuccess) {
        const el = document.getElementById(elementId);
        el.innerHTML = `<div class="alert ${isSuccess ? 'alert-success' : 'alert-error'}" style="border-radius: var(--radius-md); font-size: 14px; padding: 10px 14px;">${message}</div>`;
        setTimeout(() => { el.innerHTML = ''; }, 5000);
    }

    function rechargeSaldo() {
        const amount = parseFloat(document.getElementById('recharge-amount').value);
        if (!amount || amount <= 0) {
            showMsg('recharge-msg', 'Inserisci un importo valido.', false);
            return;
        }
        const btn = document.getElementById('recharge-btn');
        btn.disabled = true;
        btn.textContent = 'Caricamento...';

        fetch('saldo_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'recharge', amount: amount })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                updateSaldoDisplay(data.saldo);
                showMsg('recharge-msg', data.message, true);
                document.getElementById('recharge-amount').value = '';
            } else {
                showMsg('recharge-msg', data.message, false);
            }
        })
        .catch(() => showMsg('recharge-msg', 'Errore di rete.', false))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Ricarica';
        });
    }

    function transferSaldo() {
        const recipient = document.getElementById('transfer-recipient').value.trim();
        const amount = parseFloat(document.getElementById('transfer-amount').value);
        if (!recipient) {
            showMsg('transfer-msg', 'Inserisci il destinatario.', false);
            return;
        }
        if (!amount || amount <= 0) {
            showMsg('transfer-msg', 'Inserisci un importo valido.', false);
            return;
        }

        if (!confirm(`Trasferire €${amount.toFixed(2)} a "${recipient}"?`)) return;

        const btn = document.getElementById('transfer-btn');
        btn.disabled = true;
        btn.textContent = 'Invio...';

        fetch('saldo_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'transfer', recipient: recipient, amount: amount })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                updateSaldoDisplay(data.saldo);
                showMsg('transfer-msg', data.message, true);
                document.getElementById('transfer-recipient').value = '';
                document.getElementById('transfer-amount').value = '';
            } else {
                showMsg('transfer-msg', data.message, false);
            }
        })
        .catch(() => showMsg('transfer-msg', 'Errore di rete.', false))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Trasferisci';
        });
    }
    </script>
  </body>
</html>
