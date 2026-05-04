<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixSeoMetaVariables extends Command
{
    protected $signature = 'seo:fix-meta-variables';
    protected $description = 'Replace Yoast variables in seo_metas table';

    public function handle(): void
    {
        DB::table('seo_metas')
            ->where(function ($query) {
                $query->where('title', 'like', '%\%\%%')
                    ->orWhere('description', 'like', '%\%\%%');
            })
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {

                    $title = $this->replace($row->title);
                    $description = $this->replace($row->description);

                    DB::table('seo_metas')
                        ->where('id', $row->id)
                        ->update([
                            'title' => $title,
                            'description' => $description,
                        ]);
                }
            });

        $this->info('SEO meta variables replaced successfully!');
    }

    private function replace(?string $text): ?string
    {
        if (!$text) {
            return $text;
        }

        return str_replace(
            ['%%sep%%', '%%sitename%%'],
            ['{separator}', '{site_title}'],
            $text
        );
    }
}
