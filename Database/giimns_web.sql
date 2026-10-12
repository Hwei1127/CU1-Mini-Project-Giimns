DROP DATABASE IF EXISTS giimns_web;
SET SQL_SAFE_UPDATES = 0;
CREATE DATABASE IF NOT EXISTS giimns_web;
USE giimns_web;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
	name       VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password LONGTEXT NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE users ADD CONSTRAINT chk_role CHECK (role IN ('user', 'editor', 'admin'));

CREATE TABLE genres (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    genre VARCHAR(50) NOT NULL UNIQUE
);
 
INSERT INTO genres (genre) VALUES
('Action'),
('Adventure'),
('Horror'),
('Survival'),
('Puzzle'),
('Simulation'),
('Strategy');

CREATE TABLE games (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(55) NOT NULL,
    image       VARCHAR(255),
    link        VARCHAR(255) NULL, 
    description TEXT,
    price       DECIMAL(10,2) DEFAULT 0.00,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE game_genres (
	id INT NOT NULL auto_increment PRIMARY KEY,
    game_id  INT NOT NULL,
    genre_id INT NOT NULL,
    -- PRIMARY KEY (game_id, genre_id),
    FOREIGN KEY (game_id)  REFERENCES games(id)  ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
);

CREATE TABLE wishlist (
	id INT NOT NULL auto_increment PRIMARY KEY,
    user_id  INT NOT NULL,
    game_id  INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- PRIMARY KEY (user_id, game_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    game_id    INT NOT NULL,
    rating     TINYINT NOT NULL,
    comment    TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
    UNIQUE (user_id, game_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, role) VALUES
('Admin',       'admin@giimns.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Editor Emma', 'emma@giimns.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor'),
('Alex Tan',    'alex@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Sara Lim',    'sara@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Daniel Wong', 'daniel@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

INSERT INTO games (name, image, description, price) VALUES
('Poppy Playtime',    'poppy.png',            'Explore an abandoned toy factory, solve puzzles with the GrabPack and escape the toys that lurk inside.', 4.99),
('Subnautica',        'subnautica.png',       'Survive on an alien ocean planet by gathering resources, building bases and exploring the deep.', 29.99),
('Resident Evil 4',   're4.png',              'Leon S. Kennedy fights through a hostile village to rescue the president''s daughter.', 39.99),
('Portal 2',          'portal2.png',          'Use the portal gun to solve physics-based test chambers in the Aperture Science labs.', 9.99),
('Cities: Skylines',  'cities-skylines.png',  'Build and manage a modern city, from roads and zoning to budgets and services.', 29.99),
('Civilization VI',   'civ6.png',             'Lead a civilization from the Stone Age to the Information Age in this turn-based strategy game.', 59.99),
('The Forest',        'the-forest.png',       'Crash-land on a forest peninsula and survive cannibal mutants while searching for your lost son.', 19.99),
('Stardew Valley',    'stardew.png',          'Inherit a run-down farm, grow crops, raise animals and befriend the townspeople.', 14.99);

INSERT INTO game_genres (game_id, genre_id) VALUES
(1, 3), (1, 5),          -- Poppy Playtime: Horror, Puzzle
(2, 2), (2, 4),                  -- Subnautica: Adventure, Survival
(3, 1), (3, 3), (3, 4),          -- Resident Evil 4: Action, Horror, Survival
(4, 5), (4, 2),                  -- Portal 2: Puzzle, Adventure
(5, 6), (5, 7),                  -- Cities: Skylines: Simulation, Strategy
(6, 7),                          -- Civilization VI: Strategy
(7, 4), (7, 3), (7, 2),          -- The Forest: Survival, Horror, Adventure
(8, 6), (8, 2);                  -- Stardew Valley: Simulation, Adventure

INSERT INTO wishlist (user_id, game_id) VALUES
(3, 1), (3, 3), (3, 7),
(4, 2), (4, 8),
(5, 5), (5, 6),
(2, 4);

INSERT INTO reviews (user_id, game_id, rating, comment) VALUES
(3, 1, 5, 'Creepy atmosphere and clever puzzles. The GrabPack is great.'),
(4, 1, 4, 'Really fun, but too short for me.'),
(5, 1, 4, 'Great jump scares without being overwhelming.'),
(3, 2, 5, 'Stunning ocean world. Terrifying and beautiful at the same time.'),
(4, 4, 5, 'One of the best puzzle games ever made.'),
(5, 5, 4, 'Very addictive, traffic management is the real challenge.'),
(3, 8, 5, 'So relaxing. I lost a whole weekend to it.'),
(4, 8, 5, 'Cozy and charming, perfect for unwinding.'),
(2, 3, 5, 'A remake done right, tense and action-packed.'),
(5, 7, 3, 'Good survival mechanics but the story is a bit slow.');

-- Run this ONCE, after importing your giimns_web.sql.
-- It adds what the website needs on top of your schema; nothing is dropped.
USE giimns_web;

-- 1. Banner image shown on the left of the game card (falls back to `image` when empty)
ALTER TABLE games ADD COLUMN banner VARCHAR(255) NULL AFTER image;

-- 2. A user can only have a game on their wishlist once
ALTER TABLE wishlist ADD UNIQUE KEY uq_wishlist (user_id, game_id);

-- 3. Your Poppy Playtime example: banner + Play Now link
UPDATE games
   SET banner = 'poppy_banner.png',
       link   = 'steam://launch/1721470/Dialog'
 WHERE name = 'Poppy Playtime';

SET SQL_SAFE_UPDATES = 0;