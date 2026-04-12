<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadFile extends Model
{
    use HasFactory;

    protected $table = 'Uploads';

    public const UPDATED_AT = null;

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'OriginalName',
        'StoredName',
        'Disk',
        'Path',
        'MimeType',
        'Size',
        'CreatedBy',
        'CreatedAt',
    ];

    protected function casts(): array
    {
        return [
            'Id' => 'integer',
            'Size' => 'integer',
            'CreatedBy' => 'integer',
            'CreatedAt' => 'datetime',
        ];
    }
}
