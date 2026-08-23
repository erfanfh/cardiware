<?php

namespace App\Http\Controllers\Admin\Poll;

use App\Domain\Poll\Models\Poll;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PollExportController extends Controller
{
    public function __invoke(Poll $poll): StreamedResponse
    {
        Gate::authorize('poll.export');

        return response()->streamDownload(function () use ($poll): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['شناسه پاسخ', 'نوع کاربر', 'شناسه عضو', 'نام نمایشی', 'گزینه‌ها', 'زمان ثبت']);
            $poll->responses()->with(['user:id,display_name', 'options:id,label'])->orderBy('id')->chunk(500, function ($responses) use ($handle): void {
                foreach ($responses as $response) {
                    fputcsv($handle, [
                        $response->id,
                        $response->user_id ? 'عضو' : 'مهمان',
                        $response->user_id,
                        $response->user?->display_name,
                        $response->options->pluck('label')->implode(' | '),
                        $response->submitted_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($handle);
        }, 'poll-'.$poll->id.'-responses.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
