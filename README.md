# CryptoApp — Blok 1

Een PHP webapplicatie met volledige CRUD voor crypto coins, gebouwd met OOP.

---

## 📁 Projectstructuur

```
crypto-app/
├── config/
│   └── Database.php          ← DB-verbinding (Singleton patroon)
├── models/
│   ├── Coin.php              ← Coin model (data + getters/setters)
│   └── Portfolio.php         ← Portfolio model
├── repositories/
│   └── CoinRepository.php    ← Alle SQL-queries voor coins
├── services/
│   └── CoinService.php       ← Businesslogica + validatie
├── public/
│   └── index.php             ← Frontend + controller
├── database.sql              ← Database schema + voorbeelddata
└── README.md
```

---

## 🗄️ Database relaties

```
coins ──────────────────────── portfolio_coins ──────────────── portfolios
id (PK)                        id (PK)                          id (PK)
name                           coin_id (FK → coins.id)          name
symbol                         portfolio_id (FK → portfolios.id) description
description                    amount                           created_at
created_at                     buy_price
                               created_at
```

---

## ⚙️ Installatie

### 1. Database aanmaken
Open phpMyAdmin of MySQL CLI en voer uit:
```sql
SOURCE /pad/naar/database.sql;
```

### 2. Database instellingen aanpassen
Open `config/Database.php` en pas aan:
```php
private string $host     = 'localhost';
private string $dbname   = 'cryptoapp';
private string $username = 'root';      // jouw MySQL gebruiker
private string $password = '';          // jouw MySQL wachtwoord
```

### 3. Opstarten
Zet de map in je webserver (bijv. XAMPP `htdocs/crypto-app/`)
en open: `http://localhost/crypto-app/public/index.php`

---

## 🏗️ OOP-structuur uitgelegd

| Klasse              | Verantwoordelijkheid                                     |
|---------------------|----------------------------------------------------------|
| `Database`          | Maakt verbinding met MySQL. Singleton (1 connectie).     |
| `Coin`              | Modelleert één coin. Bevat alleen data + getters/setters.|
| `Portfolio`         | Modelleert één portfolio.                                |
| `CoinRepository`    | Alle SQL-queries (getAll, getById, create, update, delete). |
| `CoinService`       | Businesslogica: validatie, foutafhandeling.              |
| `index.php`         | Controller + View: verwerkt requests en toont HTML.      |

---

## ✅ Functionaliteiten (Blok 1)

- [x] **READ** — alle coins ophalen en tonen in tabel
- [x] **CREATE** — nieuw formulier + insert in database
- [x] **UPDATE** — bewerkformulier + update in database
- [x] **DELETE** — verwijderen met bevestigingsdialoog
- [x] **Zoeken** — filteren op naam of symbool
- [x] **Validatie** — server-side controles + foutmeldingen
- [x] **Feedback** — groene/rode meldingen na elke actie
- [ ] **API integratie** — gepland voor Blok 2 (CoinGecko)

---

## 🔜 Blok 2 — API integratie (week 6)

We voegen toe:
- `services/ApiService.php` → CoinGecko API aanroepen
- Live koersen ophalen en koppelen aan coins in de database
- Gratis endpoint: `https://api.coingecko.com/api/v3/simple/price`
