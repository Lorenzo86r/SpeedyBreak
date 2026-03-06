-- Disabilita FK durante l'inserimento
SET FOREIGN_KEY_CHECKS = 0;

USE `my_input789`;

-- Categorie
INSERT INTO `SB_categoria` (`id_categoria`, `descrizione`) VALUES
(1, 'Panini e Focacce'),
(2, 'Bibite'),
(3, 'Caffetteria'),
(4, 'Dolci');

-- Dati demo
INSERT INTO `SB_utente` (`id_utente`, `username`, `email`, `password_hash`, `ruolo`) VALUES
(1, 'Mario', 'mario.rossi@example.com', 'hash_segreto_123','customer'),
(2, 'Luigi','luigi.verdi@example.com', 'hash_segreto_456','customer'),
(3, 'Admin', 'admin@speedybreak.com', 'hash_admin_789','admin');

INSERT INTO `SB_prodotto` (`id_prodotto`, `nome`, `descrizione`, `prezzo`, `giacenza`, `id_categoria`) VALUES
(1, 'Panino al Prosciutto', 'Panino classico con prosciutto cotto e formaggio', 4.50, 1, 1),
(2, 'Focaccia Ligure', 'Focaccia classica all\'olio', 2.00, 1, 1),
(3, 'Coca Cola', 'Lattina 33cl', 2.50, 1, 2),
(4, 'Acqua Naturale', 'Bottiglia 50cl', 1.00, 1, 2),
(5, 'Caffè Espresso', 'Miscela arabica', 1.10, 1, 3),
(6, 'Cornetto alla Crema', 'Cornetto fresco di pasticceria', 1.50, 1, 4);

INSERT INTO `SB_ordine` (`id_ordine`, `data_ordine`, `stato`, `metodo`, `id_utente`, `nota`, `data_ritiro`) VALUES
(1, NOW(), 'Completato', 'Carta di Credito', 1, 'Nessuna nota', NOW()),
(2, NOW(), 'In Preparazione', 'Contanti', 2, 'Scaldare il panino', DATE_ADD(NOW(), INTERVAL 1 HOUR));

INSERT INTO `SB_dettaglio_ordine` (`id_ordine`, `id_prodotto`, `quantita`) VALUES
(1, 1, 1),
(1, 3, 1),
(2, 2, 2);

-- Riabilita FK
SET FOREIGN_KEY_CHECKS = 1;
