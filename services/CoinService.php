<?php

require_once __DIR__ . '/../repositories/CoinRepository.php';

/**
 * CoinService
 * 
 * Bevat de businesslogica voor coins.
 * Valideert data voordat het naar de repository gaat.
 * De frontend praat altijd met de Service, nooit direct met de Repository.
 */
class CoinService
{
    private CoinRepository $repository;

    public function __construct()
    {
        $this->repository = new CoinRepository();
    }

    // -------------------------------------------------------
    // READ
    // -------------------------------------------------------

    /** @return Coin[] */
    public function getAllCoins(): array
    {
        return $this->repository->getAll();
    }

    public function getCoinById(int $id): ?Coin
    {
        return $this->repository->getById($id);
    }

    /** @return Coin[] */
    public function searchCoins(string $query): array
    {
        if (strlen(trim($query)) < 1) {
            return $this->repository->getAll();
        }
        return $this->repository->search(trim($query));
    }

    // -------------------------------------------------------
    // CREATE
    // -------------------------------------------------------

    /**
     * Valideer en maak een nieuwe coin aan.
     * Geeft een array terug: ['success' => bool, 'message' => string, 'id' => int|null]
     */
    public function createCoin(string $name, string $symbol, string $description): array
    {
        // Validatie
        $errors = $this->validate($name, $symbol);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        // Controleer of symbool al bestaat
        if ($this->repository->symbolExists($symbol)) {
            return ['success' => false, 'message' => "Symbool '{$symbol}' bestaat al."];
        }

        $id = $this->repository->create(trim($name), trim($symbol), trim($description));
        return ['success' => true, 'message' => "Coin '{$name}' succesvol toegevoegd!", 'id' => $id];
    }

    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------

    /**
     * Valideer en update een bestaande coin.
     */
    public function updateCoin(int $id, string $name, string $symbol, string $description): array
    {
        // Controleer of coin bestaat
        if (!$this->repository->getById($id)) {
            return ['success' => false, 'message' => 'Coin niet gevonden.'];
        }

        // Validatie
        $errors = $this->validate($name, $symbol);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        // Controleer of symbool al bestaat (maar niet bij zichzelf)
        if ($this->repository->symbolExists($symbol, $id)) {
            return ['success' => false, 'message' => "Symbool '{$symbol}' wordt al gebruikt."];
        }

        $ok = $this->repository->update($id, trim($name), trim($symbol), trim($description));
        return $ok
            ? ['success' => true,  'message' => "Coin '{$name}' succesvol bijgewerkt!"]
            : ['success' => false, 'message' => 'Er ging iets mis bij het bijwerken.'];
    }

    // -------------------------------------------------------
    // DELETE
    // -------------------------------------------------------

    public function deleteCoin(int $id): array
    {
        $coin = $this->repository->getById($id);
        if (!$coin) {
            return ['success' => false, 'message' => 'Coin niet gevonden.'];
        }

        $ok = $this->repository->delete($id);
        return $ok
            ? ['success' => true,  'message' => "Coin '{$coin->getName()}' verwijderd."]
            : ['success' => false, 'message' => 'Er ging iets mis bij het verwijderen.'];
    }

    // -------------------------------------------------------
    // VALIDATIE (privé hulpfunctie)
    // -------------------------------------------------------

    private function validate(string $name, string $symbol): array
    {
        $errors = [];

        if (strlen(trim($name)) < 2) {
            $errors[] = 'Naam moet minimaal 2 tekens bevatten.';
        }
        if (strlen(trim($name)) > 100) {
            $errors[] = 'Naam mag maximaal 100 tekens bevatten.';
        }
        if (!preg_match('/^[A-Za-z]{1,10}$/', trim($symbol))) {
            $errors[] = 'Symbool mag alleen letters bevatten (1–10 tekens), bijv. BTC.';
        }

        return $errors;
    }
}
