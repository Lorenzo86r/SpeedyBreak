<?php
// --- CONFIGURAZIONE DATABASE ---
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$db = "my_marcucci200";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

// --- SICUREZZA TABELLA ---
$allowed = ['SB_categoria', 'SB_prodotto', 'SB_utente', 'SB_ordine'];
$tabella = $_GET['tabella'] ?? 'SB_prodotto';

if (!in_array($tabella, $allowed)) {
    die("Tabella non valida");
}

$message = "";

// --- 1. LOGICA DELETE ---
if (isset($_GET['delete_id']) && isset($_GET['id_col'])) {
    $id_col = $_GET['id_col'];
    $id_val = intval($_GET['delete_id']);

    if ($conn->query("DELETE FROM $tabella WHERE $id_col = $id_val")) {
        $message = "<div class='alert alert-success'>Eliminato con successo!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Errore eliminazione: " . $conn->error . "</div>";
    }
}

// --- 2. LOGICA INSERT / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $azione = $_POST['azione'] ?? '';

    if ($tabella == 'SB_categoria') {

        $desc = $conn->real_escape_string($_POST['descrizione']);

        $sql = ($azione == 'add') 
            ? "INSERT INTO SB_categoria (descrizione) VALUES ('$desc')"
            : "UPDATE SB_categoria SET descrizione='$desc' WHERE id_categoria=" . intval($_POST['id']);
    } 

    elseif ($tabella == 'SB_prodotto') {

        $nome = $conn->real_escape_string($_POST['nome']);
        $prezzo = floatval($_POST['prezzo']);
        $cat = intval($_POST['id_categoria']);

        $sql = ($azione == 'add')
            ? "INSERT INTO SB_prodotto (nome, prezzo, id_categoria, giacenza) VALUES ('$nome', $prezzo, $cat, 1)"
            : "UPDATE SB_prodotto 
               SET nome='$nome', prezzo=$prezzo, id_categoria=$cat 
               WHERE id_prodotto=" . intval($_POST['id']);
    }

    elseif ($tabella == 'SB_utente') {

        $nome = $conn->real_escape_string($_POST['nome']);
        $email = $conn->real_escape_string($_POST['email']);
        $ruolo = $conn->real_escape_string($_POST['ruolo']);

        $sql = ($azione == 'add')
            ? "INSERT INTO SB_utente (nome, email, ruolo, password_hash) 
               VALUES ('$nome', '$email', '$ruolo', 'hash_default')"
            : "UPDATE SB_utente 
               SET nome='$nome', email='$email', ruolo='$ruolo' 
               WHERE id_utente=" . intval($_POST['id']);
    }

    // Controllo se SQL è stata creata
    if (!isset($sql)) {
        $message = "<div class='alert alert-danger'>Operazione non supportata per questa tabella.</div>";
    } 
    elseif ($conn->query($sql)) {
        $message = "<div class='alert alert-success'>Operazione riuscita!</div>";
    } 
    else {
        $message = "<div class='alert alert-danger'>Errore: " . $conn->error . "</div>";
    }
}

// --- 3. RECUPERO DATI ---
$query_tabella = $conn->query("SELECT * FROM $tabella");
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
<?php foreach ($campi as $f) echo "<th>" . ucfirst($f->name) . "</th>"; ?>
<th>Azioni</th>
</tr>
</thead>

<tbody>
<?php while ($row = $query_tabella->fetch_assoc()): 
$pk = $campi[0]->name;
$json_data = htmlspecialchars(json_encode($row));
?>

<tr>
<?php foreach ($campi as $f): ?>
<td><?= $row[$f->name] ?></td>
<?php endforeach; ?>

<td>
<button class="btn btn-sm btn-warning"
onclick='apriModalModifica(<?= $json_data ?>)'>
<i class="bi bi-pencil"></i>
</button>

<a href="?tabella=<?= $tabella ?>&delete_id=<?= $row[$pk] ?>&id_col=<?= $pk ?>"
class="btn btn-sm btn-danger"
onclick="return confirm('Eliminare?')">
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
<form method="POST" class="modal-content">

<div class="modal-header">
<h5 class="modal-title" id="modalTitle">Gestisci Record</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body" id="modalBody">
<input type="hidden" name="azione" id="formAzione">
<input type="hidden" name="id" id="formId">

<?php if($tabella == 'SB_categoria'): ?>
<label>Descrizione</label>
<input type="text" name="descrizione" id="input_descrizione" class="form-control" required>

<?php elseif($tabella == 'SB_prodotto'): ?>
<label>Nome</label>
<input type="text" name="nome" id="input_nome" class="form-control mb-2" required>
<label>Prezzo</label>
<input type="number" step="0.01" name="prezzo" id="input_prezzo" class="form-control mb-2" required>
<label>ID Categoria</label>
<input type="number" name="id_categoria" id="input_id_categoria" class="form-control" required>

<?php elseif($tabella == 'SB_utente'): ?>
<label>Nome</label>
<input type="text" name="nome" id="input_nome" class="form-control mb-2" required>
<label>Email</label>
<input type="email" name="email" id="input_email" class="form-control mb-2" required>
<label>Ruolo</label>
<select name="ruolo" id="input_ruolo" class="form-select">
<option value="customer">customer</option>
<option value="admin">admin</option>
</select>
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
const modal = new bootstrap.Modal(document.getElementById('crudModal'));

function apriModalAggiungi() {
document.getElementById('modalTitle').innerText = "Aggiungi Nuovo";
document.getElementById('formAzione').value = "add";
document.getElementById('formId').value = "";

document.querySelectorAll('#modalBody input:not([type=hidden]), #modalBody select')
.forEach(i => i.value = "");

modal.show();
}

function apriModalModifica(data) {
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
