-- ==========================================
-- ORARIO RITIRO ORDINE
-- ==========================================

-- Ordini da ritirare oggi, ordinati per orario
SELECT
	o.id_ordine,
	o.data_ritiro,
	o.stato,
	u.nome,
	u.cognome,
	u.telefono
FROM SB_ordine o
JOIN SB_utente u ON u.id_utente = o.id_utente
WHERE DATE(o.data_ritiro) = CURDATE()
ORDER BY o.data_ritiro ASC;

-- Controllo disponibilità slot (numero ordini nello stesso orario)
SELECT COUNT(*) AS ordini_slot
FROM SB_ordine
WHERE data_ritiro = :data_ritiro;

-- Aggiorna orario di ritiro
UPDATE SB_ordine
SET data_ritiro = :nuova_data_ritiro
WHERE id_ordine = :id_ordine;

-- Prossimi ordini non completati
SELECT
	o.id_ordine,
	o.data_ritiro,
	o.stato,
	u.nome,
	u.cognome
FROM SB_ordine o
JOIN SB_utente u ON u.id_utente = o.id_utente
WHERE o.data_ritiro >= NOW()
  AND o.stato <> 'Completato'
ORDER BY o.data_ritiro ASC;
