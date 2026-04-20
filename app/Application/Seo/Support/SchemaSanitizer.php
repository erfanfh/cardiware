<?php

namespace App\Application\Seo\Support;

final class SchemaSanitizer
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function sanitize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if ($this->isList($value)) {
            $items = [];

            foreach ($value as $item) {
                $sanitized = $this->sanitize($item);

                if ($this->shouldKeep($sanitized)) {
                    $items[] = $sanitized;
                }
            }

            return $items;
        }

        $result = [];

        foreach ($value as $key => $item) {
            $sanitized = $this->sanitize($item);

            if ($this->shouldKeep($sanitized)) {
                $result[$key] = $sanitized;
            }
        }

        return $result;
    }

    protected function shouldKeep(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && $value === []) {
            return false;
        }

        return true;
    }

    /**
     * @param array<mixed> $array
     */
    protected function isList(array $array): bool
    {
        return array_is_list($array);
    }
}
