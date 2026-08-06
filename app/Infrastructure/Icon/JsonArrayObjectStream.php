<?php

namespace App\Infrastructure\Icon;

use Generator;
use JsonException;
use RuntimeException;

class JsonArrayObjectStream
{
    public function __construct(
        protected int $chunkSize = 8192,
        protected int $maxObjectBytes = 1048576,
    ) {
    }

    /**
     * @return Generator<int, array<string, mixed>>
     */
    public function read(string $path): Generator
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Icon JSON file [$path] could not be opened.");
        }

        $arrayStarted = false;
        $arrayFinished = false;
        $capturing = false;
        $expectingValue = true;
        $hasItems = false;
        $objectDepth = 0;
        $inString = false;
        $escaped = false;
        $buffer = '';
        $itemIndex = 0;
        $firstChunk = true;

        try {
            while (!feof($handle)) {
                $chunk = fread($handle, $this->chunkSize);

                if ($chunk === false) {
                    throw new RuntimeException("Icon JSON file [$path] could not be read.");
                }

                if ($firstChunk) {
                    $chunk = preg_replace('/^\xEF\xBB\xBF/', '', $chunk) ?? $chunk;
                    $firstChunk = false;
                }

                $length = strlen($chunk);

                for ($offset = 0; $offset < $length; $offset++) {
                    $character = $chunk[$offset];

                    if ($arrayFinished) {
                        if (!ctype_space($character)) {
                            throw new RuntimeException("Unexpected content after the root JSON array in [$path].");
                        }

                        continue;
                    }

                    if (!$arrayStarted) {
                        if (ctype_space($character)) {
                            continue;
                        }

                        if ($character !== '[') {
                            throw new RuntimeException("Icon JSON file [$path] must contain a root array.");
                        }

                        $arrayStarted = true;
                        continue;
                    }

                    if (!$capturing) {
                        if (ctype_space($character)) {
                            continue;
                        }

                        if ($expectingValue) {
                            if ($character === ']') {
                                if ($hasItems) {
                                    throw new RuntimeException("Trailing comma found in icon JSON file [$path].");
                                }

                                $arrayFinished = true;
                                continue;
                            }

                            if ($character !== '{') {
                                throw new RuntimeException("Every icon entry in [$path] must be a JSON object.");
                            }

                            $capturing = true;
                            $objectDepth = 1;
                            $inString = false;
                            $escaped = false;
                            $buffer = '{';
                            continue;
                        }

                        if ($character === ',') {
                            $expectingValue = true;
                            continue;
                        }

                        if ($character === ']') {
                            $arrayFinished = true;
                            continue;
                        }

                        throw new RuntimeException("Expected a comma or the end of the JSON array in [$path].");
                    }

                    $buffer .= $character;

                    if (strlen($buffer) > $this->maxObjectBytes) {
                        throw new RuntimeException("An icon entry in [$path] exceeds the allowed size.");
                    }

                    if ($inString) {
                        if ($escaped) {
                            $escaped = false;
                            continue;
                        }

                        if ($character === '\\') {
                            $escaped = true;
                            continue;
                        }

                        if ($character === '"') {
                            $inString = false;
                        }

                        continue;
                    }

                    if ($character === '"') {
                        $inString = true;
                        continue;
                    }

                    if ($character === '{') {
                        $objectDepth++;
                        continue;
                    }

                    if ($character !== '}') {
                        continue;
                    }

                    $objectDepth--;

                    if ($objectDepth !== 0) {
                        continue;
                    }

                    try {
                        $decoded = json_decode($buffer, true, flags: JSON_THROW_ON_ERROR);
                    } catch (JsonException $exception) {
                        throw new RuntimeException(
                            "Invalid icon entry at index [$itemIndex] in [$path]: {$exception->getMessage()}",
                            previous: $exception,
                        );
                    }

                    if (!is_array($decoded)) {
                        throw new RuntimeException("Icon entry [$itemIndex] in [$path] is not an object.");
                    }

                    yield $itemIndex => $decoded;

                    $itemIndex++;
                    $buffer = '';
                    $capturing = false;
                    $expectingValue = false;
                    $hasItems = true;
                }
            }
        } finally {
            fclose($handle);
        }

        if (!$arrayStarted) {
            throw new RuntimeException("Icon JSON file [$path] is empty.");
        }

        if ($capturing) {
            throw new RuntimeException("Icon JSON file [$path] ended before an icon object was complete.");
        }

        if (!$arrayFinished) {
            throw new RuntimeException("Icon JSON file [$path] ended before the root array was closed.");
        }
    }
}
