<?php
	namespace Dplus\FileServices;
	
	use Dplus\Base\StringerBell;
	use SimpleMail;
	use LogmUser;
	
	/**
	 * Class that implements php-simple-mail to Send Emails out of Dpluso
	 */
	class DplusEmailer {
		use \Dplus\Base\ThrowErrorTrait;
		use \Dplus\Base\MagicMethodTraits;
		
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
		 * @var array
		 */
		protected $emailto;
		
		/**
		 * Reply to 
		 * Key Value array that is Email => Reply Name
		 * @var EmailContact
		 */
		protected $replyto;
		
		/**
		 * Who to show the Email was from
		 * Key Value array that is Email => Email From Name
		 * @var EmailContact
		 */
		protected $emailfrom;

		/**
		 * Blind Carbon Copy
		 * @var array
		 */
		protected $bcc = false;
		
		/**
		 * Carbon Copy
		 * @var array
		 */
		protected $cc = false;
		
		/**
		 * Email Body
		 * @var string
		 */
		protected $body;
		
		/**
		 * Array of Filepaths (string)
		 * @var array File Paths
		 */
		protected $files = array();
		
		/**
		 * Send Blind Carbon to Self
		 * @var bool
		 */
		protected $selfbcc = false;

		
		/* =============================================================
			SETTERS
		============================================================ */
		/**
		 * Sets the Reply To and Email From Logm User Details
		 * @param string $userID User's Login ID
		 */
		public function set_fromlogmuser($userID) {
			$user = LogmUser::load($userID);

			if (!$user) {
				$this->error("Could Not Find User with loginid of $userID");
				return false;
			}
			$contact = new EmailContact();
			$contact->set('email', $user->email);
			$contact->set('name', $user->name);
			$contact->set('phone', $user->phone);
			$this->replyto = $contact;
			$this->emailfrom = $contact;
		}

		/**
		 * Sets the subject of the email
		 * @param string $subject Subject Line
		 */
		public function set_subject($subject) {
			$this->subject = $subject;
		}
		
		/**
		 * Sets the Body text
		 * @param string  $body Body Text
		 * @param bool    $html If body contains HTML
		 */
		public function set_body($body, $html = true) {
			$stringer = new StringerBell();
			$this->hashtml = $html;
			$body .= "<br>". $this->replyto->name;
			$body .= "<br>" . $this->replyto->email;
			$body .= "<br>" . $stringer->format_phone($this->replyto->phone) ;
			$this->body = $body;
		}
		
		/**
		 * Adds to $this->files array
		 * @param string $filepath path/to/file
		 */
		public function add_file($filepath) {
			$this->hasfile = true;
			$this->files[] = $filepath;
		}

		/**
		 * Add contact to send to 
		 * @param EmailContact $contact Contact 
		 */
		public function add_emailto(EmailContact $contact) {
			$this->emailto[] = $contact;
		}
		
		/** 
		 * Set the Carbon Copy Array
		 * @param EmailContact $contact Contact
		 */
		public function add_cc(EmailContact $contact) {
			$this->cc[] = $contact;
		}
		
		/** 
		 * Set the Blind Carbon Copy Array
		 * @param EmailContact $contact Contact
		 */
		public function add_bcc(EmailContact $contact) {
			$this->bcc[] = $contact;
		}
		
		/**
		 * Set Self Blind Carbon Copy on or off
		 * @param bool $val 
		 */
		public function set_selfbcc($val = true) {
			$this->selfbcc = $val;
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
			
			foreach ($this->emailto as $contact) {
				$emailer->setTo($contact->email, $contact->name);
			}
			
			$emailer->setFrom($this->emailfrom->email, $this->emailfrom->name);
			$emailer->setReplyTo($this->replyto->email, $this->replyto->name);
			
			if ($this->selfbcc) {
				$this->add_bcc($this->replyto);
			}
			
			// setBcc allows setting from Array
			if (!empty($this->bcc)) {
				$bcc = array();
				foreach ($this->bcc as $contact) {
					$bcc[$contact->name] = $contact->email;
				}
				$emailer->setBcc($bcc);
			}
			
			if (!empty($this->cc)) {
				$cc = array();
				foreach ($this->cc as $contact) {
					$cc[$contact->name] = $contact->email;
				}
				$emailer->setCc($cc);
			}
			
			if ($this->hasfile) {
				foreach($this->files as $file) {
					$emailer->addAttachment($file);
				}
				
			}
			return $emailer->send();
		}
	}

	class EmailContact {
		use \Dplus\Base\ThrowErrorTrait;
		use \Dplus\Base\MagicMethodTraits;
		use \Dplus\Base\CreateFromObjectArrayTraits;
		
		/**
		 * Email
		 * @var string
		 */
		protected $email;
		
		/**
		 * Contact Name
		 * @var string
		 */
		protected $name;
		
		/**
		 * Contact Phone
		 * @var string
		 */
		protected $phone;

	}
