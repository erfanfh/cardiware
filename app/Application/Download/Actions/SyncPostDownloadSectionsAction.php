<?php

namespace App\Application\Download\Actions;

use App\Domain\Content\Models\Post;
use App\Domain\Download\Models\DownloadEntry;
use App\Domain\Download\Models\DownloadSection;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncPostDownloadSectionsAction
{
    /**
     * @param Post $post
     * @param array $sectionsData
     * @return void
     * @throws Throwable
     */
    public function execute(Post $post, array $sectionsData): void
    {
        DB::transaction(function () use ($post, $sectionsData) {

            $existingSectionIds = $post->downloadSections()->pluck('id')->all();
            $incomingSectionIds = collect($sectionsData)
                ->pluck('id')
                ->filter()
                ->all();

            // Delete removed sections
            $sectionsToDelete = array_diff($existingSectionIds, $incomingSectionIds);
            if (!empty($sectionsToDelete)) {
                DownloadSection::query()
                    ->whereIn('id', $sectionsToDelete)
                    ->delete();
            }

            foreach ($sectionsData as $sectionData) {
                $section = DownloadSection::updateOrCreate(
                    [
                        'id' => $sectionData['id'] ?? null,
                    ],
                    [
                        'post_id' => $post->id,
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'] ?? null,
                        'password' => $sectionData['password'] ?? null,
                        'sort_order' => $sectionData['sort_order'] ?? 0,
                    ]
                );

                $this->syncEntries($section, $sectionData['entries'] ?? []);
            }
        });
    }

    private function syncEntries(DownloadSection $section, array $entriesData): void
    {
        $existingEntryIds = $section->entries()->pluck('id')->all();
        $incomingEntryIds = collect($entriesData)
            ->pluck('id')
            ->filter()
            ->all();

        // Soft delete removed entries
        $entriesToDelete = array_diff($existingEntryIds, $incomingEntryIds);
        if (!empty($entriesToDelete)) {
            DownloadEntry::query()
                ->whereIn('id', $entriesToDelete)
                ->delete();
        }

        foreach ($entriesData as $entryData) {
            if (!empty($entryData['id'])) {
                $entry = DownloadEntry::query()
                    ->where('id', $entryData['id'])
                    ->where('download_section_id', $section->id)
                    ->first();

                if ($entry) {
                    $entry->update([
                        'type' => $entryData['type'],
                        'title' => $entryData['title'],
                        'content' => $entryData['content'],
                        'sort_order' => $entryData['sort_order'] ?? 0,
                    ]);

                    continue;
                }
            }

            DownloadEntry::create([
                'download_section_id' => $section->id,
                'type' => $entryData['type'],
                'title' => $entryData['title'],
                'content' => $entryData['content'],
                'sort_order' => $entryData['sort_order'] ?? 0,
            ]);
        }
    }
}
