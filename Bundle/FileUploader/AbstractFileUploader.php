<?php

namespace Module\Dashboard\Bundle\FileUploader;

use finfo;
use Uss\Component\Kernel\Uss;

abstract class AbstractFileUploader
{
    protected array $mimeTypes = [];
    protected int $maxFileSize = 0;
    protected ?string $uploadDirectory = null;
    protected ?string $filenamePrefix = null;
    protected ?string $filename = null;
    protected ?string $fileExtension = null;
    protected ?string $basename = null;
    protected ?string $filepath = null;
    protected ?string $error = null;
    protected bool $isUploaded = false;

    public function __construct(protected array|null $file)
    {
    }

    /**
     * @method fileAvailable
     */
    protected function validateFileAvailability(): void
    {
        $fileExists =
            $this->file &&
            isset($this->file['tmp_name']) &&
            $this->file['error'] === UPLOAD_ERR_OK;

        if(!$fileExists) {

            $errorList = [
                0 => 'File is uploaded successfully',
                1 => 'Uploaded file exceeds the upload_max_filesize limit',
                2 => 'Uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form',
                3 => 'File is partially uploaded or there is an error in between uploading',
                4 => 'No file was uploaded',
                6 => 'Missing a temporary folder',
                7 => 'Failed to write file to disk',
                8 => 'A PHP extension stopped the uploading process'
            ];

            $index = $this->file['error'];

            throw new \Exception($errorList[$index]);
        };
    }

    /**
     * @method validateMimeType
     */
    protected function validateMimeType(): void
    {
        if(!empty($this->mimeTypes)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($this->file['tmp_name']);
            if (!in_array($mimeType, $this->mimeTypes)) {
                throw new \Exception(
                    sprintf(
                        "Unsupported File Type! \n
                        Please ensure your file is in one of the following formats: %s",
                        Uss::instance()->implodeReadable($this->mimeTypes, 'or')
                    )
                );
            };
        }
    }

    /**
     * @method validateFileSize
     */
    protected function validateFileSize(): void
    {
        if($this->maxFileSize) {
            if ($this->file['size'] > $this->maxFileSize) {
                throw new \Exception("File size exceeds the allowed limit");
            }
        }
    }

    /**
     * @method generateFilepath
     */
    protected function generateFilepath(): void
    {
        if(!$this->uploadDirectory) {
            throw new \Exception(
                sprintf(
                    "%s: No upload directory is defined",
                    __CLASS__
                )
            );
        };

        $this->createDirectoryIfNotExists();

        if(!$this->filename) {
            $this->filename = hash_file('md5', $this->file['tmp_name']);
        }

        if(!$this->fileExtension) {
            $this->fileExtension = pathinfo($this->file['name'], PATHINFO_EXTENSION);
        }

        $this->basename = $this->filenamePrefix . $this->filename . "." . $this->fileExtension;
        $this->filepath = $this->uploadDirectory . '/' . $this->basename;
    }

    /**
     * @method createDirectoryIfNotExists
     */
    protected function createDirectoryIfNotExists(): void
    {
        if (!file_exists($this->uploadDirectory)) {
            if(!mkdir($this->uploadDirectory, 0777, true)) {
                $lastError = error_get_last();
                throw new \Exception(
                    sprintf(
                        "%s: %s",
                        __CLASS__,
                        $lastError['message'] ?? 'Unknown Error'
                    )
                );
            }
        }
    }

    /**
     * @method moveUploadedPath
     */
    protected function moveUploadedPath(): bool
    {
        $this->isUploaded = move_uploaded_file($this->file['tmp_name'], $this->filepath);
        if(!$this->isUploaded) {
            $this->error = "Failed to move uploaded file";
        };
        return $this->isUploaded;
    }
}
