<?php

class FileUploader
{
    private array $mimeTypes = [];
    private int $maxFileSize = 0;
    private ?string $uploadDirectory = null;
    private string $filenamePrefix = '';
    private ?string $filename = null;
    private ?string $fileExtension = null;
    private ?string $basename = null;
    private ?string $filepath = null;
    private ?string $error = null;
    private bool $uploaded = false;

    public function __construct(
        private array|null $file
    ) { 
    }

    /**
     * @method addMimeType
     */
    public function addMimeType(string|array $mimeType): void 
    {
        if(is_string($mimeType)) {
            $mimeType = [$mimeType];
        }
        $mimeType = array_values($mimeType);
        $this->mimeTypes = array_map(function($value) {
            return trim(strtolower($value));
        }, array_unique([...$this->mimeTypes, ...$mimeType]));
    }

    /**
     * @method setMaxFileSize
     */
    public function setMaxFileSize(int $maxFileSize): void
    {
        $this->maxFileSize = abs($maxFileSize);
    }

    /**
     * @method setUploadDirectory
     */
    public function setUploadDirectory(string $uploadDirectory): void
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
    }
    
    /**
     * You may need to use function like "uniqid()" to generate unique filenames 
     * to avoid overwriting existing files
     * @method setFilenamePrefix
     */
    public function setFilenamePrefix(string $filenamePrefix): void
    {
        $this->filenamePrefix = $filenamePrefix;
    }

    /**
     * @method setFilename
     */
    public function setFilename(string $filename, ?string $fileExtension = null): void
    {
        $this->filename = $filename;
        if($fileExtension) {
            preg_match('/\b[a-z]+\b/', $fileExtension, $matches);
            $this->fileExtension = $matches[0];
        }
    }

    /**
     * @method getFilepath
     */
    public function getUploadedFilepath(): ?string
    {
        return $this->uploaded ? $this->filepath : null;
    }

    /**
     * @method getError
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @method uploadFile
     */
    public function uploadFile(): bool
    {
        try {
            $this->fileAvailable();
            $this->validateMimeType();
            $this->validateFileSize();
        } catch(\Exception $e) {
            return !($this->error = $e->getMessage());
        }
        $this->generateFilepath();
        return $this->moveUploadedPath();
    }

    /**
     * @method fileAvailable
     */
    protected function fileAvailable(): void
    {
        $fileExists = $this->file && isset($this->file['tmp_name']) && $this->file['error'] === UPLOAD_ERR_OK;
        if(!$fileExists) {
            $errorList = array(
                0 => 'File is uploaded successfully',
                1 => 'Uploaded file exceeds the upload_max_filesize limit',
                2 => 'Uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form',
                3 => 'File is partially uploaded or there is an error in between uploading',
                4 => 'No file was uploaded',
                6 => 'Missing a temporary folder',
                7 => 'Failed to write file to disk',
                8 => 'A PHP extension stopped the uploading process'
            );
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
                        "Invalid file type. Allowed types: %s",
                        implode(', ', $this->mimeTypes)
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
    public function createDirectoryIfNotExists(): void
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
    public function moveUploadedPath(): bool
    {
        $this->uploaded = move_uploaded_file($this->file['tmp_name'], $this->filepath);
        if(!$this->uploaded) {
            $this->error = "Failed to move uploaded file";
        };
        return $this->uploaded;
    }
}