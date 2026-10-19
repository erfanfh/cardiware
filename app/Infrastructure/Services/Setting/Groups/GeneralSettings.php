<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class GeneralSettings extends BaseSettings
{
    protected string $group = 'general';
    protected array $allowedKeys = [
        'active_tab',
        'title',
        'subtitle',
        'system_report_recipients',

        'default_sms_provider',

        'smsir_login_pattern',
        'smsir_login_name_parameter',
        'smsir_login_time_parameter',
        'smsir_login_ip_parameter',
        'smsir_login_source_parameter',
        'farazsms_login_pattern',
        'farazsms_login_name_parameter',
        'farazsms_login_time_parameter',
        'farazsms_login_ip_parameter',
        'farazsms_login_source_parameter',
        'melipayamak_login_pattern',

        'smsir_ticket_reply_pattern',
        'smsir_ticket_reply_name_parameter',
        'smsir_ticket_reply_ticket_number_parameter',
        'farazsms_ticket_reply_pattern',
        'farazsms_ticket_reply_name_parameter',
        'farazsms_ticket_reply_ticket_number_parameter',
        'melipayamak_ticket_reply_pattern',
        'smsir_order_paid_pattern',
        'smsir_order_paid_name_parameter',
        'smsir_order_paid_order_number_parameter',
        'farazsms_order_paid_pattern',
        'farazsms_order_paid_name_parameter',
        'farazsms_order_paid_order_number_parameter',
        'melipayamak_order_paid_pattern',

        'smsir_api_key',
        'smsir_otp_template_id',
        'smsir_otp_parameter_name',

        'farazsms_api_key',
        'farazsms_line_number',
        'farazsms_otp_pattern',
        'farazsms_otp_attribute_name',

        'melipayamak_username',
        'melipayamak_password',
        'melipayamak_otp_pattern',

        'select_related_content_settings',
        'category_tag_posts_count_settings',
        'search_posts_count_settings',
        'search_projects_count_settings',
        'search_products_count_settings',
        'search_videos_count_settings',
        'author_posts_count_settings',

        'default_payment_gateway',
        'tax_enabled',
        'tax_rate_percent',
        'checkout_terms_enabled',
        'checkout_terms_url',
        'zibal_sandbox',
        'zibal_merchant_id',
        'zarinpal_merchant_id',
        'zarinpal_sandbox',
        'payping_sandbox',
        'payping_token',
        'aqayepardakht_pin',
        'aqayepardakht_sandbox',

        'goftino_code',

        // Public installer shown on /launcher and launcher bridge pages.
        // This is intentionally separate from launcher update packages.
        'launcher_public_download_url',
        'launcher_download_max_connections',

        // Remote kill switch: when enabled, the Windows launcher blocks login/session-restore
        // and shows launcher_maintenance_message instead. Falls back to the LAUNCHER_MAINTENANCE_*
        // env vars (config/launcher.php) when never set from this panel, so existing deployments
        // keep working without needing this row populated.
        'launcher_maintenance_enabled',
        'launcher_maintenance_message',
        // One mobile number per line. A device whose owning account matches one of
        // these numbers keeps normal access while maintenance is on -- lets the team
        // test a build against the real server before lifting maintenance for
        // everyone. Checked by GetLauncherBootstrapAction against the device_id the
        // launcher already sends with every bootstrap call (see DeviceIdentityService
        // on the client -- not a secret, just the same locally-generated id already
        // used at login).
        'launcher_maintenance_bypass_mobiles',
        // Emergency offline mode before an internet shutdown -- see LauncherEmergencyOffline.
        'launcher_emergency_offline_enabled',
        'launcher_emergency_offline_days',
        // VPN / non-Iranian IP check -- see GetLauncherBootstrapAction::networkCheck.
        'launcher_vpn_policy',
        'launcher_vpn_message',
        'launcher_vpn_extra_ranges',
    ];

    protected array $casts = [
        'favicon' => MediaFileCast::class,
        'zibal_sandbox' => BooleanCast::class,
        'zarinpal_sandbox' => BooleanCast::class,
        'payping_sandbox' => BooleanCast::class,
        'aqayepardakht_sandbox' => BooleanCast::class,
        'tax_enabled' => BooleanCast::class,
        'checkout_terms_enabled' => BooleanCast::class,
        'launcher_maintenance_enabled' => BooleanCast::class,
        'launcher_emergency_offline_enabled' => BooleanCast::class,
    ];
}
