<?php
    namespace Dplus\FileServices;
    
    /**
     * Class for Creating Spreadsheets
     */
    class SpreadSheetWriter {
        use \Dplus\Base\ThrowErrorTrait;
		use \Dplus\Base\MagicMethodTraits;
        
        /**
         * File that will be created by any one of the functions
         * @var UploadedFile;
         */
        protected $outputfile;
        
        /**
         * Converts Spreadsheet file into a new spreadsheet with Uppercase strings
         * @param  UploadedFile $readfile       Original File
         * @param  string       $save_extension File Extension for the new file
         * @return void
         */
        public function convert_filetouppercase(UploadedFile $readfile, $save_extension) {
            // Check the Original File's extension in order to get the proper parser
            switch ($readfile->extension) {
                case 'xls':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                    break;
                case 'xlsx':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                    break;
                default:
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                    $reader->setInputEncoding('CP1252');
                    $reader->setSheetIndex(0);
                    $reader->setDelimiter("\t");
                    break;
            }
            $spreadsheet = $reader->load($readfile->get_filepath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                // This loops through all cells,
                //    even if a cell value is not set.
                // By default, only cells that have a value
                //    set will be iterated.
                $cellIterator->setIterateOnlyExistingCells(FALSE); 
                foreach ($cellIterator as $cell) {
                    $cell->setValue(strtoupper($cell->getValue()));
                }
            }
            
            // Create new file, using the original file properties
            // Set the extension to be the new extension
            // NOTE the file does not exist yet until the Writer saves
            $this->outputfile = UploadedFile::create_fromobject($readfile);
            $this->outputfile->set_extension($save_extension);
            
            // Instantiate the correct Spreadsheet Writer based on file extension
            switch ($save_extension) {
                case 'xls':
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                    break;
                case 'xlsx':
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    break;
                default:
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                    $writer->setUseBOM(true);
                    $writer->setLineEnding("\r\n");
                    $writer->setSheetIndex(0);
                    $writer->setDelimiter("\t");
                    break;
            }
            $writer->save($this->outputfile->get_filepath());
        }
    }
