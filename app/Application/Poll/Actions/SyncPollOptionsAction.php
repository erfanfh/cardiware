<?php

namespace App\Application\Poll\Actions;

use App\Domain\Poll\Models\Poll;

class SyncPollOptionsAction
{
    /** @param array<int,array<string,mixed>> $items */
    public function execute(Poll $poll, array $items): void
    {
        $kept = [];

        foreach (array_values($items) as $index => $item) {
            $id = isset($item['id']) ? (int) $item['id'] : null;
            $option = $id ? $poll->options()->whereKey($id)->first() : null;
            $payload = [
                'label' => trim((string) ($item['label'] ?? '')),
                'description' => filled($item['description'] ?? null) ? trim((string) $item['description']) : null,
                'color' => preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($item['color'] ?? '')) ? $item['color'] : '#f97316',
                'sort_order' => $index,
                'is_active' => (bool) ($item['is_active'] ?? true),
            ];

            if ($option) {
                $option->update($payload);
            } else {
                $option = $poll->options()->create($payload);
            }
            $kept[] = $option->getKey();
        }

        $poll->options()->whereNotIn('id', $kept)->get()->each(function ($option): void {
            if ((int) $option->responses_count === 0) {
                $option->delete();
            } else {
                $option->update(['is_active' => false]);
            }
        });
    }
}
