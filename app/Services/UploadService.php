<?php

namespace App\Services;

use App\Models\UploadFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadService
{
    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    public function uploadMany(array $files, int $actorId): array
    {
        $disk = config('filesystems.default', 'local');
        $urls = [];

        foreach ($files as $file) {
            $storedName = now()->format('dmYHis').'_'.$file->getClientOriginalName();
            $safeStoredName = (string) Str::of($storedName)->replaceMatches('/[^A-Za-z0-9._-]/', '_');
            $path = $file->storeAs('media/'.now()->format('Y/m/d'), (string) $safeStoredName, $disk);

            $upload = UploadFile::query()->create([
                'OriginalName' => $file->getClientOriginalName(),
                'StoredName' => (string) $safeStoredName,
                'Disk' => (string) $disk,
                'Path' => $path,
                'MimeType' => $file->getClientMimeType() ?? '',
                'Size' => (int) $file->getSize(),
                'CreatedBy' => $actorId,
                'CreatedAt' => now(),
            ]);

            $urls[] = Storage::disk($disk)->url($upload->Path);
        }

        return $urls;
    }
}
