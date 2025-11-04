<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Default disk for file uploads
     */
    protected string $disk = 'public';

    /**
     * Default folder for uploads
     */
    protected string $folder = 'uploads';

    /**
     * Set the disk to use for uploads
     */
    public function disk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Set the folder for uploads
     */
    public function folder(string $folder): self
    {
        $this->folder = $folder;
        return $this;
    }

    /**
     * Upload a file and return the path to be stored in database
     *
     * @param UploadedFile|null $file The uploaded file
     * @param string|null $oldFilePath The old file path to delete (for updates)
     * @param string|null $customFolder Custom folder name (optional)
     * @return string|null The file path to store in database, or null if no file
     */
    public function upload(?UploadedFile $file, ?string $oldFilePath = null, ?string $customFolder = null): ?string
    {
        // If no file provided, return null
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Delete old file if exists
        if ($oldFilePath) {
            $this->delete($oldFilePath);
        }

        // Use custom folder if provided, otherwise use default
        $folder = $customFolder ?? $this->folder;

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);

        // Store file
        $path = $file->storeAs($folder, $filename, $this->disk);

        // Return path relative to storage (without disk prefix)
        // This will be stored in database
        return $path;
    }

    /**
     * Upload multiple files and return array of paths
     *
     * @param array $files Array of UploadedFile objects
     * @param array|null $oldFilePaths Array of old file paths to delete
     * @param string|null $customFolder Custom folder name (optional)
     * @return array Array of file paths
     */
    public function uploadMultiple(array $files, ?array $oldFilePaths = null, ?string $customFolder = null): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $this->upload($file, null, $customFolder);
            }
        }

        // Delete old files if provided
        if ($oldFilePaths) {
            foreach ($oldFilePaths as $oldPath) {
                $this->delete($oldPath);
            }
        }

        return array_filter($paths);
    }

    /**
     * Delete a file from storage
     *
     * @param string|null $filePath The file path stored in database
     * @return bool True if file was deleted, false otherwise
     */
    public function delete(?string $filePath): bool
    {
        if (!$filePath) {
            return false;
        }

        // Delete from storage
        if (Storage::disk($this->disk)->exists($filePath)) {
            return Storage::disk($this->disk)->delete($filePath);
        }

        return false;
    }

    /**
     * Get the full URL for a file path
     *
     * @param string|null $filePath The file path stored in database
     * @return string|null The full URL or null if file doesn't exist
     */
    public function url(?string $filePath): ?string
    {
        if (!$filePath) {
            return null;
        }

        // Check if it's already a full URL
        if (filter_var($filePath, FILTER_VALIDATE_URL)) {
            return $filePath;
        }

        // Check if file exists
        if (!Storage::disk($this->disk)->exists($filePath)) {
            return null;
        }

        // Return public URL using Storage facade
        // For public disk, construct URL manually using config
        if ($this->disk === 'public') {
            $baseUrl = config('filesystems.disks.public.url', config('app.url') . '/storage');
            return rtrim($baseUrl, '/') . '/' . ltrim($filePath, '/');
        }

        // For other disks, try to get URL from config
        $baseUrl = config("filesystems.disks.{$this->disk}.url", config('app.url'));
        return rtrim($baseUrl, '/') . '/' . ltrim($filePath, '/');
    }

    /**
     * Check if file exists
     *
     * @param string|null $filePath The file path stored in database
     * @return bool True if file exists
     */
    public function exists(?string $filePath): bool
    {
        if (!$filePath) {
            return false;
        }

        return Storage::disk($this->disk)->exists($filePath);
    }

    /**
     * Generate a unique filename
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Sanitize filename
        $sanitizedName = Str::slug($originalName);

        // Generate unique filename with timestamp
        $filename = $sanitizedName . '_' . time() . '_' . Str::random(8) . '.' . $extension;

        return $filename;
    }

    /**
     * Static method for quick upload (for dropify and common use cases)
     *
     * @param UploadedFile|null $file
     * @param string|null $oldFilePath
     * @param string|null $folder
     * @return string|null
     */
    public static function uploadFile(?UploadedFile $file, ?string $oldFilePath = null, ?string $folder = null): ?string
    {
        return (new self())->folder($folder ?? 'uploads')->upload($file, $oldFilePath);
    }

    /**
     * Static method for quick delete
     *
     * @param string|null $filePath
     * @return bool
     */
    public static function deleteFile(?string $filePath): bool
    {
        return (new self())->delete($filePath);
    }

    /**
     * Static method for quick URL
     *
     * @param string|null $filePath
     * @return string|null
     */
    public static function getFileUrl(?string $filePath): ?string
    {
        return (new self())->url($filePath);
    }
}
