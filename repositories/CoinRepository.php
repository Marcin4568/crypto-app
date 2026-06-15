<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Coin.php';

/**
 * CoinRepository
 * 
 * Verantwoordelijk voor ALLE database-queries op de `coins` tabel.
 * Geen businesslogica hier — alleen data ophalen en opslaan.
 */
class CoinRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // -------------------------------------------------------
    // READ
    // -------------------------------------------------------

    /**
     * Haal alle coins op uit de database.
     * @return Coin[]
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM coins ORDER BY name ASC");
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => Coin::fromArray($row), $rows);
    }

    /**
     * Haal één coin op via het ID.
     */
    public function getById(int $id): ?Coin
    {
        $stmt = $this->db->prepare("SELECT * FROM coins WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? Coin::fromArray($row) : null;
    }

    public function getCoinMap(): array
    {
        $stmt = $this->db->query("SELECT symbol, api_id FROM coin_mappings");
        $rows = $stmt->fetchAll();

        $map = [];

        foreach ($rows as $row) {
            $map[strtoupper($row['symbol'])] = $row['api_id'];
        }

        return $map;
    }

    /**
     * Zoek coins op naam of symbool.
     * @return Coin[]
     */
    public function search(string $query): array
    {
        $like = '%' . $query . '%';
        $stmt = $this->db->prepare(
            "SELECT * FROM coins WHERE name LIKE :q1 OR symbol LIKE :q2 ORDER BY name ASC"
        );
        $stmt->execute([':q1' => $like, ':q2' => $like]);

        return array_map(fn($row) => Coin::fromArray($row), $stmt->fetchAll());
    }
    public function getCoinsWithPortfolioData(int $portfolioId): array
    {
        $stmt = $this->db->prepare("
        SELECT 
            c.id,
            c.name,
            c.symbol,
            c.description,
            pc.amount,
            pc.buy_price
        FROM coins c
        JOIN portfolio_coins pc ON c.id = pc.coin_id
        WHERE pc.portfolio_id = :portfolio_id
    ");

        $stmt->execute([
            ':portfolio_id' => $portfolioId
        ]);

        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // CREATE
    // -------------------------------------------------------

    /**
     * Voeg een nieuwe coin toe aan de database.
     * Geeft het nieuwe ID terug.
     */
    public function create(string $name, string $symbol, string $description): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO coins (name, symbol, description) VALUES (:name, :symbol, :description)"
        );
        $stmt->execute([
            ':name'        => $name,
            ':symbol'      => strtoupper($symbol),
            ':description' => $description,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------

    /**
     * Pas een bestaande coin aan.
     * Geeft true terug als het gelukt is.
     */
    public function update(int $id, string $name, string $symbol, string $description): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE coins SET name = :name, symbol = :symbol, description = :description WHERE id = :id"
        );
        $stmt->execute([
            ':id'          => $id,
            ':name'        => $name,
            ':symbol'      => strtoupper($symbol),
            ':description' => $description,
        ]);

        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------
    // DELETE
    // -------------------------------------------------------

    /**
     * Verwijder een coin op basis van ID.
     * Geeft true terug als het gelukt is.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM coins WHERE id = :id");
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------
    // EXTRA: bestaat dit symbool al?
    // -------------------------------------------------------

    public function symbolExists(string $symbol, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM coins WHERE symbol = :symbol AND id != :excludeId"
        );
        $stmt->execute([
            ':symbol'    => strtoupper($symbol),
            ':excludeId' => $excludeId,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
