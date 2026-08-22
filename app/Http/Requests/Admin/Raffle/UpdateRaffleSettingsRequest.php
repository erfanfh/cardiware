<?php

namespace App\Http\Requests\Admin\Raffle;

use App\Infrastructure\Services\Sms\Providers\FarazSmsProvider;
use App\Infrastructure\Services\Sms\Providers\MeliPayamakProvider;
use App\Infrastructure\Services\Sms\Providers\SmsIrProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class UpdateRaffleSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('raffle-campaign.edit');
    }

    public function rules(): array
    {
        return [
            'winner_sms_enabled' => ['required', 'boolean'],
            'winner_reminder_sms_enabled' => ['required', 'boolean'],
            'winner_reminder_hours_before_deadline' => ['required', 'integer', 'min:1', 'max:168'],
            'auto_start_draw_enabled' => ['required', 'boolean'],
            'show_in_header_navigation' => ['required', 'boolean'],

            'smsir_winner_template_id' => ['nullable', 'string', 'max:100'],
            'smsir_winner_name_parameter' => ['nullable', 'string', 'max:100'],
            'smsir_winner_campaign_parameter' => ['nullable', 'string', 'max:100'],
            'smsir_winner_prize_parameter' => ['nullable', 'string', 'max:100'],
            'smsir_winner_deadline_parameter' => ['nullable', 'string', 'max:100'],
            'smsir_reminder_template_id' => ['nullable', 'string', 'max:100'],
            'smsir_reminder_name_parameter' => ['nullable', 'string', 'max:100'],
            'smsir_reminder_campaign_parameter' => ['nullable', 'string', 'max:100'],
            'smsir_reminder_prize_parameter' => ['nullable', 'string', 'max:100'],
            'smsir_reminder_deadline_parameter' => ['nullable', 'string', 'max:100'],

            'farazsms_winner_pattern' => ['nullable', 'string', 'max:100'],
            'farazsms_winner_name_attribute' => ['nullable', 'string', 'max:100'],
            'farazsms_winner_campaign_attribute' => ['nullable', 'string', 'max:100'],
            'farazsms_winner_prize_attribute' => ['nullable', 'string', 'max:100'],
            'farazsms_winner_deadline_attribute' => ['nullable', 'string', 'max:100'],
            'farazsms_reminder_pattern' => ['nullable', 'string', 'max:100'],
            'farazsms_reminder_name_attribute' => ['nullable', 'string', 'max:100'],
            'farazsms_reminder_campaign_attribute' => ['nullable', 'string', 'max:100'],
            'farazsms_reminder_prize_attribute' => ['nullable', 'string', 'max:100'],
            'farazsms_reminder_deadline_attribute' => ['nullable', 'string', 'max:100'],

            'melipayamak_winner_pattern' => ['nullable', 'string', 'max:100'],
            'melipayamak_reminder_pattern' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->boolean('winner_sms_enabled') && ! $this->boolean('winner_reminder_sms_enabled')) {
                return;
            }

            $provider = get_setting('general', 'default_sms_provider');
            $types = [];
            if ($this->boolean('winner_sms_enabled')) {
                $types[] = 'winner';
            }
            if ($this->boolean('winner_reminder_sms_enabled')) {
                $types[] = 'reminder';
            }

            if ($provider === MeliPayamakProvider::class) {
                foreach ($types as $type) {
                    $this->requireField($validator, "melipayamak_{$type}_pattern");
                }

                return;
            }

            $config = match ($provider) {
                SmsIrProvider::class => ['prefix' => 'smsir', 'id' => 'template_id', 'name' => 'parameter'],
                FarazSmsProvider::class => ['prefix' => 'farazsms', 'id' => 'pattern', 'name' => 'attribute'],
                default => null,
            };

            if (! $config) {
                $validator->errors()->add('winner_sms_enabled', 'ابتدا سرویس پیش‌فرض پیامک را در تنظیمات عمومی انتخاب کنید.');

                return;
            }

            foreach ($types as $type) {
                $this->requireField($validator, "{$config['prefix']}_{$type}_{$config['id']}");
                foreach (['name', 'campaign', 'prize', 'deadline'] as $parameter) {
                    $this->requireField($validator, "{$config['prefix']}_{$type}_{$parameter}_{$config['name']}");
                }
            }
        }];
    }

    private function requireField(Validator $validator, string $field): void
    {
        if (blank($this->input($field))) {
            $validator->errors()->add($field, 'این مقدار برای سرویس پیامکی فعال الزامی است.');
        }
    }
}
