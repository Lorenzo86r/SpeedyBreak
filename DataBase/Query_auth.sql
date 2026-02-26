-- ==========================================
-- AUTENTICAZIONE
-- ==========================================

-- Registrazione nuovo utente
INSERT INTO SB_utente (nome, cognome, email, password_hash, telefono, ruolo)
VALUES (:nome, :cognome, :email, :password_hash, :telefono, 'customer');

-- Verifica se email già esistente
SELECT id_utente
FROM SB_utente
WHERE email = :email;

-- Login: recupero dati utente per verifica password in PHP (password_verify)
SELECT id_utente, nome, cognome, email, password_hash, ruolo
FROM SB_utente
WHERE email = :email;

-- Profilo utente loggato
SELECT id_utente, nome, cognome, email, telefono, ruolo
FROM SB_utente
WHERE id_utente = :id_utente;

-- Aggiornamento password (hash già generato in applicazione)
UPDATE SB_utente
SET password_hash = :nuovo_password_hash
WHERE id_utente = :id_utente;
