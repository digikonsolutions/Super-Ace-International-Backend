<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Saves uploads into public/uploads/{folder} and returns the relative path
 * (e.g. "uploads/products/abc.jpg"). Works on shared hosting without storage:link.
 */
class Uploader
{
    public static function store(UploadedFile $file, string $folder): string
    {
        $dir = public_path('uploads/'.$folder);
        File::ensureDirectoryExists($dir);

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
        $filename = Str::limit($name, 40, '').'-'.Str::lower(Str::random(8)).'.'.$ext;

        $file->move($dir, $filename);

        return 'uploads/'.$folder.'/'.$filename;
    }

    public static function delete(?string $path): void
    {
        if ($path && Str::startsWith($path, 'uploads/')) {
            File::delete(public_path($path));
        }
    }
}
