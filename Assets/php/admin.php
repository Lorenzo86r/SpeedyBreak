<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amministrazione SpeedyBreak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="text-center mb-4">
            <h1><i class="bi bi-gear"></i> Pannello Admin</h1>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-auto">
                <div class="btn-group" role="group">
                    <a href="?tabella=SB_categoria" class="btn btn-outline-primary btn-lg">Categorie</a>
                    <a href="?tabella=SB_utente" class="btn btn-outline-primary btn-lg">Utenti</a>
                    <a href="?tabella=SB_prodotto" class="btn btn-outline-primary btn-lg">Prodotti</a>
                    <a href="?tabella=SB_ordine" class="btn btn-outline-primary btn-lg">Ordini</a>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title text-center"> Seleziona una tabella sopra </h3>
                
                <div class="table-responsive mt-4">
                    <p class="text-center text-muted">Nessun dato visualizzato al momento.</p>
                </div>
            </div>
        </div>
    
    </div>

        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>