USE affitti_brevi;

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM immobile_servizio;
DELETE FROM prenotazioni;
DELETE FROM immobili;
DROP TABLE IF EXISTS localita;

-- Ricrea la tabella localita
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

-- Assicuriamoci che la tabella immobili contenga la colonna indirizzo se richiesta
ALTER TABLE immobili ADD COLUMN IF NOT EXISTS indirizzo VARCHAR(255) DEFAULT 'Via Centrale, 1';

-- Inserimento Immobili umbri con indirizzo e prezzi accessibili
INSERT INTO immobili (id, host_id, localita_id, titolo, descrizione, prezzo_notte, indirizzo, attivo) VALUES
(1, 1, 1, 'Appartamento Centro Storico Perugia', 'Grazioso appartamento vicino a Corso Vannucci, ideale per coppie.', 65.00, 'Corso Vannucci 15', TRUE),
(2, 1, 2, 'Casa Vacanze San Francesco', 'Vista panoramica sulla Basilica di Assisi, silenzioso e accogliente.', 75.00, 'Via San Francesco 8', TRUE),
(3, 1, 3, 'Dimora Medievale Orvieto', 'Caratteristico alloggio nel cuore di Orvieto con soffitti a travi a vista.', 80.00, 'Corso Cavour 42', TRUE);

SET FOREIGN_KEY_CHECKS = 1;
