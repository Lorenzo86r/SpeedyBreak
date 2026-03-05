<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Gestione Ordini</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
  <div class="container">
     <a class="navbar-brand d-flex align-items-center" href="#">
    <img src="Assets\Images\logo.png" alt="Logo Speedy Break" height="45" class="me-2">
    <span class="fw-bold">Speedy Break</span>
</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="./Pages/creazione_ordine/ordine.html">Ordina</a></li>
        <li class="nav-item"><a class="nav-link" href="./Pages/gestione_ordini/gestione-ordine.php">Gestione Ordini</a></li>
        <li class="nav-item"><a class="nav-link" href="./Pages/amministrazione/admin.php">Admin</a></li>
        <li class="nav-item">
          <?php if(isset($_SESSION["user_id"])): ?>
            <a class="btn btn-danger ms-2" href="./Pages/auth/logout.php">Logout</a>
          <?php else: ?>
            <a class="btn btn-warning ms-2" href="./Pages/auth/login.php">Login</a>
          <?php endif; ?>
        </li>
      </ul>
    </div>
  </div>
</nav>


<header class="bg-light text-center py-5">
    <div class="container">
        <h1 class="display-4 fw-bold">Benvenuto in Speedy Break ☕</h1>
    
    </div>
</header>

<!-- Parte centrale -->
<main class="container my-5">

    <div class="text-center mb-4">
        <h2>Presentazione del Progetto</h2>
        <p class="text-muted">
            Questo sito permette di inserire nuovi ordini, visualizzare quelli esistenti
            e controllare lo stato delle richieste in modo efficiente.
        </p>
    </div>

    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title"> Organizzazione</h5>
                    <p class="card-text">Gestione ordinata di tutti gli ordini ricevuti.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title"> Velocità</h5>
                    <p class="card-text">Riduce i tempi di attesa e migliora il servizio.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title"> Precisione</h5>
                    <p class="card-text">Diminuisce gli errori negli ordini.</p>
                </div>
            </div>
        </div>
    </div>

</main>

<!-- FOOTER -->
<footer class="bg-dark text-white text-center py-3">
    <p class="mb-0">Progetto Speedy Break - 5CIN © 2026</p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>