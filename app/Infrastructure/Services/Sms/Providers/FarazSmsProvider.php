<?php

namespace App\Infrastructure\Services\Sms\Providers;

use App\Infrastructure\Services\Sms\SmsProviderInterface;

class FarazSmsProvider implements SmsProviderInterface
{
    /** Sms.ir provider class
     * @param string $mobile
     * @param string $message
     * @return bool
     */
    public function send(string $mobile, string $message): bool
    {
        $payload = [
            'sending_type' => 'pattern',
            'from_number' => config('services.notifications.sms.providers.farazsms.sms_from'),
            'code' => (int) config('services.notifications.sms.providers.farazsms.sms_template_id'),
            'recipients' => [
                (string) preg_replace('/^0/', '', $mobile)
            ],
            'params' => [
                'code' => $message
            ]
        ];

        $headers = [
            'Content-Type: application/json',
            'Accept: text/plain',
            'Authorization: ' . config('services.notifications.sms.providers.farazsms.sms_api_token')
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://edge.ippanel.com/v1/api/send',
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
