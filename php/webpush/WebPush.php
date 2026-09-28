<?php
/**
 * WebPush.php — Pure PHP VAPID Web Push (no Composer dependency)
 * Supports EC P-256 VAPID keys, AES-128-GCM payload encryption (no-payload mode).
 * We use no-payload push: SW fetches the notification content from notification-info.php.
 */
class WebPush
{
    private string $vapidPublicKey;
    private string $vapidPrivateKeyPem;
    private string $subject;

    public function __construct(string $subject, string $vapidPublicKeyBase64url, string $vapidPrivateKeyPemPath)
    {
        $this->subject           = $subject;
        $this->vapidPublicKey    = $vapidPublicKeyBase64url;
        $this->vapidPrivateKeyPem = file_get_contents($vapidPrivateKeyPemPath);
    }

    /**
     * Send a no-payload push to one subscription.
     * @param array $subscription ['endpoint'=>..., 'keys'=>['p256dh'=>..., 'auth'=>...]]
     * @return array ['ok'=>bool, 'http_code'=>int, 'error'=>string|null]
     */
    public function send(array $subscription): array
    {
        $endpoint = $subscription['endpoint'] ?? '';
        if (!$endpoint) return ['ok' => false, 'http_code' => 0, 'error' => 'No endpoint'];

        $audienceUrl = parse_url($endpoint);
        $audience    = $audienceUrl['scheme'] . '://' . $audienceUrl['host'];

        $jwt     = $this->buildJwt($audience);
        $authHeader = 'vapid t=' . $jwt . ', k=' . $this->vapidPublicKey;

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $authHeader,
                'TTL: 86400',
                'Content-Length: 0',
                'Content-Type: application/octet-stream',
            ],
        ]);

        $response  = curl_exec($ch);
        $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $ok = ($httpCode >= 200 && $httpCode < 300) || $httpCode === 201;
        return [
            'ok'        => $ok,
            'http_code' => $httpCode,
            'error'     => $curlError ?: ($ok ? null : ('HTTP ' . $httpCode . ': ' . $response)),
        ];
    }

    /* ── JWT builder ── */
    private function buildJwt(string $audience): string
    {
        $header  = $this->base64url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $payload = $this->base64url(json_encode([
            'aud' => $audience,
            'exp' => time() + 43200, // 12h
            'sub' => $this->subject,
        ]));

        $data = $header . '.' . $payload;
        $pkey = openssl_pkey_get_private($this->vapidPrivateKeyPem);
        openssl_sign($data, $derSig, $pkey, 'SHA256');

        return $data . '.' . $this->base64url($this->derToRaw($derSig));
    }

    /* DER-encoded ECDSA signature → raw 64-byte (r||s) */
    private function derToRaw(string $der): string
    {
        // DER: 30 xx 02 xx [r] 02 xx [s]
        $offset = 2 + 2; // skip SEQUENCE + INTEGER tag/len
        $rLen   = ord($der[$offset - 1]);
        $r      = substr($der, $offset, $rLen);
        $offset += $rLen + 2; // skip INTEGER tag/len
        $sLen   = ord($der[$offset - 1]);
        $s      = substr($der, $offset, $sLen);

        // Strip leading zero bytes (DER pads to keep positive) and pad to 32 bytes
        $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
