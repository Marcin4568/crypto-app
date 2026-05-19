<?php

/**
 * Coin Model
 * 
 * Vertegenwoordigt één rij uit de `coins` tabel.
 * Bevat alleen data en getters/setters — geen database-logica.
 */
class Coin
{
    private int    $id;
    private string $name;
    private string $symbol;
    private string $description;
    private string $createdAt;

    public function __construct(
        int    $id,
        string $name,
        string $symbol,
        string $description = '',
        string $createdAt   = ''
    ) {
        $this->id          = $id;
        $this->name        = $name;
        $this->symbol      = strtoupper($symbol);
        $this->description = $description;
        $this->createdAt   = $createdAt;
    }

    // --- Getters ---
    public function getId():          int    { return $this->id; }
    public function getName():        string { return $this->name; }
    public function getSymbol():      string { return $this->symbol; }
    public function getDescription(): string { return $this->description; }
    public function getCreatedAt():   string { return $this->createdAt; }

    // --- Setters ---
    public function setName(string $name):               void { $this->name        = $name; }
    public function setSymbol(string $symbol):           void { $this->symbol      = strtoupper($symbol); }
    public function setDescription(string $description): void { $this->description = $description; }

    /**
     * Maakt een Coin-object van een database-rij (array).
     * Handig zodat je niet overal new Coin(...) met losse velden hoeft te schrijven.
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (int)  $row['id'],
                   $row['name'],
                   $row['symbol'],
                   $row['description'] ?? '',
                   $row['created_at']  ?? ''
        );
    }

    /**
     * Geeft de coin terug als array (handig voor formulieren of JSON).
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'symbol'      => $this->symbol,
            'description' => $this->description,
            'created_at'  => $this->createdAt,
        ];
    }
}
