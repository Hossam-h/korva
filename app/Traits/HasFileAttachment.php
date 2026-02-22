<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasFileAttachment
{
    /**
     * Boot the trait and register model events.
     */
    protected static function bootHasFileAttachment(): void
    {
        static::deleting(function ($model) {
            $model->deleteAllAttachments();
        });
    }

    /**
     * Upload or update a file attachment.
     *
     * @param  string  $fieldName  The database column name (e.g., 'thumbnail', 'image', 'logo')
     * @param  string|null  $folder  Custom folder path (optional, defaults to model name)
     * @param  string|null  $disk  Storage disk (optional, defaults to config filesystems.default)
     */
    public function uploadFile(
        UploadedFile $file,
        string $fieldName,
        ?string $folder = null,
        ?string $disk = null
    ): bool {
        try {
            // Get storage disk
            $disk = $disk ?? config('filesystems.default');

            // Get folder path
            $folder = $folder ?? $this->getDefaultFolder();

            // Delete old file if exists
            if ($this->$fieldName) {
                $this->deleteFile($fieldName, $disk);
            }

            // Generate unique filename
            $filename = $this->generateFileName($file);

            // Store file
            $path = $file->storeAs($folder, $filename, $disk);

            // Update model
            $this->$fieldName = $path;
            $this->save();

            return true;
        } catch (\Exception $e) {
            Log::error('File upload failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Delete a specific file attachment.
     *
     * @param  string  $fieldName  The database column name
     * @param  string|null  $disk  Storage disk (optional)
     */
    public function deleteFile(string $fieldName, ?string $disk = null): bool
    {
        try {
            if (! $this->$fieldName) {
                return true; // No file to delete
            }

            $disk = $disk ?? config('filesystems.default');

            // Delete file from storage
            if (Storage::disk($disk)->exists($this->$fieldName)) {
                Storage::disk($disk)->delete($this->$fieldName);
            }

            // Remove reference from database
            $this->$fieldName = null;
            $this->save();

            return true;
        } catch (\Exception $e) {
            Log::error('File deletion failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Delete all file attachments for the model.
     */
    public function deleteAllAttachments(): void
    {
        $fileFields = $this->getFileFields();

        foreach ($fileFields as $field) {
            if ($this->$field) {
                $this->deleteFile($field);
            }
        }
    }

    /**
     * Get the full URL of a file attachment.
     *
     * @param  string  $fieldName  The database column name
     * @param  string|null  $disk  Storage disk (optional)
     */
    public function getFileUrl(string $fieldName, ?string $disk = null): ?string
    {
        if (! $this->$fieldName) {
            return null;
        }

        $disk = $disk ?? config('filesystems.default');

        return Storage::disk($disk)->url($this->$fieldName);
    }

    /**
     * Get the full path of a file attachment.
     *
     * @param  string  $fieldName  The database column name
     * @param  string|null  $disk  Storage disk (optional)
     */
    public function getFilePath(string $fieldName, ?string $disk = null): ?string
    {
        if (! $this->$fieldName) {
            return null;
        }

        $disk = $disk ?? config('filesystems.default');

        return Storage::disk($disk)->path($this->$fieldName);
    }

    /**
     * Check if a file exists.
     *
     * @param  string  $fieldName  The database column name
     * @param  string|null  $disk  Storage disk (optional)
     */
    public function hasFile(string $fieldName, ?string $disk = null): bool
    {
        if (! $this->$fieldName) {
            return false;
        }

        $disk = $disk ?? config('filesystems.default');

        return Storage::disk($disk)->exists($this->$fieldName);
    }

    /**
     * Get the default folder name based on model name.
     */
    protected function getDefaultFolder(): string
    {
        $modelName = Str::snake(class_basename($this));

        return Str::plural($modelName);
    }

    /**
     * Generate a unique filename.
     */
    protected function generateFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $timestamp = now()->timestamp;
        $random = Str::random(8);

        return "{$name}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get all file fields from the model.
     * Override this method in your model to specify which fields contain file paths.
     */
    protected function getFileFields(): array
    {
        // Default implementation: check common file field names
        $commonFields = ['thumbnail', 'image', 'logo', 'photo', 'avatar', 'file', 'document', 'attachment'];

        $fileFields = [];
        foreach ($commonFields as $field) {
            if (in_array($field, $this->getFillable()) || property_exists($this, $field)) {
                $fileFields[] = $field;
            }
        }

        return $fileFields;
    }
}
