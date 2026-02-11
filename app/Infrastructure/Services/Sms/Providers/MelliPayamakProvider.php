<?php

namespace App\Infrastructure\Services\Sms\Providers;

use App\Infrastructure\Services\Sms\SmsProviderInterface;

class MelliPayamakProvider implements SmsProviderInterface
{
    /** Sms.ir provider class
     * @param string $mobile
     * @param string $message
     * @return bool
     */
    public function send(string $mobile, string $message): bool
    {
        $payload = [
            'username' => config('services.notifications.sms.providers.melli-payamak.username'),
            'password' => config('services.notifications.sms.providers.melli-payamak.password'),
            'from' => config('services.notifications.sms.providers.melli-payamak.from'),
            'text' => $message,
            'to' => (string)preg_replace('/^0/', '', $mobile),
        ];

        $post_data = http_build_query($payload);
        $handle = curl_init('https://rest.payamak-panel.com/api/SendSMS/SendSMS');
        curl_setopt($handle, CURLOPT_HTTPHEADER, array(
            'content-type' => 'application/x-www-form-urlencoded'
        ));

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);


        curl_exec($handle);
        curl_close($handle);

        return true;
    }
}
