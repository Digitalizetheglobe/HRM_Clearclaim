<?php

namespace Lahirulhr\PayHere;

use Illuminate\Http\Response;

class Checkout
{
    protected array $data = [];

    protected ?string $successUrl = null;

    protected ?string $failUrl = null;

    protected ?string $notifyUrl = null;

    public function data(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function successUrl(string $url): self
    {
        $this->successUrl = $url;

        return $this;
    }

    public function failUrl(string $url): self
    {
        $this->failUrl = $url;

        return $this;
    }

    public function notifyUrl(string $url): self
    {
        $this->notifyUrl = $url;

        return $this;
    }

    public function setOptionalData(array $optional): self
    {
        $this->data = array_merge($this->data, $optional);

        return $this;
    }

    public function renderView(): Response
    {
        $endpoint = rtrim((string) config('payhere.api_endpoint', 'https://sandbox.payhere.lk'), '/').'/pay/checkout';
        $amount = $this->data['amount'] ?? '';
        if (is_numeric($amount)) {
            $amount = number_format((float) $amount, 2, '.', '');
        }

        $fields = [
            'merchant_id' => (string) config('payhere.merchant_id', ''),
            'return_url' => (string) $this->successUrl,
            'cancel_url' => (string) $this->failUrl,
            'notify_url' => (string) ($this->notifyUrl ?: $this->successUrl),
            'order_id' => (string) ($this->data['order_id'] ?? ''),
            'items' => (string) ($this->data['items'] ?? ''),
            'currency' => (string) ($this->data['currency'] ?? 'LKR'),
            'amount' => (string) $amount,
            'first_name' => (string) ($this->data['first_name'] ?? ''),
            'last_name' => (string) ($this->data['last_name'] ?? ''),
            'email' => (string) ($this->data['email'] ?? ''),
            'phone' => (string) ($this->data['phone'] ?? ''),
            'address' => (string) ($this->data['address'] ?? ''),
            'city' => (string) ($this->data['city'] ?? ''),
            'country' => (string) ($this->data['country'] ?? ''),
            'hash' => (string) ($this->data['hash'] ?? ''),
        ];

        $inputs = '';
        foreach ($fields as $name => $value) {
            $inputs .= '<input type="hidden" name="'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'" value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'">';
        }

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Redirecting to PayHere</title></head><body>'
            .'<form id="payhere-checkout" method="post" action="'.htmlspecialchars($endpoint, ENT_QUOTES, 'UTF-8').'">'
            .$inputs
            .'</form><script>document.getElementById("payhere-checkout").submit();</script></body></html>';

        return response($html);
    }
}
