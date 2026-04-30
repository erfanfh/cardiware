<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InspectWordPressDataCommand extends Command
{
    protected $signature = 'wp:inspect';

    protected $description = 'Inspect WordPress database before migration';

    public function handle(): int
    {
        $db = DB::connection('wordpress');

        $this->info('🔍 Inspecting WordPress database...');
        $this->newLine();

        // Posts
        $this->info('📄 Posts by type/status:');

        $posts = $db->table('posts')
            ->select('post_type', 'post_status', DB::raw('COUNT(*) as total'))
            ->groupBy('post_type', 'post_status')
            ->orderBy('post_type')
            ->orderBy('post_status')
            ->get();

        $this->table(
            ['Type', 'Status', 'Total'],
            $posts->map(fn($row) => [
                $row->post_type,
                $row->post_status,
                $row->total,
            ])->toArray()
        );

        $this->newLine();

        // Taxonomies
        $this->info('🏷️ Taxonomies:');

        $taxonomies = $db->table('term_taxonomy')
            ->select('taxonomy', DB::raw('COUNT(*) as total'))
            ->groupBy('taxonomy')
            ->orderBy('taxonomy')
            ->get();

        $this->table(
            ['Taxonomy', 'Total'],
            $taxonomies->map(fn($row) => [
                $row->taxonomy,
                $row->total,
            ])->toArray()
        );

        $this->newLine();

        // Counts
        $usersCount = $db->table('users')->count();
        $commentsCount = $db->table('comments')->count();
        $attachmentsCount = $db->table('posts')->where('post_type', 'attachment')->count();

        $this->info("👤 Users: {$usersCount}");
        $this->info("💬 Comments: {$commentsCount}");
        $this->info("🖼 Attachments: {$attachmentsCount}");

        $this->newLine();

        // Meta Keys
        $this->info('🧠 Top postmeta keys:');

        $metaKeys = $db->table('postmeta')
            ->select('meta_key', DB::raw('COUNT(*) as total'))
            ->groupBy('meta_key')
            ->orderByDesc('total')
            ->limit(100)
            ->get();

        $this->table(
            ['Meta Key', 'Total'],
            $metaKeys->map(fn($row) => [
                $row->meta_key,
                $row->total,
            ])->toArray()
        );

        $this->newLine();
        $this->info('✅ Inspection completed.');

        return self::SUCCESS;
    }
}
