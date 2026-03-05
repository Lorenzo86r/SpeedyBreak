-- ==========================================
-- ORDINE
-- ==========================================

-- Creazione testata ordine
INSERT INTO SB_ordine (data_ordine, stato, metodo, id_utente, nota, data_ritiro)
VALUES (NOW(), 'In attesa', :metodo, :id_utente, :nota, :data_ritiro);

-- Recupero ultimo id ordine inserito (MySQL)
SELECT LAST_INSERT_ID() AS id_ordine;

-- Inserimento righe ordine (ripetere per ogni prodotto nel carrello)
INSERT INTO SB_dettaglio_ordine (id_ordine, id_prodotto, quantita)
VALUES (:id_ordine, :id_prodotto, :quantita);

-- Totale ordine
SELECT
	d.id_ordine,
	SUM(d.quantita * p.prezzo) AS totale
FROM SB_dettaglio_ordine d
JOIN SB_prodotto p ON p.id_prodotto = d.id_prodotto
WHERE d.id_ordine = :id_ordine
GROUP BY d.id_ordine;

-- Dettaglio completo ordine
SELECT
	o.id_ordine,
	o.data_ordine,
	o.stato,
	o.metodo,
	o.data_ritiro,
	o.nota,
	p.id_prodotto,
	p.nome AS prodotto,
	d.quantita,
	p.prezzo,
	(d.quantita * p.prezzo) AS subtotale
FROM SB_ordine o
JOIN SB_dettaglio_ordine d ON d.id_ordine = o.id_ordine
JOIN SB_prodotto p ON p.id_prodotto = d.id_prodotto
WHERE o.id_ordine = :id_ordine
ORDER BY p.nome;

-- Storico ordini utente
SELECT
	o.id_ordine,
	o.data_ordine,
	o.stato,
	o.metodo,
	o.data_ritiro,
	SUM(d.quantita * p.prezzo) AS totale
FROM SB_ordine o
JOIN SB_dettaglio_ordine d ON d.id_ordine = o.id_ordine
JOIN SB_prodotto p ON p.id_prodotto = d.id_prodotto
WHERE o.id_utente = :id_utente
GROUP BY o.id_ordine, o.data_ordine, o.stato, o.metodo, o.data_ritiro
ORDER BY o.data_ordine DESC;

-- Aggiornamento stato ordine (admin)
UPDATE SB_ordine
SET stato = :stato
WHERE id_ordine = :id_ordine;
