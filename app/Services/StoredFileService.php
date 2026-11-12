<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StoredFileService
{
    /**
     * Serve a stored file across local/public disks and fallback storage paths
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

        foreach (['local', 'public'] as $disk) {
            foreach ($candidatePaths as $candidate) {
                if (Storage::disk($disk)->exists($candidate)) {
                    return Storage::disk($disk)->response(
                        $candidate,
                        $download ? $targetFilename : null,
                        [],
                        $disposition
                    );
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

