<?php
// --- CONFIGURAZIONE DATABASE ---
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$db = "my_input789";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

// --- SICUREZZA TABELLA ---
$allowed = ['SB_categoria', 'SB_prodotto', 'SB_utente', 'SB_ordine', 'SB_dettaglio_ordine'];
$tabella = $_GET['tabella'] ?? 'SB_prodotto';
if (!in_array($tabella, $allowed)) die("Tabella non valida");

$message = "";

// --- RECUPERO DATI PER SELECT ---
$options_cat = [];
$res_cat = $conn->query("SELECT id_categoria, descrizione FROM SB_categoria ORDER BY descrizione ASC");
if($res_cat) while ($c = $res_cat->fetch_assoc()) $options_cat[] = $c;

$options_utenti = [];
$res_utenti = $conn->query("SELECT id_utente, nome FROM SB_utente ORDER BY nome ASC");
if($res_utenti) while ($u = $res_utenti->fetch_assoc()) $options_utenti[] = $u;

$options_prodotti = [];
$res_prod = $conn->query("SELECT id_prodotto, nome FROM SB_prodotto ORDER BY nome ASC");
if($res_prod) while ($p = $res_prod->fetch_assoc()) $options_prodotti[] = $p;

$options_ordini = [];
$res_ord = $conn->query("SELECT id_ordine FROM SB_ordine ORDER BY id_ordine ASC");
if($res_ord) while ($o = $res_ord->fetch_assoc()) $options_ordini[] = $o;

// --- FIX BUG 1: DELETE con redirect pulito ---
if (isset($_GET['delete_id']) && isset($_GET['id_col'])) {
    $id_col = $_GET['id_col'];
    $id_val = intval($_GET['delete_id']);

    $allowed_cols = ['id_categoria', 'id_prodotto', 'id_utente', 'id_ordine'];
    if (!in_array($id_col, $allowed_cols)) die("Colonna non valida");

    if ($conn->query("DELETE FROM $tabella WHERE $id_col = $id_val")) {
        header("Location: ?tabella=$tabella&msg=deleted");
        exit;
    } else {
        $message = "<div class='alert alert-danger'>Errore eliminazione: " . $conn->error . "</div>";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $message = "<div class='alert alert-success'>Eliminato con successo!</div>";
}

// --- INSERT / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $azione = $_POST['azione'] ?? '';
    $sql = null;

    if ($tabella == 'SB_categoria') {
        $desc = $conn->real_escape_string($_POST['descrizione']);
        $sql = ($azione == 'add')
            ? "INSERT INTO SB_categoria (descrizione) VALUES ('$desc')"
            : "UPDATE SB_categoria SET descrizione='$desc' WHERE id_categoria=" . intval($_POST['id']);
    }
    elseif ($tabella == 'SB_prodotto') {
        $nome      = $conn->real_escape_string($_POST['nome']);
        $desc_prod = $conn->real_escape_string($_POST['descrizione']);
        $prezzo    = floatval($_POST['prezzo']);
        $cat       = intval($_POST['id_categoria']);
        $giacenza  = intval($_POST['giacenza']);
        $sql = ($azione == 'add')
            ? "INSERT INTO SB_prodotto (nome, descrizione, prezzo, id_categoria, giacenza) VALUES ('$nome', '$desc_prod', $prezzo, $cat, $giacenza)"
            : "UPDATE SB_prodotto SET nome='$nome', descrizione='$desc_prod', prezzo=$prezzo, id_categoria=$cat, giacenza=$giacenza WHERE id_prodotto=" . intval($_POST['id']);
    }
    elseif ($tabella == 'SB_utente') {
        $nome     = $conn->real_escape_string($_POST['nome']);
        $cognome  = $conn->real_escape_string($_POST['cognome']);
        $email    = $conn->real_escape_string($_POST['email']);
        $telefono = $conn->real_escape_string($_POST['telefono']);
        $ruolo    = $conn->real_escape_string($_POST['ruolo']);
        if ($azione == 'add') {
            $sql = "INSERT INTO SB_utente (nome, cognome, email, telefono, ruolo, password_hash)
                    VALUES ('$nome', '$cognome', '$email', '$telefono', '$ruolo', 'hash_default')";
        } else {
            $sql = "UPDATE SB_utente SET nome='$nome', cognome='$cognome', email='$email',
                        telefono='$telefono', ruolo='$ruolo'
                    WHERE id_utente=" . intval($_POST['id']);
        }
    }
    elseif ($tabella == 'SB_ordine') {
        $id_utente   = intval($_POST['id_utente']);
        $stato       = $conn->real_escape_string($_POST['stato']);
        $metodo      = $conn->real_escape_string($_POST['metodo']);
        $nota        = $conn->real_escape_string($_POST['nota']);
        $data_ordine = $conn->real_escape_string($_POST['data_ordine']);
        $data_ritiro = $conn->real_escape_string($_POST['data_ritiro']);
        if ($azione == 'add') {
            $sql = "INSERT INTO SB_ordine (id_utente, stato, metodo, nota, data_ordine, data_ritiro)
                    VALUES ($id_utente, '$stato', '$metodo', '$nota', '$data_ordine', '$data_ritiro')";
        } else {
            $sql = "UPDATE SB_ordine SET id_utente=$id_utente, stato='$stato', metodo='$metodo',
                        nota='$nota', data_ordine='$data_ordine', data_ritiro='$data_ritiro'
                    WHERE id_ordine=" . intval($_POST['id']);
        }
    }
    elseif ($tabella == 'SB_dettaglio_ordine') {
        $id_ordine   = intval($_POST['id_ordine']);
        $id_prodotto = intval($_POST['id_prodotto']);
        $quantita    = intval($_POST['quantita']);
        if ($azione == 'add') {
            $sql = "INSERT INTO SB_dettaglio_ordine (id_ordine, id_prodotto, quantita)
                    VALUES ($id_ordine, $id_prodotto, $quantita)";
        } else {
            // PK composta: id_ordine + id_prodotto
            $old_id_ordine   = intval($_POST['old_id_ordine']);
            $old_id_prodotto = intval($_POST['old_id_prodotto']);
            $sql = "UPDATE SB_dettaglio_ordine SET id_ordine=$id_ordine, id_prodotto=$id_prodotto, quantita=$quantita
                    WHERE id_ordine=$old_id_ordine AND id_prodotto=$old_id_prodotto";
        }
    }

    if ($sql && $conn->query($sql)) {
        $message = "<div class='alert alert-success'>Operazione riuscita!</div>";
    } elseif ($sql) {
        $message = "<div class='alert alert-danger'>Errore: " . $conn->error . "</div>";
    }
}

// --- QUERY VISUALIZZAZIONE ---
if ($tabella == 'SB_prodotto') {
    $query_sql = "SELECT p.id_prodotto, p.nome, p.descrizione, p.prezzo,
                  c.descrizione AS categoria, p.giacenza, p.id_categoria
                  FROM SB_prodotto p
                  LEFT JOIN SB_categoria c ON p.id_categoria = c.id_categoria";

} elseif ($tabella == 'SB_ordine') {
    $query_sql = "SELECT o.id_ordine, u.nome AS utente, o.stato, o.metodo,
                         o.nota, o.data_ordine, o.data_ritiro, o.id_utente
                  FROM SB_ordine o
                  LEFT JOIN SB_utente u ON o.id_utente = u.id_utente";

} elseif ($tabella == 'SB_dettaglio_ordine') {
    $query_sql = "SELECT d.id_ordine, p.nome AS prodotto, d.quantita, d.id_prodotto
                  FROM SB_dettaglio_ordine d
                  LEFT JOIN SB_prodotto p ON d.id_prodotto = p.id_prodotto";

} else {
    $query_sql = "SELECT * FROM $tabella";
}

$query_tabella = $conn->query($query_sql);
if (!$query_tabella) die("Errore query: " . $conn->error . "<br>Query: " . $query_sql);

$campi = $query_tabella->fetch_fields();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>SpeedyBreak Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 bg-dark min-vh-100 p-3 text-white">
            <h3 class="h5 mb-4 text-primary">SpeedyBreak</h3>
            <div class="nav flex-column nav-pills">
                <a href="?tabella=SB_categoria"         class="nav-link text-white <?= $tabella=='SB_categoria'         ? 'active':'' ?>">Categorie</a>
                <a href="?tabella=SB_prodotto"          class="nav-link text-white <?= $tabella=='SB_prodotto'          ? 'active':'' ?>">Prodotti</a>
                <a href="?tabella=SB_utente"            class="nav-link text-white <?= $tabella=='SB_utente'            ? 'active':'' ?>">Utenti</a>
                <a href="?tabella=SB_ordine"            class="nav-link text-white <?= $tabella=='SB_ordine'            ? 'active':'' ?>">Ordini</a>
                <a href="?tabella=SB_dettaglio_ordine"  class="nav-link text-white <?= $tabella=='SB_dettaglio_ordine'  ? 'active':'' ?>">Dettagli Ordine</a>
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
                            <?php foreach ($campi as $f): 
                                if (in_array($f->name, ['id_categoria','id_utente','id_prodotto'])) continue;
                                echo "<th>" . ucfirst($f->name) . "</th>";
                            endforeach; ?>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $query_tabella->fetch_assoc()):
                            $json_data = htmlspecialchars(json_encode($row));
                            // Gestione PK: normale o composta per dettaglio_ordine
                            if ($tabella == 'SB_dettaglio_ordine') {
                                $delete_params = "delete_id={$row['id_ordine']}&id_col=id_ordine&id_prodotto_del={$row['id_prodotto']}";
                            } else {
                                $pk = $campi[0]->name;
                                $delete_params = "delete_id={$row[$pk]}&id_col=$pk";
                            }
                        ?>
                        <tr>
                            <?php foreach ($campi as $f): 
                                if (in_array($f->name, ['id_categoria','id_utente','id_prodotto'])) continue;
                            ?>
                                <td><?= htmlspecialchars($row[$f->name] ?? '') ?></td>
                            <?php endforeach; ?>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='apriModalModifica(<?= $json_data ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?tabella=<?= $tabella ?>&<?= $delete_params ?>"
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

<!-- MODAL -->
<div class="modal fade" id="crudModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="?tabella=<?= $tabella ?>" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Gestisci Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="azione" id="formAzione">
                <input type="hidden" name="id"     id="formId">

                <?php if($tabella == 'SB_categoria'): ?>
                    <label class="form-label">Descrizione</label>
                    <input type="text" name="descrizione" id="input_descrizione" class="form-control" required>

                <?php elseif($tabella == 'SB_prodotto'): ?>
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" id="input_nome" class="form-control mb-2" required>
                    <label class="form-label">Descrizione</label>
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
                    <label class="form-label">Giacenza</label>
                    <input type="number" name="giacenza" id="input_giacenza" class="form-control" required>

                <?php elseif($tabella == 'SB_utente'): ?>
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" id="input_nome" class="form-control mb-2" required>
                    <label class="form-label">Cognome</label>
                    <input type="text" name="cognome" id="input_cognome" class="form-control mb-2" required>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="input_email" class="form-control mb-2" required>
                    <label class="form-label">Telefono</label>
                    <input type="text" name="telefono" id="input_telefono" class="form-control mb-2">
                    <label class="form-label">Ruolo</label>
                    <select name="ruolo" id="input_ruolo" class="form-select" required>
                        <option value="">-- Seleziona --</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                        <option value="cliente">Cliente</option>
                    </select>

                <?php elseif($tabella == 'SB_ordine'): ?>
                    <label class="form-label">Utente</label>
                    <select name="id_utente" id="input_id_utente" class="form-select mb-2" required>
                        <option value="">-- Seleziona Utente --</option>
                        <?php foreach ($options_utenti as $u): ?>
                            <option value="<?= $u['id_utente'] ?>"><?= htmlspecialchars($u['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-label">Stato</label>
                    <select name="stato" id="input_stato" class="form-select mb-2" required>
                        <option value="">-- Seleziona Stato --</option>
                        <option value="In Preparazione">In Preparazione</option>
                        <option value="Pronto">Pronto</option>
                        <option value="Completato">Completato</option>
                        <option value="Annullato">Annullato</option>
                    </select>
                    <label class="form-label">Metodo Pagamento</label>
                    <select name="metodo" id="input_metodo" class="form-select mb-2" required>
                        <option value="">-- Seleziona Metodo --</option>
                        <option value="Contanti">Contanti</option>
                        <option value="Carta di Credito">Carta di Credito</option>
                        <option value="Bancomat">Bancomat</option>
                    </select>
                    <label class="form-label">Nota</label>
                    <input type="text" name="nota" id="input_nota" class="form-control mb-2">
                    <label class="form-label">Data Ordine</label>
                    <input type="datetime-local" name="data_ordine" id="input_data_ordine" class="form-control mb-2" required>
                    <label class="form-label">Data Ritiro</label>
                    <input type="datetime-local" name="data_ritiro" id="input_data_ritiro" class="form-control">

                <?php elseif($tabella == 'SB_dettaglio_ordine'): ?>
                    <!-- Hidden per PK composta in modifica -->
                    <input type="hidden" name="old_id_ordine"   id="input_old_id_ordine">
                    <input type="hidden" name="old_id_prodotto" id="input_old_id_prodotto">
                    <label class="form-label">Ordine #</label>
                    <select name="id_ordine" id="input_id_ordine" class="form-select mb-2" required>
                        <option value="">-- Seleziona Ordine --</option>
                        <?php foreach ($options_ordini as $o): ?>
                            <option value="<?= $o['id_ordine'] ?>">Ordine #<?= $o['id_ordine'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-label">Prodotto</label>
                    <select name="id_prodotto" id="input_id_prodotto" class="form-select mb-2" required>
                        <option value="">-- Seleziona Prodotto --</option>
                        <?php foreach ($options_prodotti as $p): ?>
                            <option value="<?= $p['id_prodotto'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-label">Quantità</label>
                    <input type="number" name="quantita" id="input_quantita" class="form-control" min="1" required>
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

    // Salva i valori originali della PK composta per SB_dettaglio_ordine
    if (document.getElementById('input_old_id_ordine')) {
        document.getElementById('input_old_id_ordine').value   = data['id_ordine']   ?? '';
        document.getElementById('input_old_id_prodotto').value = data['id_prodotto'] ?? '';
    }

    for (let key in data) {
        let el = document.getElementById('input_' + key);
        if (el) el.value = data[key];
    }
    modal.show();
}
</script>
</body>
</html>
