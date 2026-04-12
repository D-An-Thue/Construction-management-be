<?php

namespace App\Services;

use App\Models\UploadFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadService
{
    /**
     * @return array<string, mixed>
     */
    public function upload(UploadedFile $file, int $actorId): array
    {
        $disk = config('filesystems.default', 'local');
        $storedName = (string) Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $storedName, $disk);

        $upload = UploadFile::query()->create([
            'OriginalName' => $file->getClientOriginalName(),
            'StoredName' => $storedName,
            'Disk' => (string) $disk,
            'Path' => $path,
            'MimeType' => $file->getClientMimeType() ?? '',
            'Size' => (int) $file->getSize(),
            'CreatedBy' => $actorId,
            'CreatedAt' => now(),
        ]);

        return [
            'Id' => $upload->Id,
            'OriginalName' => $upload->OriginalName,
            'StoredName' => $upload->StoredName,
            'Disk' => $upload->Disk,
            'Path' => $upload->Path,
            'MimeType' => $upload->MimeType,
            'Size' => $upload->Size,
            'Url' => Storage::disk($disk)->url($path),
        ];
    }
}
