<?php

namespace App\Support;

use App\Models\LeaveRequest;

class LeaveAttachmentLinks
{
    public static function paths(LeaveRequest $request): array
    {
        if (empty($request->file_uploads)) {
            return [];
        }

        $decoded = json_decode($request->file_uploads, true);

        if (is_array($decoded)) {
            return array_values(array_filter($decoded, static fn ($path) => is_string($path) && trim($path) !== ''));
        }

        $single = trim((string) $request->file_uploads);

        return $single !== '' ? [$single] : [];
    }

    /**
     * @return list<array{label: string, url: string, downloadUrl: string, extension: string, name: string, previewable: bool}>
     */
    public static function forRequest(LeaveRequest $request): array
    {
        $links = [];

        foreach (self::paths($request) as $index => $path) {
            $url = route('leave.attachments.show', [
                'leaveRequest' => $request->id,
                'attachment' => $index + 1,
            ]);

            $name = basename($path) ?: ('attachment_' . ($index + 1));
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            $links[] = [
                'label' => 'File ' . ($index + 1),
                'url' => $url,
                'downloadUrl' => $url . '?download=1',
                'extension' => $extension,
                'name' => $name,
                'previewable' => self::isPreviewableExtension($extension),
            ];
        }

        return $links;
    }

    /**
     * Signed attachment links for Senior Manager emails (no login required).
     *
     * @return list<array{label: string, viewUrl: string, downloadUrl: string, extension: string, name: string, previewable: bool}>
     */
    public static function forEmailRequest(LeaveRequest $request, int $daysValid = 7): array
    {
        $links = [];
        $expires = now()->addDays($daysValid);

        foreach (self::paths($request) as $index => $path) {
            $attachment = $index + 1;
            $name = basename($path) ?: ('attachment_'.$attachment);
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            $viewUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'leave.email.attachment',
                $expires,
                ['leaveRequest' => $request->id, 'attachment' => $attachment]
            );
            $downloadUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'leave.email.attachment',
                $expires,
                ['leaveRequest' => $request->id, 'attachment' => $attachment, 'download' => 1]
            );

            $links[] = [
                'label' => 'File '.$attachment,
                'viewUrl' => $viewUrl,
                'downloadUrl' => $downloadUrl,
                'extension' => $extension,
                'name' => $name,
                'previewable' => self::isPreviewableExtension($extension),
            ];
        }

        return $links;
    }

    public static function isPreviewableExtension(string $extension): bool
    {
        return in_array(strtolower($extension), ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    public static function isPreviewablePath(string $path): bool
    {
        return self::isPreviewableExtension(strtolower(pathinfo($path, PATHINFO_EXTENSION)));
    }

    public static function deleteStoredFiles(LeaveRequest $request): void
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        foreach (self::paths($request) as $path) {
            $path = ltrim($path, '/');
            if ($path !== '' && $disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }
}
