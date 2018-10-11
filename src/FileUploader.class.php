<?php
    namespace Dplus\FileServices;
    
    /**
     * Class to handle File Uploading from $_FILES array and moves it to a 
     */
    class FileUploader {
        use \Dplus\Base\ThrowErrorTrait;
		use \Dplus\Base\MagicMethodTraits;
        
        /**
         * Allowed File Extensions
         * @var array
         */
        public static $allowed_extensions = ['xls', 'csv', 'xlsx', 'txt']; // Get all the file extensions
        
        /**
         * Error Messages
         * @var array
         */
        protected $errors = []; // Store all foreseen and unforseen errors here
        
        /**
         * Was File Uploaded
         * @var bool
         */
        protected $uploaded = false;
        
        /**
         * File 
         * @var UploadedFile
         */
        protected $file;
        
        /**
         * Creates a new file from the $_FILES array
         * @param  array $files     $_FILES array
         * @param  string $filename File Name to rename file to
         * @return bool             Was File Uploaded?
         */
        public function upload($files, $filename = '') {
            $this->file = UploadedFile::create_fromfilesarray($files);
            
            if (!empty($filename)) {
                $this->file->set('name', "$filename.{$this->file->extension}");
            }
            
            if (!in_array($this->file->extension, self::$allowed_extensions)) {
                 $this->errors[] = "This file extension ({$this->file->extension}) is not allowed";
            }
            
            if (empty($this->errors)) {
                $this->uploaded = move_uploaded_file($this->file->tmpname, DplusWire::wire('config')->documentstoragedirectory.$this->file->name);
                
                if (!$this->uploaded) {
                    $this->errors[] = "File Failed to upload";
                } else {
                    $this->file->set('directory', DplusWire::wire('config')->documentstoragedirectory);
                }
            }
            return $this->uploaded;
        }
    }
