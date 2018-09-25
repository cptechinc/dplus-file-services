<?php
	/**
	 * Class that implements php-simple-mail to Send Emails out of Dpluso
	 */
	class DplusEmailer {
		use ThrowErrorTrait;
		
		/**
		 * User ID of person mailing, used to retreive their name and email
		 * @var string
		 */
		protected $user;
		
		/**
		 * Does Email Contain HTML
		 * Due to some outlook 365 issues always will be false
		 * @var bool
		 */
		protected $hashtml = false;
		
		/**
		 * If email needs to have attachments
		 * @var bool
		 */
		protected $hasfile;
		
		/**
		 * Where the file is located usually want to stick to one file directory
		 * @var string path of the file directory on server
		 */
		public static $filedirectory;
		
		/**
		 * Subject of Email
		 * @var string
		 */
		protected $subject;
		/**
		 * Recipient(s) of email
		 * Key Value array that is Email => Recipient Name
		 * @var array
		 */
		protected $emailto = false;
		
		/**
		 * Reply to 
		 * Key Value array that is Email => Reply Name
		 * @var array
		 */
		protected $replyto = false;
		
		/**
		 * Who to show the Email was from
		 * Key Value array that is Email => Email From Name
		 * @var array
		 */
		protected $emailfrom = false; 
		
		/**
		 * Blind Carbon Copy
		 * Key Value array that is Recipient => Email Name
		 * @var array
		 */
		protected $bcc = false;
		
		/**
		 * Carbon Copy
		 * Key Value array that is Recipient => Email Name
		 * @var array
		 */
		protected $cc = false;
		
		/**
		 * Email Body
		 * @var string
		 */
		protected $body;
		
		/**
		 * Attached File
		 * @var string File Path
		 */
		protected $file = false;
		
		/**
		 * Send Blind Carbon to Self
		 * @var bool
		 */
		protected $selfbcc = false;
		
		/**
		 * Creates Instance of Dplus Emailer
		 * @param string $loginID User's Login ID
		 */
		function __construct($loginID) {
			$this->user = LogmUser::load($loginID);
			if (!$this->user) {
				$this->error("Could Not Find User with loginid of $userID");
				return false;
			}
			$this->replyto = array($this->user->email => $this->user->name);
			$this->emailfrom = array($this->user->email => $this->user->name);
		}
		
		/* =============================================================
			GETTERS
		============================================================ */
		/**
		 * Magic function to open up properties to be examined
		 * @param  string $property string
		 * @return mixed          Property value
		 */
		public function __get($property) {
			$method = "get_{$property}";
			if (method_exists($this, $method)) {
				return $this->$method();
			} elseif (property_exists($this, $property)) {
				return $this->$property;
			} else {
				$this->error("This property ($property) does not exist");
			}
		}
		
		/**
		 * Return File Directory
		 * @return string Directory This instance is using for files
		 */
		public function get_filedirectory() {
			return self::$filedirectory;
		}
		
		/* =============================================================
			SETTERS
		============================================================ */
		/**
		 * Since properties are protected, we make a set function to allow changing of values
		 * @param string $property Property of Instance
		 * @param mixed $data     value to give to $this->$property
		 */
		public function set($property, $data) {
			$method = "set_{$property}";
			if (method_exists($this, $method)) {
				return $this->$method($data);
			} else {
				$this->error("This property ($property) does not exist");
			}
		}
		
		/**
		 * Sets the subject of the email
		 * @param string $subject Subject Line
		 */
		public function set_subject($subject) {
			$this->subject = $subject;
		}
		
		/**
		 * Sets Email to 
		 * @param string $email Recipient Email
		 * @param string $name  Recipient Name
		 */
		public function set_emailto($email, $name) {
			$this->emailto = array($email => $name); 
		}
		
		/**
		 * Sets the Body text
		 * @param string  $body Body Text
		 * @param bool  $html If body contains HTML
		 */
		public function set_body($body, $html = true) {
			$stringer = new StringerBell();
			$this->hashtml = $html;
			$body .= "<br>". $this->user->name;
			$body .= "<br>" . $this->user->email;
			$body .= "<br>" . $stringer->format_phone($this->user->phone) ;
			$this->body = $body;
		}
		
		/**
		 * Sets the value for filename
		 * @param string $filename path/to/file
		 */
		public function set_file($filename) {
			$this->hasfile = true;
			$this->file = $filename;
		}
		
		/** 
		 * Set the Carbon Copy Array
		 * @param string $email Email to Carbon Copy
		 * @param string $name  Name of Recipient to Carbon Copy
		 */
		public function set_cc($email, $name) {
			$this->cc = array($name => $email);
		}
		
		/** 
		 * Set the Blind Carbon Copy Array
		 * @param string $email Email to Blind Carbon Copy
		 * @param string $name  Name of Recipient to Carbon Copy
		 */
		public function set_bcc($email, $name) {
			$this->bcc = array($name => $email);
		}
		
		/**
		 * Set Self Blind Carbon Copy on or off
		 * @param bool $val 
		 */
		public function set_selfbcc($val = true) {
			$this->selfbcc = $val;
		}
		
		/**
		 * Set the file directory to use
		 * @param string $dir directory path
		 */
		public function set_filedirectory($dir) {
			self::$filedirectory = $dir;
		}
		
		/* =============================================================
			CLASS FUNCTIONS
		============================================================ */
		/**
		 * Sends the email using php-simple-mail
		 * Attaches and sets the properties and values as needed
		 * @return mixed true or false
		 */
		public function send() {
			$emailer = SimpleMail::make()
			->setSubject($this->subject)
			->setMessage($this->body);
			
			foreach ($this->emailto as $email => $name) {
				$emailer->setTo($email, $name);
			}
			
			foreach ($this->emailfrom as $email => $name) {
				$emailer->setFrom($email, $name);
			}
			
			foreach ($this->replyto as $email => $name) {
				$emailer->setReplyTo($email, $name);
			}
			
			if ($this->selfbcc) {
				$this->set_bcc($this->user->email, $this->user->name);
			}
			
			// setBcc allows setting from Array
			if (!empty($this->bcc)) {
				$emailer->setBcc($this->bcc);
			}
			
			if ($this->hasfile) {
				if (strpos($this->filedirectory, $this->file) !== false) {
					$emailer->addAttachment($this->filedirectory.$this->file);
				} else {
					$emailer->addAttachment($this->file);
				}
			}
			
			/* if ($this->hashtml) {
				//$emailer->setHtml();
			} */
			return $emailer->send();
		}
	}
