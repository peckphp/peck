<?php

declare(strict_types=1);

it('presets are sorted alphabetically', function (): void {
    $presetPath = __DIR__.'/../../stubs/presets';

    $files = array_diff(scandir($presetPath), ['.', '..']);

    foreach ($files as $file) {
        // Read the content of the preset file
        $content = file_get_contents(sprintf('%s/%s', $presetPath, $file));
        $lines = explode("\n", $content);

        // Remove empty lines and trim whitespace
        $lines = array_filter(array_map('trim', $lines));

        // Sort the lines
        $sortedLines = $lines;
        sort($sortedLines);

        // Compare if the lines are sorted
        expect($lines)->toBe($sortedLines);
    }
});
