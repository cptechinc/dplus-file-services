<?php 
	namespace Dplus\FileServices;
	
	use mikehaertl\wkhtmlto\Pdf as Wkhtmlpdf;
	use Dplus\ProcessWire\DplusWire;
	
	class PDFMaker extends Wkhtmlpdf {
		use \Dplus\Base\ThrowErrorTrait;
		use \Dplus\Base\MagicMethodTraits;
		
		/**
		 * URL of Page to convert to PDF
		 * @var string
		 */
		protected $url = false;
		
		/**
		 * File Identifier ex. Sales Order Number, Quote Number, RGA #
		 * @var string
		 */
		protected $fileID = false;
		
		/**
		 * File Type is a descriptor to add to the end of the file
		 * IE quote
		 * @var string
		 */
		protected $filetype;
		
		/**
		 * What the File name will be
		 * @var string
		 */
		protected $filename;
		
		/**
		 * Options to provide to wkhtmltopdf
		 * @var array
		 */
		protected $options = array(
			'binary' => '/usr/local/bin/wkhtmltopdf',
			// Explicitly tell wkhtmltopdf that we're using an X environment
			'use-xserver',
			//'footer-right "Page [page] of [toPage]"',
			// Enable built in Xvfb support in the command
			'commandOptions' => array(
				'enableXvfb' => true,
				// Optional: Set your path to xvfb-run. Default is just 'xvfb-run'.
				// 'xvfbRunBinary' => '/usr/bin/xvfb-run',
				// Optional: Set options for xfvb-run. The following defaults are used.
				// 'xvfbRunOptions' =>  '--server-args="-screen 0, 1024x768x24"',
			),
		);
		
		/**
		 * Folders for each document type
		 * @var array
		 */
		public static $folders = array(
			'quote' => 'CUSTQT'
		);
		
		public function __construct($fileID, $filetype, $url) {
			parent::__construct($this->options);
			$this->fileID = $fileID;
			$this->filetype = $filetype;
			$this->url = $url;
		}
		
		public function add_pagenumber() {
			$this->options[] = 'footer-right "Page [page] of [toPage]"';
			$this->setOptions($this->options);
		}
		
		/**
		 * Takes the fileID and URL and Makes a PDF out of that page
		 * @return string file
		 */
		public function process() {
			$filename = str_replace("-$this->filetype", '', $this->fileID)."-$this->filetype";
			$file = DplusWire::wire('config')->documentstoragedirectory."$filename.pdf";
			$this->filename = "$filename.pdf";
			
			if (file_exists($file)) {
				unlink($file);
			}
			$this->addPage($this->url);
			
			if (!$this->saveAs($file)) {
				$this->error($this->getError());
				return false;
			}
			//echo $this->getCommand()->getOutput();
			return $file;
		}
	}
