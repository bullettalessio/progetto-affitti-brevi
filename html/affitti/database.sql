CREATE DATABASE IF NOT EXISTS affitti_brevi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE affitti_brevi;

CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('ospite', 'host') NOT NULL DEFAULT 'ospite',
    creato_il TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS localita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS immobili (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(150) NOT NULL,
    descrizione TEXT,
    localita_id INT NOT NULL,
    prezzo_notte DECIMAL(10,2) NOT NULL,
    posti_letto INT NOT NULL DEFAULT 2,
    host_id INT NOT NULL,
    FOREIGN KEY (localita_id) REFERENCES localita(id),
    FOREIGN KEY (host_id) REFERENCES utenti(id)
);

CREATE TABLE IF NOT EXISTS prenotazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    immobile_id INT NOT NULL,
    utente_id INT NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE NOT NULL,
    prezzo_totale DECIMAL(10,2) NOT NULL,
    stato ENUM('confermata', 'annullata') DEFAULT 'confermata',
    FOREIGN KEY (immobile_id) REFERENCES immobili(id),
    FOREIGN KEY (utente_id) REFERENCES utenti(id)
);

-- Inserimento dati di test
INSERT INTO localita (id, nome) VALUES 
(1, 'Perugia'), 
(2, 'Assisi'), 
(3, 'Orvieto'), 
(4, 'Gubbio'), 
(5, 'Spoleto')
ON DUPLICATE KEY UPDATE nome=VALUES(nome);

INSERT INTO utenti (id, nome, email, password, ruolo) VALUES 
(1, 'Mario Host', 'host@umbria.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'host'),
(2, 'Luigi Ospite', 'ospite@umbria.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ospite')
ON DUPLICATE KEY UPDATE email=VALUES(email);

INSERT INTO immobili (id, titolo, descrizione, localita_id, prezzo_notte, posti_letto, host_id) VALUES 
(1, 'Appartamento Storico Centro', 'Splendido appartamento nel cuore di Perugia.', 1, 75.00, 4, 1),
(2, 'Villa Panoramica Assisi', 'Vista mozzafiato sulla basilica.', 2, 120.00, 6, 1)
ON DUPLICATE KEY UPDATE titolo=VALUES(titolo);
