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
$db = "my_saqlain";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

// --- SICUREZZA TABELLA ---
$allowed = ['SB_categoria', 'SB_prodotto', 'SB_utente', 'SB_ordine'];
$tabella = $_GET['tabella'] ?? 'SB_prodotto';
if (!in_array($tabella, $allowed)) {
    die("Tabella non valida");
}

$message = "";

// --- 1. RECUPERO CATEGORIE ---
$options_cat = [];
$res_cat = $conn->query("SELECT id_categoria, descrizione FROM SB_categoria ORDER BY descrizione ASC");
if ($res_cat) while ($c = $res_cat->fetch_assoc()) $options_cat[] = $c;

// --- 1b. RECUPERO UTENTI ---
$options_utenti = [];
$res_utenti = $conn->query("SELECT id_utente, nome FROM SB_utente ORDER BY nome ASC");
if ($res_utenti) while ($u = $res_utenti->fetch_assoc()) $options_utenti[] = $u;

// --- 1c. RECUPERO PRODOTTI ---
$options_prodotti = [];
$res_prod = $conn->query("SELECT id_prodotto, nome FROM SB_prodotto ORDER BY nome ASC");
if ($res_prod) while ($p = $res_prod->fetch_assoc()) $options_prodotti[] = $p;

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
        $sql = ($azione == 'add')
            ? "INSERT INTO SB_categoria (descrizione) VALUES ('$desc')"
            : "UPDATE SB_categoria SET descrizione='$desc' WHERE id_categoria=" . intval($_POST['id']);
    } elseif ($tabella == 'SB_prodotto') {
        $nome = $conn->real_escape_string($_POST['nome']);
        $desc_prod = $conn->real_escape_string($_POST['descrizione']);
        $prezzo = floatval($_POST['prezzo']);
        $cat = intval($_POST['id_categoria']);
        $giacenza = intval($_POST['giacenza']);
        $sql = ($azione == 'add')
            ? "INSERT INTO SB_prodotto (nome, descrizione, prezzo, id_categoria, giacenza) VALUES ('$nome', '$desc_prod', $prezzo, $cat, $giacenza)"
            : "UPDATE SB_prodotto SET nome='$nome', descrizione='$desc_prod', prezzo=$prezzo, id_categoria=$cat, giacenza=$giacenza WHERE id_prodotto=" . intval($_POST['id']);
    } elseif ($tabella == 'SB_utente') {
        $nome = $conn->real_escape_string($_POST['nome']);
        $cognome = $conn->real_escape_string($_POST['cognome']);
        $email = $conn->real_escape_string($_POST['email']);
        $telefono = $conn->real_escape_string($_POST['telefono']);
        $ruolo = $conn->real_escape_string($_POST['ruolo']);
        if ($azione == 'add') {
            $sql = "INSERT INTO SB_utente (nome, cognome, email, telefono, ruolo, password_hash)
                    VALUES ('$nome', '$cognome', '$email', '$telefono', '$ruolo', 'hash_default')";
        } else {
            $sql = "UPDATE SB_utente SET
                        nome='$nome',
                        cognome='$cognome',
                        email='$email',
                        telefono='$telefono',
                        ruolo='$ruolo'
                    WHERE id_utente=" . intval($_POST['id']);
        }
    } elseif ($tabella == 'SB_ordine') {
        $id_utente = intval($_POST['id_utente']);
        $stato = $conn->real_escape_string($_POST['stato']);
        $metodo = $conn->real_escape_string($_POST['metodo']);
        $nota = $conn->real_escape_string($_POST['nota']);
        $data_ritiro = $conn->real_escape_string($_POST['data_ritiro']);
        $sql = ($azione == 'add')
            ? "INSERT INTO SB_ordine (id_utente, stato, metodo, nota, data_ritiro) VALUES ($id_utente, '$stato', '$metodo', '$nota', '$data_ritiro')"
            : "UPDATE SB_ordine SET id_utente=$id_utente, stato='$stato', metodo='$metodo', nota='$nota', data_ritiro='$data_ritiro' WHERE id_ordine=" . intval($_POST['id']);
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
} elseif ($tabella == 'SB_ordine') {
    $query_sql = "SELECT o.id_ordine, u.nome AS utente, o.data_ordine, o.stato,
                         o.metodo, o.nota, o.data_ritiro, o.id_utente
                  FROM SB_ordine o
                  LEFT JOIN SB_utente u ON o.id_utente = u.id_utente";
} else {
    $query_sql = "SELECT * FROM $tabella";
}

// --- Controllo errore query ---
$query_tabella = $conn->query($query_sql);
if (!$query_tabella) die("Errore query: " . $conn->error . "<br>Query: " . $query_sql);

$campi = $query_tabella->fetch_fields();
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
                <li><a href="../creazione_ordine/index_order.php">Ordina</a></li>
                <?php if(isset($_SESSION["ruolo"]) && $_SESSION["ruolo"] === 'admin'): ?>
                    <li><a href="../gestione_ordini/manage.php">Gestione Ordini</a></li>
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
            <div class="col-md-2 bg-dark min-vh-100 p-3 text-white">
                <h3 class="h5 mb-4 text-primary">SpeedyBreak</h3>
                <div class="nav flex-column nav-pills">
                    <a href="?tabella=SB_categoria" class="nav-link text-white <?= $tabella == 'SB_categoria' ? 'active' : '' ?>">Categorie</a>
                    <a href="?tabella=SB_prodotto" class="nav-link text-white <?= $tabella == 'SB_prodotto' ? 'active' : '' ?>">Prodotti</a>
                    <a href="?tabella=SB_utente" class="nav-link text-white <?= $tabella == 'SB_utente' ? 'active' : '' ?>">Utenti</a>
                    <a href="?tabella=SB_ordine" class="nav-link text-white <?= $tabella == 'SB_ordine' ? 'active' : '' ?>">Ordini</a>
                </div>
            </div>

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
                                    if ($f->name == 'id_categoria' || $f->name == 'id_utente' || $f->name == 'id_prodotto') continue;
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
                                        if ($f->name == 'id_categoria' || $f->name == 'id_utente' || $f->name == 'id_prodotto') continue;
                                    ?>
                                        <td><?= $row[$f->name] ?></td>
                                    <?php endforeach; ?>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick='apriModalModifica(<?= $json_data ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?tabella=<?= $tabella ?>&delete_id=<?= $row[$pk] ?>&id_col=<?= $pk ?>"
                                            class="btn btn-sm btn-danger" onclick="return confirm('Eliminare?')">
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

    <div class="modal fade" id="crudModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Gestisci Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <input type="hidden" name="azione" id="formAzione">
                    <input type="hidden" name="id" id="formId">

                    <?php if ($tabella == 'SB_categoria'): ?>
                        <label>Descrizione Categoria</label>
                        <input type="text" name="descrizione" id="input_descrizione" class="form-control" required>

                    <?php elseif ($tabella == 'SB_prodotto'): ?>
                        <label>Nome Prodotto</label>
                        <input type="text" name="nome" id="input_nome" class="form-control mb-2" required>
                        <label>Descrizione Prodotto</label>
                        <textarea name="descrizione" id="input_descrizione" class="form-control mb-2" rows="2"></textarea>
                        <label>Prezzo (€)</label>
                        <input type="number" step="0.01" name="prezzo" id="input_prezzo" class="form-control mb-2" required>
                        <label>Categoria</label>
                        <select name="id_categoria" id="input_id_categoria" class="form-select mb-2" required>
                            <option value="">-- Seleziona --</option>
                            <?php foreach ($options_cat as $c): ?>
                                <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['descrizione']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Quantità Disponibile</label>
                        <input type="number" name="giacenza" id="input_giacenza" class="form-control" required>

                    <?php elseif ($tabella == 'SB_utente'): ?>
                        <label>Nome</label>
                        <input type="email" name="email" id="input_email" class="form-control mb-2" required>
                        <label>Telefono</label>
                        <select name="ruolo" id="input_ruolo" class="form-select mb-2" required>
                            <option value="">-- Seleziona Ruolo --</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="cliente">Cliente</option>
                        </select>

                    <?php elseif ($tabella == 'SB_ordine'): ?>
                        <label>Utente</label>
                        <select name="id_utente" id="input_id_utente" class="form-select mb-2" required>
                            <option value="">-- Seleziona Utente --</option>
                            <?php foreach ($options_utenti as $u): ?>
                                <option value="<?= $u['id_utente'] ?>"><?= htmlspecialchars($u['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Stato</label>
                        <select name="stato" id="input_stato" class="form-select mb-2" required>
                            <option value="">-- Seleziona Stato --</option>
                            <option value="In attesa">In attesa</option>
                            <option value="In Preparazione">In Preparazione</option>
                            <option value="Pronto">Pronto</option>
                            <option value="Completato">Completato</option>
                            <option value="Annullato">Annullato</option>
                        </select>
                        <label>Metodo di Pagamento</label>
                        <select name="metodo" id="input_metodo" class="form-select mb-2" required>
                            <option value="">-- Seleziona Metodo --</option>
                            <option value="Contanti">Contanti</option>
                            <option value="Carta di Credito">Carta di Credito</option>
                            <option value="Satispay">Satispay</option>
                        </select>
                        <label>Note</label>
                        <textarea name="nota" id="input_nota" class="form-control mb-2" rows="2"></textarea>
                        <label>Data Ritiro</label>
                        <input type="datetime-local" name="data_ritiro" id="input_data_ritiro" class="form-control" required>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
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
                if (el) el.value = data[key];
            }
            modal.show();
        }
    </script>
</body>

</html>
