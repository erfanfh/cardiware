<?php

namespace App\Infrastructure\Services\Sms\Providers;

use App\Infrastructure\Services\Sms\SmsProviderInterface;

class SmsirProvider implements SmsProviderInterface
{
    /** Sms.ir provider class
     * @param string $mobile
     * @param string $message
     * @return bool
     */
    public function send(string $mobile, string $message): bool
    {
        $payload = [
            'mobile' => (string) preg_replace('/^0/', '', $mobile),
            'templateId' => get_setting('general', 'smsir_tepmlate_id'),
            'parameters' => [
                [
                    'name' => 'CODE',
                    'value' => (string) $message
                ]
            ]
        ];

        $headers = [
            'Content-Type: application/json',
            'Accept: text/plain',
            'x-api-key: ' . get_setting('general', 'smsir_api_token')
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.sms.ir/v1/send/verify',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        curl_exec($curl);
        curl_close($curl);

        return true;
    }
}
