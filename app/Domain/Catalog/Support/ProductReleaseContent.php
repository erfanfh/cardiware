<?php

namespace App\Domain\Catalog\Support;

final class ProductReleaseContent
{
    public static function fromChanges(iterable $changes): string
    {
        $items = '';

        foreach ($changes as $change) {
            $title = htmlspecialchars((string) $change->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            if ($change->is_special) {
                $title = '<strong>'.$title.'</strong>';
            }

            $items .= '<li>'.$title.'</li>';
        }

        return $items === '' ? '' : '<ul>'.$items.'</ul>';
    }
}
