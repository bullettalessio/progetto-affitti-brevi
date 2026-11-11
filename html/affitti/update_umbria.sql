USE affitti_brevi;

-- Pulisce le vecchie associazioni e tabelle per evitare conflitti di chiave esterna
DELETE FROM immobile_servizio;
DELETE FROM prenotazioni;
DELETE FROM immobili;
DELETE FROM localita;

-- Inserimento Località in Umbria
INSERT INTO localita (id, nome, regione) VALUES
(1, 'Perugia', 'Umbria'),
(2, 'Assisi', 'Umbria'),
(3, 'Orvieto', 'Umbria');

-- Inserimento Immobili umbri con prezzi accessibili (collegati all'host id 1)
INSERT INTO immobili (id, host_id, localita_id, titolo, descrizione, prezzo_notte, attivo) VALUES
(1, 1, 1, 'Appartamento Centro Storico Perugia', 'Grazioso appartamento vicino a Corso Vannucci, ideale per coppie.', 65.00, TRUE),
(2, 1, 2, 'Casa Vacanze San Francesco', 'Vista panoramica sulla Basilica di Assisi, silenzioso e accogliente.', 75.00, TRUE),
(3, 1, 3, 'Dimora Medievale Orvieto', 'Caratteristico alloggio nel cuore di Orvieto con soffitti a travi a vista.', 80.00, TRUE);

-- Associazione servizi (es. Wi-Fi e Aria Condizionata)
INSERT INTO immobile_servizio (immobile_id, servizio_id) VALUES
(1, 1), (1, 2),
(2, 1), (2, 2),
(3, 1);
