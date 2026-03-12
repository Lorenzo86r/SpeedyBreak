<?php
session_start();
if (!isset($_SESSION["ruolo"]) || $_SESSION["ruolo"] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}
// --- CONFIGURAZIONE DATABASE ---
$host = "localhost";
$user = "root";
$pass = "";
$db = "my_arevalo";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

// --- SICUREZZA TABELLA ---
$allowed = ['SB_categoria', 'SB_prodotto', 'SB_utente'];
$tabella = $_GET['tabella'] ?? 'SB_prodotto';
if (!in_array($tabella, $allowed)) {
    die("Tabella non valida");
}

$message = "";

// --- 1. RECUPERO CATEGORIE ---
$options_cat = [];
$res_cat = $conn->query("SELECT id_categoria, descrizione FROM SB_categoria ORDER BY descrizione ASC");
if ($res_cat) while ($c = $res_cat->fetch_assoc()) $options_cat[] = $c;

// --- 2. LOGICA DELETE ---
if (isset($_GET['delete_id']) && isset($_GET['id_col'])) {
    $id_col = $_GET['id_col'];
    $id_val = intval($_GET['delete_id']);
    if ($conn->query("DELETE FROM $tabella WHERE $id_col = $id_val")) {
        $message = "<div class='alert alert-success'>Eliminato con successo!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Errore: " . $conn->error . "</div>";
    }
}

// --- 3. LOGICA INSERT / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $azione = $_POST['azione'] ?? '';
    $sql = null;

    if ($tabella == 'SB_categoria') {
        $desc = $conn->real_escape_string($_POST['descrizione']);

        if ($azione == 'add') {
            $check = $conn->query("SELECT id_categoria FROM SB_categoria WHERE descrizione = '$desc' LIMIT 1");
            if ($check && $check->num_rows > 0) {
                $message = "<div class='alert alert-warning'>La categoria \"" . htmlspecialchars($_POST['descrizione']) . "\" esiste già!</div>";
                $sql = null;
            } else {
                $sql = "INSERT INTO SB_categoria (descrizione) VALUES ('$desc')";
            }
        } else {
            $sql = "UPDATE SB_categoria SET descrizione='$desc' WHERE id_categoria=" . intval($_POST['id']);
        }

    } elseif ($tabella == 'SB_prodotto') {
        $nome      = $conn->real_escape_string($_POST['nome']);
        $desc_prod = $conn->real_escape_string($_POST['descrizione']);
        $prezzo    = floatval($_POST['prezzo']);
        $cat       = intval($_POST['id_categoria']);
        $giacenza  = intval($_POST['giacenza']);
        $sql = ($azione == 'add')
            ? "INSERT INTO SB_prodotto (nome, descrizione, prezzo, id_categoria, giacenza) VALUES ('$nome', '$desc_prod', $prezzo, $cat, $giacenza)"
            : "UPDATE SB_prodotto SET nome='$nome', descrizione='$desc_prod', prezzo=$prezzo, id_categoria=$cat, giacenza=$giacenza WHERE id_prodotto=" . intval($_POST['id']);

    } elseif ($tabella == 'SB_utente') {
        $username = $conn->real_escape_string($_POST['username']);
        $email    = $conn->real_escape_string($_POST['email']);
        $ruolo    = $conn->real_escape_string($_POST['ruolo']);
        if ($azione == 'add') {
            $sql = "INSERT INTO SB_utente (username, email, ruolo, password_hash) VALUES ('$username', '$email', '$ruolo', 'hash_default')";
        } else {
            $sql = "UPDATE SB_utente SET username='$username', email='$email', ruolo='$ruolo' WHERE id_utente=" . intval($_POST['id']);
        }
    }

    if ($sql && $conn->query($sql)) {
        $message = "<div class='alert alert-success'>Operazione riuscita!</div>";
    } elseif ($sql) {
        $message = "<div class='alert alert-danger'>Errore: " . $conn->error . "</div>";
    }
}

// --- 4. RECUPERO DATI PER LA TABELLA ---
if ($tabella == 'SB_prodotto') {
    $query_sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo,
                  c.descrizione AS categoria, p.giacenza, p.id_categoria
                  FROM SB_prodotto p
                  LEFT JOIN SB_categoria c ON p.id_categoria = c.id_categoria";
} elseif ($tabella == 'SB_utente') {
    $query_sql = "SELECT id_utente, username, email, ruolo FROM SB_utente";
} else {
    $query_sql = "SELECT * FROM $tabella";
}

$query_tabella = $conn->query($query_sql);
if (!$query_tabella) die("Errore query: " . $conn->error . "<br>Query: " . $query_sql);

$campi = $query_tabella->fetch_fields();

// --- 5. CONTEGGIO PRODOTTI PER CATEGORIA (per alert JS) ---
$prodotti_per_categoria = [];
$res_count = $conn->query("SELECT id_categoria, COUNT(*) AS totale FROM SB_prodotto GROUP BY id_categoria");
if ($res_count) {
    while ($r = $res_count->fetch_assoc()) {
        $prodotti_per_categoria[$r['id_categoria']] = (int)$r['totale'];
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>SpeedyBreak Admin</title>
    <link rel="stylesheet" href="../../Assets/Styles/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-light">

    <nav class="navbar">
        <div class="nav-container">
            <div class="brand">
                <img src="../../Assets/Images/logo.png" alt="Logo Speedy Break">
                <span>Speedy Break</span>
            </div>
            <ul class="nav-links">
                <li><a href="../../index.php">Home</a></li>
                <li><a href="https://saqlain.altervista.org/SpeedyBreak/">Pagina Saqlain</a></li>
                <li><a href="../creazione_ordine/index_order.php">Ordina</a></li>
                <?php if(isset($_SESSION["ruolo"]) && ($_SESSION["ruolo"] === 'admin' || $_SESSION["ruolo"] === 'barista')): ?>
                    <li><a href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a class="active" href="admin.php">Admin</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION["user_id"])): ?>
                    <li><a class="login-btn" style="background-color: #dc3545;" href="../auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a class="login-btn" href="../auth/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container-fluid" style="margin-top: 20px;">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-dark min-vh-100 p-3 text-white">
                <h3 class="h5 mb-4 text-primary">SpeedyBreak</h3>
                <div class="nav flex-column nav-pills">
                    <a href="?tabella=SB_categoria" class="nav-link text-white <?= $tabella == 'SB_categoria' ? 'active' : '' ?>">Categorie</a>
                    <a href="?tabella=SB_prodotto"  class="nav-link text-white <?= $tabella == 'SB_prodotto'  ? 'active' : '' ?>">Prodotti</a>
                    <a href="?tabella=SB_utente"    class="nav-link text-white <?= $tabella == 'SB_utente'    ? 'active' : '' ?>">Utenti</a>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-10 p-4">
                <?= $message ?>
                <div class="d-flex justify-content-between mb-3">
                    <h2>Tabella: <?= str_replace('SB_', '', $tabella) ?></h2>
                    <button class="btn btn-primary" onclick="apriModalAggiungi()">+ Aggiungi</button>
                </div>

                <div class="card shadow">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <?php
                                foreach ($campi as $f) {
                                    if (in_array($f->name, ['id_categoria', 'id_utente', 'id_prodotto'])) continue;
                                    echo "<th>" . ucfirst($f->name) . "</th>";
                                }
                                ?>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $query_tabella->fetch_assoc()):
                                $pk = $campi[0]->name;
                                $json_data = htmlspecialchars(json_encode($row));
                            ?>
                                <tr>
                                    <?php foreach ($campi as $f):
                                        if (in_array($f->name, ['id_categoria', 'id_utente', 'id_prodotto'])) continue;
                                    ?>
                                        <td><?= htmlspecialchars($row[$f->name] ?? '') ?></td>
                                    <?php endforeach; ?>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick='apriModalModifica(<?= $json_data ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php
                                            $extra = '';
                                            if ($tabella === 'SB_categoria') {
                                                $id_cat = $row[$pk];
                                                $num_prod = $prodotti_per_categoria[$id_cat] ?? 0;
                                                $extra = "data-num-prodotti=\"$num_prod\"";
                                            }
                                        ?>
                                        <a href="?tabella=<?= $tabella ?>&delete_id=<?= $row[$pk] ?>&id_col=<?= $pk ?>"
                                            class="btn btn-sm btn-danger btn-elimina"
                                            <?= $extra ?>
                                            data-tabella="<?= $tabella ?>">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL CRUD -->
    <div class="modal fade" id="crudModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Gestisci Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <input type="hidden" name="azione" id="formAzione">
                    <input type="hidden" name="id"     id="formId">

                    <?php if ($tabella == 'SB_categoria'): ?>

                        <label class="form-label">Descrizione Categoria</label>
                        <input type="text" name="descrizione" id="input_descrizione" class="form-control" required>

                    <?php elseif ($tabella == 'SB_prodotto'): ?>

                        <label class="form-label">Nome Prodotto</label>
                        <input type="text" name="nome" id="input_nome" class="form-control mb-2" required>

                        <label class="form-label">Descrizione Prodotto</label>
                        <textarea name="descrizione" id="input_descrizione" class="form-control mb-2" rows="2"></textarea>

                        <label class="form-label">Prezzo (€)</label>
                        <input type="number" step="0.01" name="prezzo" id="input_prezzo" class="form-control mb-2" required>

                        <label class="form-label">Categoria</label>
                        <select name="id_categoria" id="input_id_categoria" class="form-select mb-2" required>
                            <option value="">-- Seleziona --</option>
                            <?php foreach ($options_cat as $c): ?>
                                <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['descrizione']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label class="form-label">Quantità Disponibile</label>
                        <input type="number" name="giacenza" id="input_giacenza" class="form-control" required>

                    <?php elseif ($tabella == 'SB_utente'): ?>

                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="input_username" class="form-control mb-2" required>

                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="input_email" class="form-control mb-2" required>

                        <label class="form-label">Ruolo</label>
                        <select name="ruolo" id="input_ruolo" class="form-select mb-2" required>
                            <option value="customer">Customer</option>
                            <option value="barista">Barista</option>
                            <option value="admin">Admin</option>
                        </select>

                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Salva</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalElement = document.getElementById('crudModal');
        const modal = new bootstrap.Modal(modalElement);

        function apriModalAggiungi() {
            modalElement.querySelector('form').reset();
            document.getElementById('modalTitle').innerText = "Aggiungi Nuovo";
            document.getElementById('formAzione').value = "add";
            document.getElementById('formId').value = "";
            modal.show();
        }

        function apriModalModifica(data) {
            modalElement.querySelector('form').reset();
            document.getElementById('modalTitle').innerText = "Modifica Record";
            document.getElementById('formAzione').value = "edit";

            const pkName = Object.keys(data)[0];
            document.getElementById('formId').value = data[pkName];

            for (let key in data) {
                let el = document.getElementById('input_' + key);
                if (!el) continue;
                if (el.type === 'datetime-local' && data[key]) {
                    el.value = data[key].replace(' ', 'T');
                } else {
                    el.value = data[key];
                }
            }
            modal.show();
        }

        // Alert eliminazione con conteggio prodotti per le categorie
        document.querySelectorAll('.btn-elimina').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const tabella = this.dataset.tabella;
                let messaggio = 'Eliminare questo record?';

                if (tabella === 'SB_categoria') {
                    const numProdotti = parseInt(this.dataset.numProdotti || '0');
                    if (numProdotti > 0) {
                        messaggio = `Attenzione! Questa categoria contiene ${numProdotti} prodott${numProdotti === 1 ? 'o' : 'i'} che verranno eliminat${numProdotti === 1 ? 'o' : 'i'} insieme ad essa.\n\nProcedere con l'eliminazione?`;
                    } else {
                        messaggio = 'Questa categoria non contiene prodotti. Eliminare?';
                    }
                }

                if (confirm(messaggio)) {
                    window.location.href = this.href;
                }
            });
        });
    </script>
</body>

</html>
