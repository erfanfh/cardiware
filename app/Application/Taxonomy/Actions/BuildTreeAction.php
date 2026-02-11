<?php

namespace App\Application\Taxonomy\Actions;

use Illuminate\Database\Eloquent\Collection;

class BuildTreeAction
{
    /**
     * Build a hierarchical tree structure from items with their children recursively.
     *
     * @param Collection|array $items
     * @param int $level
     * @return array
     */
    public function execute($items, int $level = 0): array
    {
        $result = [];

        foreach ($items as $item) {
            $item->level = $level;
            $result[] = $item;

            if ($item->children->isNotEmpty()) {
                $result = array_merge($result, $this->execute($item->children, $level + 1));
            }
        }

        return $result;
    }
}
