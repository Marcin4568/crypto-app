<?php

require_once __DIR__ . '/../repositories/CoinRepository.php';
require_once __DIR__ . '/CryptoApiService.php';

class CoinService
{
    private CoinRepository $repository;
    private CryptoApiService $cryptoApi;

    public function __construct()
    {
        $this->repository = new CoinRepository();
        $this->cryptoApi  = new CryptoApiService();
    }

    // -------------------------------------------------------
    // CoinGecko mapping
    // -------------------------------------------------------

    private function getCoinMap(): array
    {
        return [
            'BTC'  => 'bitcoin',
            'ETH'  => 'ethereum',
            'SOL'  => 'solana',
            'ADA'  => 'cardano',
            'XRP'  => 'ripple',
            'DOGE' => 'dogecoin',
            'DOT'  => 'polkadot',
        ];
    }

    // -------------------------------------------------------
    // PORTFOLIO VALUE (JOIN + API)
    // -------------------------------------------------------

    public function getPortfolioValue(int $portfolioId): array
    {
        $rows = $this->repository->getCoinsWithPortfolioData($portfolioId);

        $map = $this->getCoinMap();
        $ids = [];

        foreach ($rows as $row) {
            $symbol = strtoupper($row['symbol']);

            if (isset($map[$symbol])) {
                $ids[] = $map[$symbol];
            }
        }

        $prices = $this->cryptoApi->getPrices($ids) ?? [];

        $result = [];
        $totalValue = 0;

        foreach ($rows as $row) {
            $symbol = strtoupper($row['symbol']);
            $id = $map[$symbol] ?? null;

            $price = $prices[$id]['eur'] ?? 0;
            $amount = $row['amount'];

            $value = $price * $amount;
            $totalValue += $value;

            $result[] = [
                'name'   => $row['name'],
                'symbol' => $symbol,
                'amount' => $amount,
                'price'  => $price,
                'value'  => $value
            ];
        }

        return [
            'coins' => $result,
            'total' => $totalValue
        ];
    }

    // -------------------------------------------------------
    // ALL COINS WITH LIVE PRICES
    // -------------------------------------------------------

    public function getCoinsWithPrices(): array
    {
        $coins = $this->repository->getAll();
        $map   = $this->getCoinMap();

        $ids = [];

        foreach ($coins as $coin) {
            $symbol = strtoupper($coin->getSymbol());

            if (isset($map[$symbol])) {
                $ids[] = $map[$symbol];
            }
        }

        $prices = $this->cryptoApi->getPrices($ids) ?? [];

        $result = [];

        foreach ($coins as $coin) {
            $symbol = strtoupper($coin->getSymbol());
            $id = $map[$symbol] ?? null;

            $price = $prices[$id]['eur'] ?? null;
            $change = $prices[$id]['eur_24h_change'] ?? null;

            $result[] = [
                'coin'   => $coin,
                'price'  => $price,
                'change' => $change
            ];
        }

        return $result;
    }

    // -------------------------------------------------------
    // READ
    // -------------------------------------------------------

    public function getAllCoins(): array
    {
        return $this->repository->getAll();
    }

    public function getCoinById(int $id): ?Coin
    {
        return $this->repository->getById($id);
    }

    public function searchCoins(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            return $this->repository->getAll();
        }

        return $this->repository->search($query);
    }

    // -------------------------------------------------------
    // CREATE
    // -------------------------------------------------------

    public function createCoin(string $name, string $symbol, string $description): array
    {
        $errors = $this->validate($name, $symbol);

        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        if ($this->repository->symbolExists($symbol)) {
            return ['success' => false, 'message' => 'Symbool bestaat al.'];
        }

        $id = $this->repository->create($name, $symbol, $description);

        return ['success' => true, 'message' => 'Coin toegevoegd!', 'id' => $id];
    }

    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------

    public function updateCoin(int $id, string $name, string $symbol, string $description): array
    {
        if (!$this->repository->getById($id)) {
            return ['success' => false, 'message' => 'Coin niet gevonden.'];
        }

        $errors = $this->validate($name, $symbol);

        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        if ($this->repository->symbolExists($symbol, $id)) {
            return ['success' => false, 'message' => 'Symbool bestaat al.'];
        }

        $ok = $this->repository->update($id, $name, $symbol, $description);

        return $ok
            ? ['success' => true, 'message' => 'Coin bijgewerkt!']
            : ['success' => false, 'message' => 'Fout bij updaten'];
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
            ? ['success' => true, 'message' => 'Coin verwijderd.']
            : ['success' => false, 'message' => 'Fout bij verwijderen'];
    }

    // -------------------------------------------------------
    // VALIDATION
    // -------------------------------------------------------

    private function validate(string $name, string $symbol): array
    {
        $errors = [];

        if (strlen(trim($name)) < 2) {
            $errors[] = 'Naam te kort.';
        }

        if (!preg_match('/^[A-Za-z]{1,10}$/', $symbol)) {
            $errors[] = 'Ongeldig symbool.';
        }

        return $errors;
    }

    // -------------------------------------------------------
    // PORTFOLIO COINS (JOIN RAW DATA)
    // -------------------------------------------------------

    public function getPortfolioCoins(int $portfolioId): array
    {
        return $this->repository->getCoinsWithPortfolioData($portfolioId);
    }
}