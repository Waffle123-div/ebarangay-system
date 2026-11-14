<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StoredFileService
{
    /**
     * Resolve the active cloud object storage disk name (supports standard 's3' as well as
     * dynamically injected Laravel Cloud bucket disks via LARAVEL_CLOUD_DISK_CONFIG).
     */
    public static function cloudDisk(): ?string
    {
        $defaultDisk = config('filesystems.default');
        if (is_string($defaultDisk) && $defaultDisk !== '' && config("filesystems.disks.{$defaultDisk}.driver") === 's3') {
            return $defaultDisk;
        }

        foreach ((array) config('filesystems.disks', []) as $name => $diskConfig) {
            if (($diskConfig['driver'] ?? null) === 's3' && filled($diskConfig['bucket'] ?? null)) {
                return (string) $name;
            }
        }

        return null;
    }

    /**
     * Determine whether cloud object storage (S3 / Laravel Cloud Bucket) is active.
     */
    public static function usesCloudStorage(): bool
    {
        return static::cloudDisk() !== null;
    }

    /**
     * Resolve the target filesystem disk for newly stored files.
     */
    public static function uploadDisk(string $localFallback = 'public'): string
    {
        return static::cloudDisk() ?? $localFallback;
    }

    /**
     * Store an uploaded file on the active storage disk (S3 on cloud, local/public otherwise).
     */
    public static function storeUploadedFile(UploadedFile $file, string $directory, string $localFallback = 'public'): string
    {
        return $file->store(trim($directory, '/'), static::uploadDisk($localFallback));
    }

    /**
     * Write raw file contents onto the active storage disk.
     */
    public static function put(string $path, string $contents, string $localFallback = 'public'): bool
    {
        return (bool) Storage::disk(static::uploadDisk($localFallback))->put(ltrim($path, '/'), $contents);
    }

    /**
     * Delete a stored file from active/local disks if present.
     *
     * @param  array<int, string>  $directoryHints
     * @param  array<int, string>  $localDisks
     */
    public static function delete(?string $rawPath, array $directoryHints = [], array $localDisks = ['public', 'local']): void
    {
        $rawPath = trim((string) $rawPath);
        if ($rawPath === '' || filter_var($rawPath, FILTER_VALIDATE_URL) || str_starts_with($rawPath, 'data:')) {
            return;
        }

        $normalized = ltrim(str_replace('\\', '/', $rawPath), '/');
        if (str_contains($normalized, '..')) {
            return;
        }

        $stripped = preg_replace('#^(?:storage|public)/#', '', $normalized);
        $safeBase = basename($stripped);

        $candidates = [$normalized, $stripped];
        foreach ($directoryHints as $hint) {
            $hint = trim($hint, '/');
            if ($hint !== '' && $safeBase !== '') {
                $candidates[] = $hint . '/' . $safeBase;
            }
        }
        $candidates = array_values(array_unique(array_filter($candidates)));

        $cloudDisk = static::cloudDisk();
        $disks = $cloudDisk !== null
            ? array_values(array_unique(array_merge([$cloudDisk], $localDisks)))
            : $localDisks;

        foreach ($disks as $disk) {
            foreach ($candidates as $candidate) {
                try {
                    if (Storage::disk($disk)->exists($candidate)) {
                        Storage::disk($disk)->delete($candidate);
                    }
                } catch (\Throwable $e) {
                    if ($disk !== $cloudDisk) {
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Ensure a template file stored on cloud storage or local paths exists as a readable local file for PHPWord/download.
     *
     * @param  array<int, string>  $localPaths
     */
    public static function resolveLocalTemplatePath(?string $rawPath, array $localPaths = []): ?string
    {
        foreach ($localPaths as $localPath) {
            if ($localPath && is_file($localPath)) {
                return $localPath;
            }
        }

        $rawPath = trim((string) $rawPath);
        $cloudDisk = static::cloudDisk();
        if ($rawPath === '' || $cloudDisk === null) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $rawPath), '/');
        if (str_contains($normalized, '..')) {
            return null;
        }

        $safeBase = basename($normalized);
        $candidates = array_values(array_unique(array_filter([
            $normalized,
            'document_templates/' . $safeBase,
            $safeBase,
        ])));

        foreach ($candidates as $candidate) {
            try {
                if (Storage::disk($cloudDisk)->exists($candidate)) {
                    $destDir = storage_path('app/document_templates');
                    if (!is_dir($destDir)) {
                        @mkdir($destDir, 0755, true);
                    }
                    $cachedPath = $destDir . DIRECTORY_SEPARATOR . $safeBase;
                    $contents = Storage::disk($cloudDisk)->get($candidate);
                    if ($contents !== null && @file_put_contents($cachedPath, $contents) !== false) {
                        return $cachedPath;
                    }
                }
            } catch (\Throwable $e) {
                // Fall through if cloud disk is temporarily unreachable
            }
        }

        return null;
    }

    /**
     * Resolve the browser-accessible URL for a branding/payment setting image stored on local or cloud storage.
     */
    public static function settingImageUrl(string $key, ?string $rawPath): ?string
    {
        $rawPath = trim((string) $rawPath);
        if ($rawPath === '') {
            return null;
        }

        if (filter_var($rawPath, FILTER_VALIDATE_URL) || str_starts_with($rawPath, 'data:image/')) {
            return $rawPath;
        }

        $safeBase = basename(str_replace('\\', '/', $rawPath));
        if ($safeBase !== '' && \Illuminate\Support\Facades\Route::has('branding.asset')) {
            return route('branding.asset', [
                'key' => $key,
                'filename' => $safeBase,
            ]);
        }

        return Storage::disk(static::uploadDisk('public'))->url($rawPath);
    }

    /**
     * Serve a stored file across S3, local, and public disks (plus fallback storage paths)
     * without requiring a public/storage symlink.
     *
     * @param  array<int, string>  $directoryHints
     */
    public function serve(
        ?string $rawPath,
        array $directoryHints = [],
        string $notFoundMessage = 'File not found.',
        bool $download = false,
        ?string $downloadFilename = null
    ): Response {
        $rawPath = trim((string) $rawPath);
        if ($rawPath === '') {
            abort(404, $notFoundMessage);
        }

        if (filter_var($rawPath, FILTER_VALIDATE_URL)) {
            return redirect()->away($rawPath);
        }

        $normalized = ltrim(str_replace('\\', '/', $rawPath), '/');
        if (str_contains($normalized, '..')) {
            abort(404, $notFoundMessage);
        }

        $stripped = preg_replace('#^(?:storage|public)/#', '', $normalized);
        $safeBase = basename($stripped);

        $candidatePaths = [$normalized, $stripped];
        foreach ($directoryHints as $hint) {
            $hint = trim($hint, '/');
            if ($hint !== '') {
                $candidatePaths[] = $hint . '/' . $safeBase;
            }
        }
        $candidatePaths[] = $safeBase;
        $candidatePaths = array_values(array_unique(array_filter($candidatePaths)));

        $disposition = $download ? 'attachment' : 'inline';
        $targetFilename = $downloadFilename ? basename(str_replace('\\', '/', $downloadFilename)) : $safeBase;

        $cloudDisk = static::cloudDisk();
        $disks = $cloudDisk !== null
            ? array_values(array_unique([$cloudDisk, 'local', 'public']))
            : ['local', 'public'];

        foreach ($disks as $disk) {
            foreach ($candidatePaths as $candidate) {
                try {
                    if (Storage::disk($disk)->exists($candidate)) {
                        return Storage::disk($disk)->response(
                            $candidate,
                            $download ? $targetFilename : null,
                            [],
                            $disposition
                        );
                    }
                } catch (\Throwable $e) {
                    if ($disk !== $cloudDisk) {
                        throw $e;
                    }
                }
            }
        }

        $directPaths = [];
        foreach ($candidatePaths as $candidate) {
            $directPaths[] = storage_path('app/public/' . $candidate);
            $directPaths[] = storage_path('app/private/' . $candidate);
            $directPaths[] = public_path('storage/' . $candidate);
        }
        $directPaths[] = public_path($normalized);

        foreach (array_values(array_unique($directPaths)) as $directPath) {
            if (is_file($directPath)) {
                return $download
                    ? response()->download($directPath, $targetFilename)
                    : response()->file($directPath, ['Content-Disposition' => 'inline']);
            }
        }

        abort(404, $notFoundMessage);
    }
}

