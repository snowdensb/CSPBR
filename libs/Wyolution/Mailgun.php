<?php

namespace Wyolution;

/**
 * Wyolution wrapper for standard PHPMailer
 * -- Lets us manage dev and production configurations for email
 * 
 * @package Wyolution
 */
class Mailgun {
	
	private $f3;
	protected $mailgunUrl;
	protected $mailgunKey;
	protected $emailReplyTo;
	protected $defaultFromEmail;
	protected $skipSendingMail;
	
	/**
	 * Constructor.
	 */
	function __construct()
	{
		$this->f3 = \Base::Instance();
		$this->mailgunUrl = $this->f3->get('mailgunUrl');
		$this->mailgunKey = $this->f3->get('mailgunKey');
		$this->emailReplyTo = $this->f3->get('emailReplyTo');
		$this->defaultFromEmail = $this->f3->get('defaultFromEmail');
		$this->skipSendingMail = $this->f3->get('skipSendingMail') == '1';
		\Wyolution\Logger::debug("Mailer constructed");
	}
	
	private function settingsValid() {
	    $isConfigured = !empty($this->mailgunUrl) &&
	       !empty($this->mailgunKey) && 
	       !empty($this->emailReplyTo) &&
	       !empty($this->defaultFromEmail);
	    
	    return $isConfigured;
	}
	
	public function setFrom(string $from) {
	    if (!empty($from)) {
	       $this->defaultFromEmail = $from;
	    }
	}
	
	public function setReplyTo(string $replyTo) {
	    if (!empty($replyTo)) {
	        $this->emailReplyTo = $replyTo;
	    }
	}
	
	public function sendMail($to,$subject,$messageHtml,$message=''):bool {
	    
	    if (!$this->settingsValid()) {
	        \Wyolution\Logger::error("Mailgun settings not properly configured.");
	        return false;
	    }
	    
	    if ($this->skipSendingMail) {
	        \Wyolution\Logger::info("Mailgun settings set to skip sending mail.");
	        \Wyolution\Logger::email($to, $subject, $messageHtml);
	        return true;
	    }
	    
	    $mailgunUrl = $this->mailgunUrl;
	    $mailgunKey = $this->mailgunKey;
	    
	    /*
	     * Specifications for API can be found here:
	     * https://documentation.mailgun.com/en/latest/api-sending.html#sending
	     */
	    
	    $postData = [
	            'from'=> $this->defaultFromEmail,
            'to'=>$to,
            'subject'=>$subject,
            'h:Reply-To'=>$this->emailReplyTo
	    ];
	    if (!empty($messageHtml)) {
	        $postData['html'] = $messageHtml;
	    }
	    if (!empty($message)) {
	        $postData['text'] = $message;
	    }
	    $session = curl_init($mailgunUrl.'/messages');
	    curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    curl_setopt($session, CURLOPT_USERPWD, 'api:'.$mailgunKey);
	    curl_setopt($session, CURLOPT_POST, true);
	    curl_setopt($session, CURLOPT_POSTFIELDS, $postData);
	    curl_setopt($session, CURLOPT_HEADER, false);
	    curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
	    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 5);
	    curl_setopt($session, CURLOPT_TIMEOUT, 10);
	    $response = curl_exec($session);
	    curl_close($session);
	    $results = json_decode($response, true);
	    if (isset($results['message']) && strpos($results['message'],'Queued') !== false) {
	        \Wyolution\Logger::info("Mail successfully sent.");
	        return true;
	    }
	    else {
	        \Wyolution\Logger::error("Unable to send email.  Mailgun Error:\n" . print_r($results,true));
	        return false;
	    }
	}

}
