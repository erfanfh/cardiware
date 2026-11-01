<?php

namespace Database\Seeders\Raffle;

use App\Application\Raffle\Actions\Campaign\CreateDrawCampaignAction;
use App\Application\Raffle\Actions\Campaign\TransitionDrawCampaignStatusAction;
use App\Application\Raffle\Actions\Draw\ExecuteDrawRunAction;
use App\Application\Raffle\Actions\Draw\StartInitialDrawRunAction;
use App\Application\Raffle\Actions\Ticket\IssueDrawTicketsAction;
use App\Application\Raffle\Actions\Winner\ConfirmDrawWinnerAction;
use App\Application\Raffle\DTOs\IssueDrawTicketsDTO;
use App\Application\Raffle\DTOs\TransitionDrawCampaignStatusDTO;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawWinner;
use App\Infrastructure\Services\Setting\Groups\RaffleSettings;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * دموی قرعه‌کشی: یک کمپین باز با شمارش معکوس زنده + یک کمپین تکمیل‌شده که
 * قرعه‌کشی آن با موتور واقعی (seed اثبات‌پذیر) اجرا شده تا بخش «شفافیت و صحت‌سنجی»
 * و برندگان عمومی با داده واقعی دیده شوند.
 */
class RaffleDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (DrawCampaign::query()->count() > 0) {
            return;
        }

        $admin = User::query()->where('email', 'admin@moddingway.ir')->firstOrFail();
        $user = User::query()->where('email', 'user@moddingway.ir')->firstOrFail();

        // قرعه‌کشی خودکار در زمان مقرر برای همه کمپین‌ها فعال شود.
        app(RaffleSettings::class)->set('auto_start_draw_enabled', true);

        $this->seedOpenCampaign($admin, $user);
        $this->seedCompletedCampaign($admin, $user);
    }

    private function seedOpenCampaign(User $admin, User $user): void
    {
        $campaign = app(CreateDrawCampaignAction::class)->execute([
            'title' => 'قرعه کشی ویژه پچ FC27',
            'short_description' => 'سه جایزه ارزشمند بین دارندگان بلیط این کمپین توزیع می‌شود.',
            'body' => 'با امتیازهای باشگاه مخاطبان می‌توانی برای این قرعه‌کشی بلیط بگیری. هر بلیط یک شانس است و برندگان با الگوریتم شفاف و اثبات‌پذیر مودینگ‌وی انتخاب می‌شوند.',
            'rules' => "هر کاربر حداکثر ۵ بلیط می‌تواند داشته باشد.\nبلیط‌ها فقط تا پیش از بسته شدن کمپین قابل دریافت هستند.\nنتیجه با seed اثبات‌پذیر منتشر می‌شود.",
            'starts_at' => now()->subDays(2)->toDateTimeString(),
            'closes_at' => now()->addDays(5)->toDateTimeString(),
            'draw_at' => now()->addDays(6)->toDateTimeString(),
            'ticket_limit' => 500,
            'ticket_limit_per_user' => 5,
            'reserve_winners_per_prize' => 2,
            'winner_confirmation_window_hours' => 72,
            'show_winners_publicly' => true,
            'prizes' => [
                ['title' => 'اشتراک VIP شش‌ماهه', 'description' => 'دسترسی کامل به همه محصولات ویژه', 'estimated_value' => 594000, 'quantity' => 1],
                ['title' => 'کد تخفیف ۲۰۰ هزار تومانی', 'description' => 'قابل استفاده روی همه محصولات دانلودی', 'estimated_value' => 200000, 'quantity' => 2],
                ['title' => 'جعبه معمایی امتیاز', 'description' => 'بین ۱۰ تا ۱۰۰ هزار تومان اعتبار', 'estimated_value' => 50000, 'quantity' => 3],
            ],
        ], $admin);

        $this->transition($campaign, DrawCampaignStatusEnum::SCHEDULED, now()->subDays(3)->toImmutable(), 'demo_scheduled');
        $this->transition($campaign, DrawCampaignStatusEnum::OPEN, now()->subDays(2)->toImmutable(), 'demo_open');

        // کاربر دمو چند بلیط دارد تا تجربه «بلیط‌های من» پر باشد.
        $this->issueTickets($campaign, $user, 3);
        $this->issueTickets($campaign, $admin, 1);
    }

    private function seedCompletedCampaign(User $admin, User $user): void
    {
        $campaign = app(CreateDrawCampaignAction::class)->execute([
            'title' => 'قرعه کشی مگاپک اکتبر',
            'short_description' => 'قرعه‌کشی تکمیل‌شده قبلی؛ نتیجه و صحت‌سنجی آن منتشر شده است.',
            'body' => 'این کمپین به پایان رسیده و برندگان آن با seed اثبات‌پذیر انتخاب و منتشر شده‌اند. می‌توانی صحت نتیجه را در بخش «شفافیت و صحت‌سنجی» صفحه کمپین بررسی کنی.',
            'rules' => 'نتیجه با الگوریتم شفاف انتخاب شده است.',
            'starts_at' => now()->subDays(25)->toDateTimeString(),
            'closes_at' => now()->subDays(3)->toDateTimeString(),
            'draw_at' => now()->subDays(3)->toDateTimeString(),
            'ticket_limit' => 300,
            'ticket_limit_per_user' => 4,
            'reserve_winners_per_prize' => 2,
            'winner_confirmation_window_hours' => 168,
            'show_winners_publicly' => true,
            'prizes' => [
                ['title' => 'مگاپک کامل FC26', 'description' => 'همه پچ‌ها و مادهای فصل', 'estimated_value' => 850000, 'quantity' => 1],
                ['title' => 'کد تخفیف ۱۰۰ هزار تومانی', 'description' => 'قابل استفاده روی سبد خرید', 'estimated_value' => 100000, 'quantity' => 2],
            ],
        ], $admin);

        $this->transition($campaign, DrawCampaignStatusEnum::SCHEDULED, now()->subDays(26)->toImmutable(), 'demo_scheduled');
        $this->transition($campaign, DrawCampaignStatusEnum::OPEN, now()->subDays(25)->toImmutable(), 'demo_open');

        // شرکت‌کنندگان واقعی برای قرعه‌کشی معنادار — بلیط‌ها باید در حالت OPEN صادر شوند
        $participants = [$user, $admin];
        foreach (['rahl1@moddingway.ir', 'rahl2@moddingway.ir', 'rahl3@moddingway.ir', 'rahl4@moddingway.ir'] as $index => $email) {
            $participants[] = User::query()->where('email', $email)->first()
                ?? User::factory()->create([
                    'email' => $email,
                    'first_name' => 'شرکت‌کننده',
                    'last_name' => 'دمو ' . ($index + 1),
                ]);
        }

        $quantities = [2, 1, 3, 2, 4, 1];
        foreach ($participants as $index => $participant) {
            $this->issueTickets($campaign, $participant, $quantities[$index] ?? 1, now()->subDays(20)->toImmutable());
        }

        $this->transition($campaign, DrawCampaignStatusEnum::CLOSED, now()->subDays(3)->toImmutable(), 'demo_close');

        // اجرای واقعی قرعه‌کشی با seed اثبات‌پذیر
        $run = app(StartInitialDrawRunAction::class)->execute($campaign, $admin);
        app(ExecuteDrawRunAction::class)->execute((int) $run->getKey());

        // تایید برندگان اصلی تا در صفحه عمومی نمایش داده شوند
        DrawWinner::query()
            ->where('campaign_id', $campaign->getKey())
            ->where('kind', DrawWinnerKindEnum::PRIMARY->value)
            ->where('status', DrawWinnerStatusEnum::PENDING->value)
            ->get()
            ->each(function (DrawWinner $winner) use ($admin): void {
                try {
                    app(ConfirmDrawWinnerAction::class)->execute($winner, $admin);
                } catch (Throwable) {
                    // تایید تک‌تک برندگان برای دمو حیاتی نیست
                }
            });
    }

    private function transition(DrawCampaign $campaign, DrawCampaignStatusEnum $status, CarbonImmutable $occurredAt, string $reason): void
    {
        app(TransitionDrawCampaignStatusAction::class)->execute(
            new TransitionDrawCampaignStatusDTO($campaign, $status, occurredAt: $occurredAt, reason: $reason),
        );

        $campaign->refresh();
    }

    private function issueTickets(DrawCampaign $campaign, User $participant, int $quantity, ?CarbonImmutable $occurredAt = null): void
    {
        app(IssueDrawTicketsAction::class)->execute(new IssueDrawTicketsDTO(
            campaign: $campaign->refresh(),
            user: $participant,
            quantity: $quantity,
            idempotencyKey: 'demo-raffle:' . $campaign->getKey() . ':' . $participant->getKey(),
            occurredAt: $occurredAt,
        ));
    }
}
