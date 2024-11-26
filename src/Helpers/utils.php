<?php

function app_dir(): string
{
    return dirname(__DIR__, 2);
}

function storage_dir($path = null): string
{
    return app_dir() . '/storage' . ($path == null ? '' : '/'.$path);
}
