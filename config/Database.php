<?php

/**
 * Database
 * 
 * Verantwoordelijk voor de verbinding met de MySQL database.
 * Gebruikt het Singleton-patroon zodat er maar één connectie bestaat.
 */
class Database
{
    // Database instellingen — pas dit aan naar jouw eigen omgeving
    private string $host     = 'localhost';
    private string $dbname   = 'cryptoapp';
    private string $username = 'root';
    private string $password = '';
    private string $charset  = 'utf8mb4';

    private static ?Database $instance = null;
    private PDO $connection;

    // Private constructor → Singleton patroon
    private function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Gooi een duidelijke foutmelding (log nooit wachtwoorden in productie!)
            throw new RuntimeException("Databaseverbinding mislukt: " . $e->getMessage());
        }
    }

    /**
     * Geeft de enige instantie van Database terug (Singleton).
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Geeft de PDO-connectie terug voor gebruik in repositories.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
