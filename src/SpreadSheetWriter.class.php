<?php
    namespace Dplus\FileServices;

    use PhpOffice\PhpSpreadsheet\Reader\Xls, PhpOffice\PhpSpreadsheet\Reader\Xlsx, PhpOffice\PhpSpreadsheet\Reader\Csv;
    use PhpOffice\PhpSpreadsheet\Writer\Xls, PhpOffice\PhpSpreadsheet\Writer\Xlsx, PhpOffice\PhpSpreadsheet\Writer\Csv;

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
                    $reader = new Xls();
                    break;
                case 'xlsx':
                    $reader = new Xlsx();
                    break;
                default:
                    $reader = new Csv();
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
                    $writer = new Xls($spreadsheet);
                    break;
                case 'xlsx':
                    $writer = new Xlsx($spreadsheet);
                    break;
                default:
                    $writer = new Csv($spreadsheet);
                    $writer->setUseBOM(true);
                    $writer->setLineEnding("\r\n");
                    $writer->setSheetIndex(0);
                    $writer->setDelimiter("\t");
                    break;
            }
            $writer->save($this->outputfile->get_filepath());
        }
    }
