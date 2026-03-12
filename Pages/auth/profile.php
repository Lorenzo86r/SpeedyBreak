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
                <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a class="nav-item" href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
                    <li><a class="nav-item" href="../gestione_ordini/storico_ordini.php">Storico</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a class="nav-item" href="../amministrazione/admin.php">Admin</a></li>
                <?php endif; ?>
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
                     $initial = strtoupper(substr($username, 0, 1)); 
                     $ruolo = $_SESSION['ruolo'] ?? 'studente';
                  ?>
                  
                  <div style="position: relative; z-index: 1;">
                      <div style="width: 100px; height: 100px; background: white; color: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: 700; margin: 0 auto var(--space-4) auto; box-shadow: 0 8px 20px rgba(0,0,0,0.15);">
                          <?php echo $initial; ?>
                      </div>
                      <h2 style="color: white; font-size: 32px; margin-bottom: var(--space-2); letter-spacing: -0.02em; font-weight: 700;">Bentornato, <?php echo htmlspecialchars($username); ?>!</h2>
                      <p style="opacity: 0.9; margin: 0; font-size: var(--font-size-md); text-transform: capitalize; font-weight: 500;">Ruolo: <?php echo htmlspecialchars($ruolo); ?></p>
                  </div>
              </header>
              
              <?php if(isset($_GET['msg']) && $_GET['msg'] == 'pwd_success'): ?>
                  <div class="alert alert-success mb-6 justify-center" style="border-radius: var(--radius-md);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Password aggiornata con successo!
                  </div>
              <?php endif; ?>

              <!-- Statistiche -->
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-6); margin-bottom: var(--space-8);">
                  
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

              </div>
              
              <!-- Impostazioni Account -->
              <div style="background: white; border-radius: var(--radius-xl); border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden;">
                  <div style="padding: var(--space-6) var(--space-8); border-bottom: 1px solid border-color-light; background: var(--color-bg);">
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
  </body>
</html>
