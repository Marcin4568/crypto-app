-- =============================================
-- CryptoApp Database Schema
-- =============================================

CREATE DATABASE IF NOT EXISTS cryptoapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cryptoapp;

-- Tabel 1: coins
CREATE TABLE IF NOT EXISTS coins (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)   NOT NULL,
    symbol      VARCHAR(10)    NOT NULL UNIQUE,
    description TEXT,
    created_at  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
);
--tabel coin mappings
CREATE TABLE coin_mappings (
                               id INT AUTO_INCREMENT PRIMARY KEY,
                               symbol VARCHAR(10) NOT NULL UNIQUE,
                               api_id VARCHAR(50) NOT NULL
);

INSERT INTO coin_mappings (symbol, api_id) VALUES
                                               ('BTC', 'bitcoin'),
                                               ('ETH', 'ethereum'),
                                               ('SOL', 'solana'),
                                               ('ADA', 'cardano'),
                                               ('XRP', 'ripple'),
                                               ('DOGE', 'dogecoin'),
                                               ('DOT', 'polkadot');

-- Tabel 2: portfolios
CREATE TABLE IF NOT EXISTS portfolios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)   NOT NULL,
    description VARCHAR(255),
    created_at  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP
);

-- Tabel 3: koppeltabel (relatie coins <-> portfolios)
CREATE TABLE IF NOT EXISTS portfolio_coins (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT            NOT NULL,
    coin_id      INT            NOT NULL,
    amount       DECIMAL(18, 8) NOT NULL DEFAULT 0,
    buy_price    DECIMAL(18, 2) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE,
    FOREIGN KEY (coin_id)      REFERENCES coins(id)      ON DELETE CASCADE
);

-- =============================================
-- Voorbeelddata
-- =============================================

INSERT INTO coins (name, symbol, description) VALUES
('Bitcoin',  'BTC', 'De eerste en grootste cryptocurrency ter wereld.'),
('Ethereum', 'ETH', 'Blockchain platform voor smart contracts en dApps.'),
('Solana',   'SOL', 'Snelle en goedkope blockchain voor DeFi en NFTs.'),
('Cardano',  'ADA', 'Proof-of-stake blockchain gericht op duurzaamheid.');

INSERT INTO portfolios (name, description) VALUES
('Mijn Hoofdportfolio', 'Langetermijn investering in grote coins.'),
('Speculatief',         'Risicovollere, kleinere altcoins.');

INSERT INTO portfolio_coins (portfolio_id, coin_id, amount, buy_price) VALUES
(1, 1, 0.5,  45000.00),
(1, 2, 2.0,  2800.00),
(2, 3, 10.0, 95.00),
(2, 4, 500.0, 0.45);
