-- ==========================================
-- DISPONIBILITÀ PRODOTTI
-- ==========================================

-- Elenco prodotti disponibili con categoria
SELECT
	p.id_prodotto,
	p.nome,
	p.descrizione,
	p.prezzo,
	p.giacenza,
	c.descrizione AS categoria
FROM SB_prodotto p
JOIN SB_categoria c ON c.id_categoria = p.id_categoria
WHERE p.giacenza = 1
ORDER BY c.descrizione, p.nome;

-- Ricerca prodotti disponibili per nome
SELECT
	p.id_prodotto,
	p.nome,
	p.descrizione,
	p.prezzo,
	c.descrizione AS categoria
FROM SB_prodotto p
JOIN SB_categoria c ON c.id_categoria = p.id_categoria
WHERE p.giacenza = 1
  AND p.nome LIKE CONCAT('%', :testo_ricerca, '%')
ORDER BY p.nome;

-- Prodotti disponibili per categoria
SELECT
	p.id_prodotto,
	p.nome,
	p.descrizione,
	p.prezzo
FROM SB_prodotto p
WHERE p.giacenza = 1
  AND p.id_categoria = :id_categoria
ORDER BY p.nome;

-- Cambio disponibilità prodotto (admin)
UPDATE SB_prodotto
SET giacenza = :giacenza
WHERE id_prodotto = :id_prodotto;
