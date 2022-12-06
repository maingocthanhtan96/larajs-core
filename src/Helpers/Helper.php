<?php

/**
 * write log when exception
 *
 * @param    $e
 * @return void
 */
function write_log_exception($e): void
{
    $content = '';
    $r = explode('#0', $e->getTraceAsString());
    if (isset($r[1])) {
        $r = explode('#10', $r[1]);
        $content = $r[0];
    }
    \Illuminate\Support\Facades\Log::error($e->getMessage() . PHP_EOL . '#0 More exception::' . $content . PHP_EOL . PHP_EOL);
}

function package_path(string $type, string $path): string
{
    return match ($type) {
        'common' => base_path("../../packages/common/lib/$path"),
        default => base_path("../../packages/$path"),
    };
}

/**
 * @param  string  $direction
 * @return string
 */
function convert_direction(string $direction = 'asc'): string
{
    return in_array($direction, ['ascending', 'asc']) ? 'asc' : 'desc';
}