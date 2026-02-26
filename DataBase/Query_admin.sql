-- ==========================================
-- ADMIN - GESTIONE DATABASE
-- ==========================================

-- ---------- UTENTI ----------

-- Lista utenti
SELECT
	id_utente,
	nome,
	cognome,
	email,
	telefono,
	ruolo
FROM SB_utente
ORDER BY id_utente DESC;

-- Cerca utenti per nome/cognome/email
SELECT
	id_utente,
	nome,
	cognome,
	email,
	telefono,
	ruolo
FROM SB_utente
WHERE nome LIKE CONCAT('%', :ricerca, '%')
	OR cognome LIKE CONCAT('%', :ricerca, '%')
	OR email LIKE CONCAT('%', :ricerca, '%')
ORDER BY cognome, nome;

-- Cambia ruolo utente (customer/admin)
UPDATE SB_utente
SET ruolo = :ruolo
WHERE id_utente = :id_utente;

-- Elimina utente (solo se non ha ordini)
DELETE FROM SB_utente
WHERE id_utente = :id_utente
	AND NOT EXISTS (
		SELECT 1
		FROM SB_ordine o
		WHERE o.id_utente = SB_utente.id_utente
	);


-- ---------- CATEGORIE ----------

-- Lista categorie
SELECT id_categoria, descrizione
FROM SB_categoria
ORDER BY id_categoria;

-- Nuova categoria (id manuale nel tuo schema)
INSERT INTO SB_categoria (id_categoria, descrizione)
VALUES (:id_categoria, :descrizione);

-- Modifica categoria
UPDATE SB_categoria
SET descrizione = :descrizione
WHERE id_categoria = :id_categoria;

-- Elimina categoria solo se non usata da prodotti
DELETE FROM SB_categoria
WHERE id_categoria = :id_categoria
	AND NOT EXISTS (
		SELECT 1
		FROM SB_prodotto p
		WHERE p.id_categoria = SB_categoria.id_categoria
	);


-- ---------- PRODOTTI ----------

-- Lista prodotti completa (admin)
SELECT
	p.id_prodotto,
	p.nome,
	p.descrizione,
	p.prezzo,
	p.giacenza,
	p.id_categoria,
	c.descrizione AS categoria
FROM SB_prodotto p
JOIN SB_categoria c ON c.id_categoria = p.id_categoria
ORDER BY p.id_prodotto DESC;

-- Inserisci prodotto
INSERT INTO SB_prodotto (nome, descrizione, prezzo, giacenza, id_categoria)
VALUES (:nome, :descrizione, :prezzo, :giacenza, :id_categoria);

-- Modifica prodotto
UPDATE SB_prodotto
SET nome = :nome,
	descrizione = :descrizione,
	prezzo = :prezzo,
	giacenza = :giacenza,
	id_categoria = :id_categoria
WHERE id_prodotto = :id_prodotto;

-- Elimina prodotto solo se mai ordinato
DELETE FROM SB_prodotto
WHERE id_prodotto = :id_prodotto
	AND NOT EXISTS (
		SELECT 1
		FROM SB_dettaglio_ordine d
		WHERE d.id_prodotto = SB_prodotto.id_prodotto
	);


-- ---------- ORDINI ----------

-- Lista ordini con cliente e totale
SELECT
	o.id_ordine,
	o.data_ordine,
	o.data_ritiro,
	o.stato,
	o.metodo,
	o.nota,
	u.id_utente,
	CONCAT(u.nome, ' ', u.cognome) AS cliente,
	SUM(d.quantità * p.prezzo) AS totale
FROM SB_ordine o
JOIN SB_utente u ON u.id_utente = o.id_utente
JOIN SB_dettaglio_ordine d ON d.id_ordine = o.id_ordine
JOIN SB_prodotto p ON p.id_prodotto = d.id_prodotto
GROUP BY o.id_ordine, o.data_ordine, o.data_ritiro, o.stato, o.metodo, o.nota, u.id_utente, cliente
ORDER BY o.data_ordine DESC;

-- Filtra ordini per stato
SELECT
	o.id_ordine,
	o.data_ordine,
	o.data_ritiro,
	o.stato,
	CONCAT(u.nome, ' ', u.cognome) AS cliente
FROM SB_ordine o
JOIN SB_utente u ON u.id_utente = o.id_utente
WHERE o.stato = :stato
ORDER BY o.data_ordine DESC;

-- Filtra ordini per intervallo date
SELECT
	o.id_ordine,
	o.data_ordine,
	o.data_ritiro,
	o.stato,
	CONCAT(u.nome, ' ', u.cognome) AS cliente
FROM SB_ordine o
JOIN SB_utente u ON u.id_utente = o.id_utente
WHERE o.data_ordine BETWEEN :data_inizio AND :data_fine
ORDER BY o.data_ordine DESC;

-- Elimina ordine (prima dettagli, poi testata)
DELETE FROM SB_dettaglio_ordine
WHERE id_ordine = :id_ordine;

DELETE FROM SB_ordine
WHERE id_ordine = :id_ordine;


-- ---------- REPORT ----------

-- Incasso totale per giorno
SELECT
	DATE(o.data_ordine) AS giorno,
	SUM(d.quantità * p.prezzo) AS incasso
FROM SB_ordine o
JOIN SB_dettaglio_ordine d ON d.id_ordine = o.id_ordine
JOIN SB_prodotto p ON p.id_prodotto = d.id_prodotto
WHERE o.stato = 'Completato'
GROUP BY DATE(o.data_ordine)
ORDER BY giorno DESC;

-- Top prodotti più venduti
SELECT
	p.id_prodotto,
	p.nome,
	SUM(d.quantità) AS quantita_venduta,
	SUM(d.quantità * p.prezzo) AS ricavo
FROM SB_dettaglio_ordine d
JOIN SB_prodotto p ON p.id_prodotto = d.id_prodotto
JOIN SB_ordine o ON o.id_ordine = d.id_ordine
WHERE o.stato = 'Completato'
GROUP BY p.id_prodotto, p.nome
ORDER BY quantita_venduta DESC, ricavo DESC;

-- Conteggio ordini per stato
SELECT stato, COUNT(*) AS totale_ordini
FROM SB_ordine
GROUP BY stato
ORDER BY totale_ordini DESC;
