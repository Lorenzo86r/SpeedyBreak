-- Disabilita FK durante l'inserimento
SET FOREIGN_KEY_CHECKS = 0;

USE `my_vignali`;

-- Categorie
INSERT INTO `SB_categoria` (`id_categoria`, `descrizione`) VALUES
(1, 'Panini e Focacce'),
(2, 'Bibite'),
(3, 'Caffetteria'),
(4, 'Dolci');

-- Dati demo
INSERT INTO `SB_utente` (`nome`, `cognome`, `email`, `password_hash`, `telefono`, `ruolo`) VALUES
('Mario', 'Rossi', 'mario.rossi@example.com', 'hash_segreto_123', '3331234567', 'customer'),
('Luigi', 'Verdi', 'luigi.verdi@example.com', 'hash_segreto_456', '3339876543', 'customer'),
('Admin', 'Sistema', 'admin@speedybreak.com', 'hash_admin_789', NULL, 'admin');

INSERT INTO `SB_prodotto` (`nome`, `descrizione`, `prezzo`, `giacenza`, `id_categoria`) VALUES
('Panino al Prosciutto', 'Panino classico con prosciutto cotto e formaggio', 4.50, 1, 1),
('Focaccia Ligure', 'Focaccia classica all\'olio', 2.00, 1, 1),
('Coca Cola', 'Lattina 33cl', 2.50, 1, 2),
('Acqua Naturale', 'Bottiglia 50cl', 1.00, 1, 2),
('Caffè Espresso', 'Miscela arabica', 1.10, 1, 3),
('Cornetto alla Crema', 'Cornetto fresco di pasticceria', 1.50, 1, 4);

INSERT INTO `SB_ordine` (`data_ordine`, `stato`, `metodo`, `id_utente`, `nota`, `data_ritiro`) VALUES
(NOW(), 'Completato', 'Carta di Credito', 1, 'Nessuna nota', NOW()),
(NOW(), 'In Preparazione', 'Contanti', 2, 'Scaldare il panino', DATE_ADD(NOW(), INTERVAL 1 HOUR));

INSERT INTO `SB_dettaglio_ordine` (`id_ordine`, `id_prodotto`, `quantità`) VALUES
(1, 1, 1),
(1, 3, 1),
(2, 2, 2);

-- Riabilita FK
SET FOREIGN_KEY_CHECKS = 1;
