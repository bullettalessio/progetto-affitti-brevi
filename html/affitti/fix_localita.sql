USE affitti_brevi;

-- Disattiva temporaneamente i controlli delle chiavi esterne per evitare conflitti durante il reset
SET FOREIGN_KEY_CHECKS = 0;

-- Svuota o ricrea le tabelle dipendenti e principali
DELETE FROM immobile_servizio;
DELETE FROM prenotazioni;
DELETE FROM immobili;
DROP TABLE IF EXISTS localita;

CREATE TABLE localita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    regione VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- Inserimento Località in Umbria
INSERT INTO localita (id, nome, regione) VALUES
(1, 'Perugia', 'Umbria'),
(2, 'Assisi', 'Umbria'),
(3, 'Orvieto', 'Umbria');

-- Reinserimento degli immobili umbri di prova
INSERT INTO immobili (id, host_id, localita_id, titolo, descrizione, prezzo_notte, attivo) VALUES
(1, 1, 1, 'Appartamento Centro Storico Perugia', 'Grazioso appartamento vicino a Corso Vannucci, ideale per coppie.', 65.00, TRUE),
(2, 1, 2, 'Casa Vacanze San Francesco', 'Vista panoramica sulla Basilica di Assisi, silenzioso e accogliente.', 75.00, TRUE),
(3, 1, 3, 'Dimora Medievale Orvieto', 'Caratteristico alloggio nel cuore di Orvieto con soffitti a travi a vista.', 80.00, TRUE);

-- Riattiva i controlli delle chiavi esterne
SET FOREIGN_KEY_CHECKS = 1;
