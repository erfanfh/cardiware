<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InspectWordPressAttachmentFormatsCommand extends Command
{
    protected $signature = 'wp:inspect-attachment-formats';

    protected $description = 'Inspect WordPress attachment file extensions and mime types';

    public function handle(): int
    {
        $this->info('Inspecting WordPress attachment formats...');

        $attachments = DB::connection('wordpress')
            ->table('posts')
            ->leftJoin('postmeta', function ($join) {
                $join->on('posts.ID', '=', 'postmeta.post_id')
                    ->where('postmeta.meta_key', '_wp_attached_file');
            })
            ->where('posts.post_type', 'attachment')
            ->select([
                'posts.ID',
                'posts.post_mime_type',
                'postmeta.meta_value as attached_file',
            ])
            ->get();

        $formats = [];

        foreach ($attachments as $attachment) {
            $path = (string)$attachment->attached_file;
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: 'no_extension';
            $mimeType = $attachment->post_mime_type ?: 'no_mime';

            $key = $extension . '|' . $mimeType;

            if (!isset($formats[$key])) {
                $formats[$key] = [
                    'extension' => $extension,
                    'mime_type' => $mimeType,
                    'total' => 0,
                    'example' => $path,
                ];
            }

            $formats[$key]['total']++;
        }

        usort($formats, fn($a, $b) => $b['total'] <=> $a['total']);

        $this->table(
            ['Extension', 'MIME Type', 'Total', 'Example'],
            array_map(fn($item) => [
                $item['extension'],
                $item['mime_type'],
                $item['total'],
                $item['example'],
            ], $formats)
        );

        $this->newLine();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
