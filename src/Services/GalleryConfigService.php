<?php

namespace MBsoft\FileGallery\Services;

class GalleryConfigService
{
    protected array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function get(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->settings[$key] = $value;
    }

    public function usesDatabase(): bool
    {
        return $this->get('database', false);
    }
}
