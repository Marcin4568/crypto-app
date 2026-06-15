<?php

class CryptoApiService
{
    private string $baseUrl = "https://api.coingecko.com/api/v3";

    public function getPrices(array $coinIds): ?array
    {
        if (empty($coinIds)) {
            return null;
        }

        $ids = implode(',', $coinIds);

        $url = $this->baseUrl .
            "/simple/price?ids={$ids}&vs_currencies=eur&include_24hr_change=true";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log(curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}