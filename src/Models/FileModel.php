<?php

namespace MBsoft\FileGallery\Models;

use Illuminate\Database\Eloquent\Model;

class FileModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'original_name',
        'filename',
        'path',
        'extension',
        'size',
        'disk',
        'mime_type',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the full URL to the stored file.
     */
    public function getUrlAttribute(): string
    {
        return \Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get a human-readable size format (e.g., KB, MB).
     */
    public function getFormattedSizeAttribute(): string
    {
        $size = $this->size;

        if ($size >= 1048576) {
            return round($size / 1048576, 2).' MB';
        } elseif ($size >= 1024) {
            return round($size / 1024, 2).' KB';
        }

        return $size.' B';
    }
}
