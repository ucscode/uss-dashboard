<?php

namespace Module\Dashboard\Bundle\FileUploader;

use Uss\Component\Kernel\Uss;

class FileUploader extends AbstractFileUploader
{
    /**
     * Example of MimeType includes:
     *
     * - image/png
     * - application/zip
     * - text/plain
     * - video/mp4 ...
     */
    public function addMimeType(string $mimeType): self
    {
        $mimeType = trim(strtolower($mimeType));
        if(!in_array($mimeType, $this->mimeTypes, true)) {
            $this->mimeTypes[] = $mimeType;
        }
        return $this;
    }

    public function removeMimeType(string $mimeType): self
    {
        $mimeType = trim(strtolower($mimeType));
        $key = array_search($mimeType, $this->mimeTypes);
        if($key !== false) {
            unset($this->mimeTypes[$key]);
            $this->mimeTypes = array_values($this->mimeTypes);
        }
        return $this;
    }

    public function setMimeTypes(array $mimeTypes): self
    {
        $this->mimeTypes = array_values(
            array_unique(
                array_map(
                    fn ($value) => trim(strtolower($value)),
                    array_filter($mimeTypes, fn ($value) => is_string($value) && !empty($value))
                )
            )
        );
        return $this;
    }

    public function getMimeTypes(): array
    {
        return $this->mimeTypes;
    }

    /**
     * The max filesize is set in byte.
     * 
     * TIP: 1024 bytes = 1KB
     */
    public function setMaxFileSize(int $maxBytes): self
    {
        $this->maxFileSize = abs($maxBytes);
        return $this;
    }

    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * @method setUploadDirectory
     */
    public function setUploadDirectory(string $uploadDirectory): self
    {
        $abspath = Uss::instance()->isAbsolutePath($uploadDirectory);
        if(!$abspath) {
            throw new \Exception(
                sprintf(
                    "%s: Upload directory must be a valid absolute path",
                    __CLASS__
                )
            );
        }
        $this->uploadDirectory = $uploadDirectory;
        return $this;
    }

    public function getUploadDirectory(): ?string
    {
        return $this->uploadDirectory;
    }

    /**
     * You may need to use function like "uniqid()" to generate unique filenames
     * to avoid overwriting existing files
     * @method setFilenamePrefix
     */
    public function setFilenamePrefix(string $filenamePrefix): self
    {
        $this->filenamePrefix = $filenamePrefix;
        return $this;
    }

    public function getFilenamePrefix(): ?string
    {
        return $this->filenamePrefix;
    }

    /**
     * @method setFilename
     */
    public function setFilename(string $filename, ?string $fileExtension = null): self
    {
        $this->filename = $filename;
        if($fileExtension) {
            preg_match('/\b[a-z]+\b/', strtolower($fileExtension), $matches);
            $this->fileExtension = $matches[0];
        }
        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    /**
     * @method uploadFile
     */
    public function uploadFile(): bool
    {
        try {
            $this->validateFileAvailability();
            $this->validateMimeType();
            $this->validateFileSize();
        } catch(\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        $this->generateFilepath();
        return $this->moveUploadedPath();
    }

    /**
     * @method getFilepath
     */
    public function getUploadedFilepath(): ?string
    {
        return $this->isUploaded ? $this->filepath : null;
    }

    /**
     * @method getError
     */
    public function getError(bool $msg = false): string|int|null
    {
        return $msg ? $this->error : $this->file['error'];
    }
}
