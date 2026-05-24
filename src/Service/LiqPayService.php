<?php

namespace App\Service;

use LiqPay;

class LiqPayService
{
    private LiqPay $liqpay;

    public function __construct(
        private string $publicKey,
        private string $privateKey,
    ) {
        $this->liqpay = new LiqPay($this->publicKey, $this->privateKey);
    }

    public function createPaymentForm(string $orderId, float $amount, string $description, string $resultUrl, string $serverUrl): string
    {
        return $this->liqpay->cnb_form([
            'action' => 'pay',
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => $description,
            'order_id' => $orderId,
            'version' => '3',
            'sandbox' => 1,
            'result_url' => $resultUrl,
            'server_url' => $serverUrl,
        ]);
    }

    public function verifyCallback(string $data, string $signature): bool
    {
        $expectedSignature = base64_encode(sha1($this->privateKey . $data . $this->privateKey, true));

        return $expectedSignature === $signature;
    }

    public function decodeData(string $data): array
    {
        return json_decode(base64_decode($data), true);
    }
}
