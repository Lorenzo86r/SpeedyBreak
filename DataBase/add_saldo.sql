-- Aggiungere colonna saldo alla tabella SB_utente
ALTER TABLE `SB_utente` ADD COLUMN `saldo` DECIMAL(10,2) NOT NULL DEFAULT 0.00;
