<?php

namespace Tests\Feature\Support;

use App\Application\Support\Support\TicketAttachmentValidationRules;
use App\Http\Requests\Admin\Support\ReplyTicketRequest;
use App\Infrastructure\Services\Setting\Groups\TicketSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TicketAttachmentValidationRulesTest extends TestCase
{
    public function test_valid_images_pass_dynamic_attachment_rules(): void
    {
        $validator = Validator::make([
            'attachments' => [
                UploadedFile::fake()->image('evidence.jpg', 400, 300),
                UploadedFile::fake()->image('screen.png', 500, 400),
            ],
        ], $this->rules(maxFiles: 2, maxSizeMb: 1));

        self::assertTrue($validator->passes(), $validator->errors()->first());
    }

    public function test_non_image_and_spoofed_extension_are_rejected(): void
    {
        $validator = Validator::make([
            'attachments' => [
                UploadedFile::fake()->createWithContent('payload.jpg', '<?php echo "unsafe";'),
            ],
        ], $this->rules());

        self::assertTrue($validator->fails());
        self::assertTrue($validator->errors()->has('attachments.0'));
    }

    public function test_admin_file_count_and_size_limits_are_enforced(): void
    {
        $tooMany = Validator::make([
            'attachments' => [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->image('two.jpg'),
            ],
        ], $this->rules(maxFiles: 1));

        $tooLarge = Validator::make([
            'attachments' => [
                UploadedFile::fake()->create('large.jpg', 2049, 'image/jpeg'),
            ],
        ], $this->rules(maxSizeMb: 2));

        self::assertTrue($tooMany->errors()->has('attachments'));
        self::assertTrue($tooLarge->errors()->has('attachments.0'));
    }

    public function test_admin_reply_request_accepts_the_same_secured_attachment_fields(): void
    {
        $settings = new class extends TicketSettings
        {
            public function attachmentsPerMessage(): int
            {
                return 3;
            }

            public function attachmentMaxSizeMegabytes(): int
            {
                return 2;
            }
        };
        $this->app->instance(TicketSettings::class, $settings);

        $rules = (new ReplyTicketRequest)->rules();

        self::assertArrayHasKey('attachments', $rules);
        self::assertArrayHasKey('attachments.*', $rules);
        self::assertContains('max:3', $rules['attachments']);
        self::assertContains('max:2048', $rules['attachments.*']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(int $maxFiles = 4, int $maxSizeMb = 5): array
    {
        $settings = new class($maxFiles, $maxSizeMb) extends TicketSettings
        {
            public function __construct(
                private readonly int $maxFiles,
                private readonly int $maxSizeMb,
            ) {
                parent::__construct();
            }

            public function attachmentsPerMessage(): int
            {
                return $this->maxFiles;
            }

            public function attachmentMaxSizeMegabytes(): int
            {
                return $this->maxSizeMb;
            }
        };

        return (new TicketAttachmentValidationRules($settings))->rules();
    }
}
