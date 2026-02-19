-- Creazione tabelle SpeedyBreak su my_vignali (prefisso SB_)

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

USE `my_vignali`;

-- Categoria
CREATE TABLE IF NOT EXISTS `SB_categoria` (
  `id_categoria` INT NOT NULL,
  `descrizione` VARCHAR(45) NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE = InnoDB;

-- Utente
CREATE TABLE IF NOT EXISTS `SB_utente` (
  `id_utente` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `cognome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(20) NULL DEFAULT NULL,
  `ruolo` VARCHAR(50) NULL DEFAULT 'customer',
  PRIMARY KEY (`id_utente`),
  UNIQUE INDEX (`email` ASC) VISIBLE
);

-- Prodotto (FK verso SB_categoria)
CREATE TABLE IF NOT EXISTS `SB_prodotto` (
  `id_prodotto` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `descrizione` TEXT NULL DEFAULT NULL,
  `prezzo` DECIMAL(10,2) NOT NULL,
  `giacenza` TINYINT NULL DEFAULT TRUE,
  `id_categoria` INT NOT NULL,
  PRIMARY KEY (`id_prodotto`),
  INDEX `fk_prodotto_categoria_idx` (`id_categoria` ASC) VISIBLE,
  CONSTRAINT `fk_SB_prodotto_categoria`
    FOREIGN KEY (`id_categoria`)
    REFERENCES `SB_categoria` (`id_categoria`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

-- Ordine (FK verso SB_utente)
CREATE TABLE IF NOT EXISTS `SB_ordine` (
  `id_ordine` INT NOT NULL AUTO_INCREMENT,
  `data_ordine` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `stato` VARCHAR(50) NULL DEFAULT NULL,
  `metodo` VARCHAR(50) NULL DEFAULT NULL,
  `id_utente` INT NOT NULL,
  `nota` MEDIUMTEXT NULL,
  `data_ritiro` DATETIME NULL,
  PRIMARY KEY (`id_ordine`),
  INDEX `fk_SB_ordine_utente_idx` (`id_utente` ASC) VISIBLE,
  CONSTRAINT `fk_SB_ordine_utente`
    FOREIGN KEY (`id_utente`)
    REFERENCES `SB_utente` (`id_utente`)
);

-- Dettaglio ordine (FK verso SB_ordine e SB_prodotto)
CREATE TABLE IF NOT EXISTS `SB_dettaglio_ordine` (
  `id_ordine` INT NOT NULL,
  `id_prodotto` INT NOT NULL,
  `quantità` INT NULL DEFAULT 1,
  PRIMARY KEY (`id_ordine`, `id_prodotto`),
  INDEX `fk_SB_dettaglio_prodotto_idx` (`id_prodotto` ASC) VISIBLE,
  CONSTRAINT `fk_SB_dettaglio_ordine`
    FOREIGN KEY (`id_ordine`)
    REFERENCES `SB_ordine` (`id_ordine`),
  CONSTRAINT `fk_SB_dettaglio_prodotto`
    FOREIGN KEY (`id_prodotto`)
    REFERENCES `SB_prodotto` (`id_prodotto`)
);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
