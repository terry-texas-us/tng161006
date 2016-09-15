<?php

class PHPMailer
{
  public $Version = '5.2.10';
  
  public $Priority = 3;
  
  public $CharSet = '';
  
  public $ContentType = 'text/plain';
  
  public $Encoding = '8bit';
  
  public $ErrorInfo = '';
  
  public $From = 'root@localhost';
  
  public $FromName = 'Root User';
  
  public $Sender = '';
  
  public $ReturnPath = '';
  
  public $Subject = '';
  
  public $Body = '';
  
  public $AltBody = '';
  
  public $Ical = '';
  
  protected $MIMEBody = '';
  
  protected $MIMEHeader = '';
  
  protected $mailHeader = '';
  
  public $WordWrap = 0;
 
  public $Mailer = 'mail';
  
  public $Sendmail = '/usr/sbin/sendmail';
  
  public $UseSendmailOptions = true;
  
  public $PluginDir = '';
  
  public $ConfirmReadingTo = '';
  
  public $Hostname = '';
  
  public $MessageID = '';
  
  public $MessageDate = '';
  
  public $Host = 'localhost';
  
  public $Port = 25;
  
  public $Helo = '';
  
  public $SMTPSecure = '';
  
  public $SMTPAutoTLS = true;
  
  public $SMTPAuth = false;
  
  public $SMTPOptions = [];
  
  public $Username = '';
  
  public $Password = '';
  
  public $AuthType = '';
  
  public $Realm = '';
  
  public $Workstation = '';
  
  public $Timeout = 300;
  
  public $SMTPDebug = 0;
  
  public $Debugoutput = 'echo';
  
  public $SMTPKeepAlive = false;
  
  public $SingleTo = false;
  
  public $SingleToArray = [];
  
  public $do_verp = false;
  
  public $AllowEmpty = false;
  
  public $LE = "\n";
  
  public $DKIM_selector = '';
  
  public $DKIM_identity = '';
  
  public $DKIM_passphrase = '';
  
  public $DKIM_domain = '';
 
  public $DKIM_private = '';
  
  public $action_function = '';
  
  public $XMailer = '';
  
  protected $smtp = null;
  
  protected $to = [];
  
  protected $cc = [];
  
  protected $bcc = [];
  
  protected $ReplyTo = [];
  
  protected $all_recipients = [];
  
  protected $attachment = [];
  
  protected $CustomHeader = [];
  
  protected $lastMessageID = '';
  
  protected $message_type = '';
  
  protected $boundary = [];
  
  protected $language = [];
  
  protected $error_count = 0;
  
  protected $sign_cert_file = '';
  
  protected $sign_key_file = '';
  
  protected $sign_extracerts_file = '';
  
  protected $sign_key_pass = '';
  
  protected $exceptions = false;
  
  protected $uniqueid = '';
  
  const STOP_MESSAGE = 0;
  
  const STOP_CONTINUE = 1;
  
  const STOP_CRITICAL = 2;
  
  const CRLF = "\r\n";
  
  const MAX_LINE_LENGTH = 998;

  
  public function __construct($options = false, $exceptions = false) {
    if ($options && isset($options->charset)) {
      $this->CharSet = $options->charset;
    } else {
      $this->CharSet = 'iso-8859-1';
    }
    $this->exceptions = (boolean)$exceptions;
  }

  
  public function __destruct() {
    //Close any open SMTP connection nicely
    if ($this->Mailer == 'smtp') {
      $this->smtpClose();
    }
  }

  
  private function mailPassthru($to, $subject, $body, $header, $params) {
    //Check overloading of mail function to avoid double-encoding
    if (ini_get('mbstring.func_overload') & 1) {
      $subject = $this->secureHeader($subject);
    } else {
      $subject = $this->encodeHeader($this->secureHeader($subject));
    }
    if (ini_get('safe_mode') || !($this->UseSendmailOptions)) {
      $result = mail($to, $subject, $body, $header);
    } else {
      $result = mail($to, $subject, $body, $header, $params);
    }
    return $result;
  }

 
  protected function edebug($str) {
    if ($this->SMTPDebug <= 0) {
      return;
    }
    //Avoid clash with built-in function names
    if (!in_array($this->Debugoutput, ['error_log', 'html', 'echo']) and is_callable($this->Debugoutput)) {
      call_user_func($this->Debugoutput, $str, $this->SMTPDebug);
      return;
    }
    switch ($this->Debugoutput) {
      case 'error_log':
        //Don't output, just log
        error_log($str);
        break;
      case 'html':
        //Cleans up output a bit for a better looking, HTML-safe output
        echo htmlentities(preg_replace('/[\r\n]+/', '', $str), ENT_QUOTES, 'UTF-8') . "<br>\n";
        break;
      case 'echo':
      default:
        //Normalize line breaks
        $str = preg_replace('/(\r\n|\r|\n)/ms', "\n", $str);
        echo gmdate('Y-m-d H:i:s') . "\t" . str_replace("\n", "\n                   \t                  ", trim($str)) . "\n";
    }
  }

  
  public function isHTML($isHtml = true) {
    if ($isHtml) {
      $this->ContentType = 'text/html';
    } else {
      $this->ContentType = 'text/plain';
    }
  }

  
  public function isSMTP() {
    $this->Mailer = 'smtp';
  }

  
  public function isMail() {
    $this->Mailer = 'mail';
  }

  
  public function isSendmail() {
    $ini_sendmail_path = ini_get('sendmail_path');
    if (!stristr($ini_sendmail_path, 'sendmail')) {
      $this->Sendmail = '/usr/sbin/sendmail';
    } else {
      $this->Sendmail = $ini_sendmail_path;
    }
    $this->Mailer = 'sendmail';
  }

  
  public function isQmail() {
    $ini_sendmail_path = ini_get('sendmail_path');
    if (!stristr($ini_sendmail_path, 'qmail')) {
      $this->Sendmail = '/var/qmail/bin/qmail-inject';
    } else {
      $this->Sendmail = $ini_sendmail_path;
    }
    $this->Mailer = 'qmail';
  }

 
  public function addAddress($address, $name = '') {
    return $this->addAnAddress('to', $address, $name);
  }

  
  public function addCC($address, $name = '') {
    return $this->addAnAddress('cc', $address, $name);
  }

  
  public function addBCC($address, $name = '') {
    return $this->addAnAddress('bcc', $address, $name);
  }

  
  public function addReplyTo($address, $name = '') {
    return $this->addAnAddress('Reply-To', $address, $name);
  }

  
  protected function addAnAddress($kind, $address, $name = '') {
    if (!preg_match('/^(to|cc|bcc|Reply-To)$/', $kind)) {
      $this->setError($this->lang('Invalid recipient array') . ': ' . $kind);
      $this->edebug($this->lang('Invalid recipient array') . ': ' . $kind);
      if ($this->exceptions) {
        throw new phpmailerException('Invalid recipient array: ' . $kind);
      }
      return false;
    }
    $address = trim($address);
    $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
    if (!$this->validateAddress($address)) {
      $this->setError($this->lang('invalid_address') . ': ' . $address);
      $this->edebug($this->lang('invalid_address') . ': ' . $address);
      if ($this->exceptions) {
        throw new phpmailerException($this->lang('invalid_address') . ': ' . $address);
      }
      return false;
    }
    if ($kind != 'Reply-To') {
      if (!isset($this->all_recipients[strtolower($address)])) {
        array_push($this->$kind, [$address, $name]);
        $this->all_recipients[strtolower($address)] = true;
        return true;
      }
    } else {
      if (!array_key_exists(strtolower($address), $this->ReplyTo)) {
        $this->ReplyTo[strtolower($address)] = [$address, $name];
        return true;
      }
    }
    return false;
  }

  
  public function parseAddresses($addrstr, $useimap = true) {
    $addresses = [];
    if ($useimap and function_exists('imap_rfc822_parse_adrlist')) {
      //Use this built-in parser if it's available
      $list = imap_rfc822_parse_adrlist($addrstr, '');
      foreach ($list as $address) {
        if ($address->host != '.SYNTAX-ERROR.') {
          if ($this->validateAddress($address->mailbox . '@' . $address->host)) {
            $addresses[] = ['name' => (property_exists($address, 'personal') ? $address->personal : ''), 'address' => $address->mailbox . '@' . $address->host];
          }
        }
      }
    } else {
      //Use this simpler parser
      $list = explode(',', $addrstr);
      foreach ($list as $address) {
        $address = trim($address);
        //Is there a separate name part?
        if (strpos($address, '<') === false) {
          //No separate name, just use the whole thing
          if ($this->validateAddress($address)) {
            $addresses[] = ['name' => '', 'address' => $address];
          }
        } else {
          list($name, $email) = explode('<', $address);
          $email = trim(str_replace('>', '', $email));
          if ($this->validateAddress($email)) {
            $addresses[] = ['name' => trim(str_replace(['"', "'"], '', $name)), 'address' => $email];
          }
        }
      }
    }
    return $addresses;
  }

  
  public function setFrom($address, $name = '', $auto = true) {
    $address = trim($address);
    $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
    if (!$this->validateAddress($address)) {
      $this->setError($this->lang('invalid_address') . ': ' . $address);
      $this->edebug($this->lang('invalid_address') . ': ' . $address);
      if ($this->exceptions) {
        throw new phpmailerException($this->lang('invalid_address') . ': ' . $address);
      }
      return false;
    }
    $this->From = $address;
    $this->FromName = $name;
    if ($auto) {
      if (empty($this->Sender)) {
        $this->Sender = $address;
      }
    }
    return true;
  }

  
  public function getLastMessageID() {
    return $this->lastMessageID;
  }

  
  public static function validateAddress($address, $patternselect = 'auto') {
    if (!$patternselect or $patternselect == 'auto') {
      //Check this constant first so it works when extension_loaded() is disabled by safe mode
      //Constant was added in PHP 5.2.4
      if (defined('PCRE_VERSION')) {
        //This pattern can get stuck in a recursive loop in PCRE <= 8.0.2
        if (version_compare(PCRE_VERSION, '8.0.3') >= 0) {
          $patternselect = 'pcre8';
        } else {
          $patternselect = 'pcre';
        }
      } elseif (function_exists('extension_loaded') and extension_loaded('pcre')) {
        //Fall back to older PCRE
        $patternselect = 'pcre';
      } else {
        //Filter_var appeared in PHP 5.2.0 and does not require the PCRE extension
        if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
          $patternselect = 'php';
        } else {
          $patternselect = 'noregex';
        }
      }
    }
    switch ($patternselect) {
      case 'pcre8':
        
        return (boolean)preg_match(
            '/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)' .
            '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' .
            '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' .
            '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
            '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
            '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
            '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' .
            '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
            '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD',
            $address
        );
      case 'pcre':
        //An older regex that doesn't need a recent PCRE
        return (boolean)preg_match(
            '/^(?!(?>"?(?>\\\[ -~]|[^"])"?){255,})(?!(?>"?(?>\\\[ -~]|[^"])"?){65,}@)(?>' .
            '[!#-\'*+\/-9=?^-~-]+|"(?>(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\xFF]))*")' .
            '(?>\.(?>[!#-\'*+\/-9=?^-~-]+|"(?>(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\xFF]))*"))*' .
            '@(?>(?![a-z0-9-]{64,})(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)(?>\.(?![a-z0-9-]{64,})' .
            '(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)){0,126}|\[(?:(?>IPv6:(?>(?>[a-f0-9]{1,4})(?>:' .
            '[a-f0-9]{1,4}){7}|(?!(?:.*[a-f0-9][:\]]){8,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?' .
            '::(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?))|(?>(?>IPv6:(?>[a-f0-9]{1,4}(?>:' .
            '[a-f0-9]{1,4}){5}:|(?!(?:.*[a-f0-9]:){6,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4})?' .
            '::(?>(?:[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4}):)?))?(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
            '|[1-9]?[0-9])(?>\.(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}))\])$/isD',
            $address
        );
      case 'html5':
        return (boolean)preg_match(
            '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
            '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD',
            $address
        );
      case 'noregex':
        //No PCRE! Do something _very_ approximate!
        //Check the address is 3 chars or longer and contains an @ that's not the first or last char
        return (strlen($address) >= 3
                and strpos($address, '@') >= 1
                and strpos($address, '@') != strlen($address) - 1);
      case 'php':
      default:
        return (boolean)filter_var($address, FILTER_VALIDATE_EMAIL);
    }
  }

  
  public function send() {
    try {
      if (!$this->preSend()) {
        return false;
      }
      return $this->postSend();
    } catch (phpmailerException $exc) {
      $this->mailHeader = '';
      $this->setError($exc->getMessage());
      if ($this->exceptions) {
        throw $exc;
      }
      return false;
    }
  }

  
  public function preSend() {
    try {
      $this->mailHeader = '';
      if ((count($this->to) + count($this->cc) + count($this->bcc)) < 1) {
        throw new phpmailerException($this->lang('provide_address'), self::STOP_CRITICAL);
      }
      // Set whether the message is multipart/alternative
      if (!empty($this->AltBody)) {
        $this->ContentType = 'multipart/alternative';
      }
      $this->error_count = 0; // Reset errors
      $this->setMessageType();
      // Refuse to send an empty message unless we are specifically allowing it
      if (!$this->AllowEmpty and empty($this->Body)) {
        throw new phpmailerException($this->lang('empty_message'), self::STOP_CRITICAL);
      }
      // Create body before headers in case body makes changes to headers (e.g. altering transfer encoding)
      $this->MIMEHeader = '';
      $this->MIMEBody = $this->createBody();
      // createBody may have added some headers, so retain them
      $tempheaders = $this->MIMEHeader;
      $this->MIMEHeader = $this->createHeader();
      $this->MIMEHeader .= $tempheaders;
      // To capture the complete message when using mail(), create
      // an extra header list which createHeader() doesn't fold in
      if ($this->Mailer == 'mail') {
        if (count($this->to) > 0) {
          $this->mailHeader .= $this->addrAppend('To', $this->to);
        } else {
          $this->mailHeader .= $this->headerLine('To', 'undisclosed-recipients:;');
        }
        $this->mailHeader .= $this->headerLine('Subject', $this->encodeHeader($this->secureHeader(trim($this->Subject))));
      }
      // Sign with DKIM if enabled
      if (!empty($this->DKIM_domain)
          && !empty($this->DKIM_private)
          && !empty($this->DKIM_selector)
          && file_exists($this->DKIM_private)
      ) {
        $header_dkim = $this->DKIM_Add(
            $this->MIMEHeader . $this->mailHeader,
            $this->encodeHeader($this->secureHeader($this->Subject)),
            $this->MIMEBody
        );
        $this->MIMEHeader = rtrim($this->MIMEHeader, "\r\n ") . self::CRLF .
                str_replace("\r\n", "\n", $header_dkim) . self::CRLF;
      }
      return true;
    } catch (phpmailerException $exc) {
      $this->setError($exc->getMessage());
      if ($this->exceptions) {
        throw $exc;
      }
      return false;
    }
  }

  
  public function postSend() {
    try {
      // Choose the mailer and send through it
      switch ($this->Mailer) {
        case 'sendmail':
        case 'qmail':
          return $this->sendmailSend($this->MIMEHeader, $this->MIMEBody);
        case 'smtp':
          return $this->smtpSend($this->MIMEHeader, $this->MIMEBody);
        case 'mail':
          return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
        default:
          $sendMethod = $this->Mailer . 'Send';
          if (method_exists($this, $sendMethod)) {
            return $this->$sendMethod($this->MIMEHeader, $this->MIMEBody);
          }
          return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
      }
    } catch (phpmailerException $exc) {
      $this->setError($exc->getMessage());
      $this->edebug($exc->getMessage());
      if ($this->exceptions) {
        throw $exc;
      }
    }
    return false;
  }

  
  protected function sendmailSend($header, $body) {
    if ($this->Sender != '') {
      if ($this->Mailer == 'qmail') {
        $sendmail = sprintf('%s -f%s', escapeshellcmd($this->Sendmail), escapeshellarg($this->Sender));
      } else {
        $sendmail = sprintf('%s -oi -f%s -t', escapeshellcmd($this->Sendmail), escapeshellarg($this->Sender));
      }
    } else {
      if ($this->Mailer == 'qmail') {
        $sendmail = sprintf('%s', escapeshellcmd($this->Sendmail));
      } else {
        $sendmail = sprintf('%s -oi -t', escapeshellcmd($this->Sendmail));
      }
    }
    if ($this->SingleTo) {
      foreach ($this->SingleToArray as $toAddr) {
        if (!$mail = popen($sendmail, 'w')) {
          throw new phpmailerException($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
        }
        fputs($mail, 'To: ' . $toAddr . "\n");
        fputs($mail, $header);
        fputs($mail, $body);
        $result = pclose($mail);
        $this->doCallback(($result == 0), [$toAddr], $this->cc, $this->bcc, $this->Subject, $body, $this->From);
        if ($result != 0) {
          throw new phpmailerException($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
        }
      }
    } else {
      if (!$mail = popen($sendmail, 'w')) {
        throw new phpmailerException($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
      }
      fputs($mail, $header);
      fputs($mail, $body);
      $result = pclose($mail);
      $this->doCallback(($result == 0), $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From);
      if ($result != 0) {
        throw new phpmailerException($this->lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
      }
    }
    return true;
  }

  
  protected function mailSend($header, $body) {
    $toArr = [];
    foreach ($this->to as $toaddr) {
      $toArr[] = $this->addrFormat($toaddr);
    }
    $to = implode(', ', $toArr);
    if (empty($this->Sender)) {
      $params = ' ';
    } else {
      $params = sprintf('-f%s', $this->Sender);
    }
    if ($this->Sender != '' and !ini_get('safe_mode')) {
      $old_from = ini_get('sendmail_from');
      ini_set('sendmail_from', $this->Sender);
    }
    $result = false;
    if ($this->SingleTo && count($toArr) > 1) {
      foreach ($toArr as $toAddr) {
        $result = $this->mailPassthru($toAddr, $this->Subject, $body, $header, $params);
        $this->doCallback($result, [$toAddr], $this->cc, $this->bcc, $this->Subject, $body, $this->From);
      }
    } else {
      $result = $this->mailPassthru($to, $this->Subject, $body, $header, $params);
      $this->doCallback($result, $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From);
    }
    if (isset($old_from)) {
      ini_set('sendmail_from', $old_from);
    }
    if (!$result) {
      throw new phpmailerException($this->lang('instantiate'), self::STOP_CRITICAL);
    }
    return true;
  }

  
  public function getSMTPInstance() {
    if (!is_object($this->smtp)) {
      $this->smtp = new SMTP;
    }
    return $this->smtp;
  }

  
  protected function smtpSend($header, $body) {
    $bad_rcpt = [];
    if (!$this->smtpConnect($this->SMTPOptions)) {
      throw new phpmailerException($this->lang('smtp_connect_failed'), self::STOP_CRITICAL);
    }
    if ('' == $this->Sender) {
      $smtp_from = $this->From;
    } else {
      $smtp_from = $this->Sender;
    }
    if (!$this->smtp->mail($smtp_from)) {
      $this->setError($this->lang('from_failed') . $smtp_from . ' : ' . implode(',', $this->smtp->getError()));
      throw new phpmailerException($this->ErrorInfo, self::STOP_CRITICAL);
    }
    // Attempt to send to all recipients
    foreach ([$this->to, $this->cc, $this->bcc] as $togroup) {
      foreach ($togroup as $to) {
        if (!$this->smtp->recipient($to[0])) {
          $error = $this->smtp->getError();
          $bad_rcpt[] = ['to' => $to[0], 'error' => $error['detail']];
          $isSent = false;
        } else {
          $isSent = true;
        }
        $this->doCallback($isSent, [$to[0]], [], [], $this->Subject, $body, $this->From);
      }
    }
    // Only send the DATA command if we have viable recipients
    if ((count($this->all_recipients) > count($bad_rcpt)) and !$this->smtp->data($header . $body)) {
      throw new phpmailerException($this->lang('data_not_accepted'), self::STOP_CRITICAL);
    }
    if ($this->SMTPKeepAlive) {
      $this->smtp->reset();
    } else {
      $this->smtp->quit();
      $this->smtp->close();
    }
    //Create error message for any bad addresses
    if (count($bad_rcpt) > 0) {
      $errstr = '';
      foreach ($bad_rcpt as $bad) {
        $errstr .= $bad['to'] . ': ' . $bad['error'];
      }
      throw new phpmailerException($this->lang('recipients_failed') . $errstr, self::STOP_CONTINUE);
    }
    return true;
  }

  
  public function smtpConnect($options = []) {
    if (is_null($this->smtp)) {
      $this->smtp = $this->getSMTPInstance();
    }
    // Already connected?
    if ($this->smtp->connected()) {
      return true;
    }
    $this->smtp->setTimeout($this->Timeout);
    $this->smtp->setDebugLevel($this->SMTPDebug);
    $this->smtp->setDebugOutput($this->Debugoutput);
    $this->smtp->setVerp($this->do_verp);
    $hosts = explode(';', $this->Host);
    $lastexception = null;
    foreach ($hosts as $hostentry) {
      $hostinfo = [];
      if (!preg_match('/^((ssl|tls):\/\/)*([a-zA-Z0-9\.-]*):?([0-9]*)$/', trim($hostentry), $hostinfo)) {
        // Not a valid host entry
        continue;
      }
      // $hostinfo[2]: optional ssl or tls prefix
      // $hostinfo[3]: the hostname
      // $hostinfo[4]: optional port number
      // The host string prefix can temporarily override the current setting for SMTPSecure
      // If it's not specified, the default value is used
      $prefix = '';
      $secure = $this->SMTPSecure;
      $tls = ($this->SMTPSecure == 'tls');
      if ('ssl' == $hostinfo[2] or ('' == $hostinfo[2] and 'ssl' == $this->SMTPSecure)) {
        $prefix = 'ssl://';
        $tls = false; // Can't have SSL and TLS at the same time
        $secure = 'ssl';
      } elseif ($hostinfo[2] == 'tls') {
        $tls = true;
        // tls doesn't use a prefix
        $secure = 'tls';
      }
      //Do we need the OpenSSL extension?
      $sslext = defined('OPENSSL_ALGO_SHA1');
      if ('tls' === $secure or 'ssl' === $secure) {
        //Check for an OpenSSL constant rather than using extension_loaded, which is sometimes disabled
        if (!$sslext) {
          throw new phpmailerException($this->lang('extension_missing') . 'openssl', self::STOP_CRITICAL);
        }
      }
      $host = $hostinfo[3];
      $port = $this->Port;
      $tport = (integer)$hostinfo[4];
      if ($tport > 0 and $tport < 65536) {
        $port = $tport;
      }
      if ($this->smtp->connect($prefix . $host, $port, $this->Timeout, $options)) {
        try {
          if ($this->Helo) {
            $hello = $this->Helo;
          } else {
            $hello = $this->serverHostname();
          }
          $this->smtp->hello($hello);
          //Automatically enable TLS encryption if:
          // * it's not disabled
          // * we have openssl extension
          // * we are not already using SSL
          // * the server offers STARTTLS
          if ($this->SMTPAutoTLS and $sslext and $secure != 'ssl' and $this->smtp->getServerExt('STARTTLS')) {
            $tls = true;
          }
          if ($tls) {
            if (!$this->smtp->startTLS()) {
              throw new phpmailerException($this->lang('connect_host'));
            }
            // We must resend HELO after tls negotiation
            $this->smtp->hello($hello);
          }
          if ($this->SMTPAuth) {
            if (!$this->smtp->authenticate($this->Username, $this->Password, $this->AuthType, $this->Realm, $this->Workstation)) {
              throw new phpmailerException($this->lang('authenticate'));
            }
          }
          return true;
        } catch (phpmailerException $exc) {
          $lastexception = $exc;
          $this->edebug($exc->getMessage());
          // We must have connected, but then failed TLS or Auth, so close connection nicely
          $this->smtp->quit();
        }
      }
    }
    // If we get here, all connection attempts have failed, so close connection hard
    $this->smtp->close();
    // As we've caught all exceptions, just report whatever the last one was
    if ($this->exceptions and !is_null($lastexception)) {
      throw $lastexception;
    }
    return false;
  }

  
  public function smtpClose() {
    if ($this->smtp !== null) {
      if ($this->smtp->connected()) {
        $this->smtp->quit();
        $this->smtp->close();
      }
    }
  }

  
  public function setLanguage($langcode = 'en', $lang_path = '') {
    // Define full set of translatable strings in English
    $PHPMAILER_LANG = [
            'authenticate' => 'SMTP Error: Could not authenticate.',
            'connect_host' => 'SMTP Error: Could not connect to SMTP host.',
            'data_not_accepted' => 'SMTP Error: data not accepted.',
            'empty_message' => 'Message body empty',
            'encoding' => 'Unknown encoding: ',
            'execute' => 'Could not execute: ',
            'file_access' => 'Could not access file: ',
            'file_open' => 'File Error: Could not open file: ',
            'from_failed' => 'The following From address failed: ',
            'instantiate' => 'Could not instantiate mail function.',
            'invalid_address' => 'Invalid address',
            'mailer_not_supported' => ' mailer is not supported.',
            'provide_address' => 'You must provide at least one recipient email address.',
            'recipients_failed' => 'SMTP Error: The following recipients failed: ',
            'signing' => 'Signing Error: ',
            'smtp_connect_failed' => 'SMTP connect() failed.',
            'smtp_error' => 'SMTP server error: ',
            'variable_set' => 'Cannot set or reset variable: ',
            'extension_missing' => 'Extension missing: '
    ];
    if (empty($lang_path)) {
      // Calculate an absolute path so it can work if CWD is not here
      $lang_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
    }
    $foundlang = true;
    $lang_file = $lang_path . 'phpmailer.lang-' . $langcode . '.php';
    // There is no English translation file
    if ($langcode != 'en') {
      // Make sure language file path is readable
      if (!is_readable($lang_file)) {
        $foundlang = false;
      } else {
        // Overwrite language-specific strings.
        // This way we'll never have missing translation keys.
        $foundlang = include $lang_file;
      }
    }
    $this->language = $PHPMAILER_LANG;
    return (boolean)$foundlang; // Returns false if language not found
  }

  public function getTranslations() {
    return $this->language;
  }

  
  public function addrAppend($type, $addr) {
    $addresses = [];
    foreach ($addr as $address) {
      $addresses[] = $this->addrFormat($address);
    }
    return $type . ': ' . implode(', ', $addresses) . $this->LE;
  }

  
  public function addrFormat($addr) {
    if (empty($addr[1])) { // No name provided
      return $this->secureHeader($addr[0]);
    } else {
      return $this->encodeHeader($this->secureHeader($addr[1]), 'phrase') . ' <' . $this->secureHeader($addr[0]) . '>';
    }
  }

  
  public function wrapText($message, $length, $qp_mode = false) {
    if ($qp_mode) {
      $soft_break = sprintf(' =%s', $this->LE);
    } else {
      $soft_break = $this->LE;
    }
    // If utf-8 encoding is used, we will need to make sure we don't
    // split multibyte characters when we wrap
    $is_utf8 = (strtolower($this->CharSet) == 'utf-8');
    $lelen = strlen($this->LE);
    $crlflen = strlen(self::CRLF);
    $message = $this->fixEOL($message);
    //Remove a trailing line break
    if (substr($message, -$lelen) == $this->LE) {
      $message = substr($message, 0, -$lelen);
    }
    //Split message into lines
    $lines = explode($this->LE, $message);
    //Message will be rebuilt in here
    $message = '';
    foreach ($lines as $line) {
      $words = explode(' ', $line);
      $buf = '';
      $firstword = true;
      foreach ($words as $word) {
        if ($qp_mode and (strlen($word) > $length)) {
          $space_left = $length - strlen($buf) - $crlflen;
          if (!$firstword) {
            if ($space_left > 20) {
              $len = $space_left;
              if ($is_utf8) {
                $len = $this->utf8CharBoundary($word, $len);
              } elseif (substr($word, $len - 1, 1) == '=') {
                $len--;
              } elseif (substr($word, $len - 2, 1) == '=') {
                $len -= 2;
              }
              $part = substr($word, 0, $len);
              $word = substr($word, $len);
              $buf .= ' ' . $part;
              $message .= $buf . sprintf('=%s', self::CRLF);
            } else {
              $message .= $buf . $soft_break;
            }
            $buf = '';
          }
          while (strlen($word) > 0) {
            if ($length <= 0) {
              break;
            }
            $len = $length;
            if ($is_utf8) {
              $len = $this->utf8CharBoundary($word, $len);
            } elseif (substr($word, $len - 1, 1) == '=') {
              $len--;
            } elseif (substr($word, $len - 2, 1) == '=') {
              $len -= 2;
            }
            $part = substr($word, 0, $len);
            $word = substr($word, $len);
            if (strlen($word) > 0) {
              $message .= $part . sprintf('=%s', self::CRLF);
            } else {
              $buf = $part;
            }
          }
        } else {
          $buf_o = $buf;
          if (!$firstword) {
            $buf .= ' ';
          }
          $buf .= $word;
          if (strlen($buf) > $length and $buf_o != '') {
            $message .= $buf_o . $soft_break;
            $buf = $word;
          }
        }
        $firstword = false;
      }
      $message .= $buf . self::CRLF;
    }
    return $message;
  }

  
  public function utf8CharBoundary($encodedText, $maxLength) {
    $foundSplitPos = false;
    $lookBack = 3;
    while (!$foundSplitPos) {
      $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
      $encodedCharPos = strpos($lastChunk, '=');
      if (false !== $encodedCharPos) {
        // Found start of encoded character byte within $lookBack block.
        // Check the encoded byte value (the 2 chars after the '=')
        $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
        $dec = hexdec($hex);
        if ($dec < 128) {
          // Single byte character.
          // If the encoded char was found at pos 0, it will fit
          // otherwise reduce maxLength to start of the encoded char
          if ($encodedCharPos > 0) {
            $maxLength = $maxLength - ($lookBack - $encodedCharPos);
          }
          $foundSplitPos = true;
        } elseif ($dec >= 192) {
          // First byte of a multi byte character
          // Reduce maxLength to split at start of character
          $maxLength = $maxLength - ($lookBack - $encodedCharPos);
          $foundSplitPos = true;
        } elseif ($dec < 192) {
          // Middle byte of a multi byte character, look further back
          $lookBack += 3;
        }
      } else {
        // No encoded character found
        $foundSplitPos = true;
      }
    }
    return $maxLength;
  }

  
  public function setWordWrap() {
    if ($this->WordWrap < 1) {
      return;
    }
    switch ($this->message_type) {
      case 'alt':
      case 'alt_inline':
      case 'alt_attach':
      case 'alt_inline_attach':
        $this->AltBody = $this->wrapText($this->AltBody, $this->WordWrap);
        break;
      default:
        $this->Body = $this->wrapText($this->Body, $this->WordWrap);
        break;
    }
  }

  
  public function createHeader() {
    $result = '';
    if ($this->MessageDate == '') {
      $this->MessageDate = self::rfcDate();
    }
    $result .= $this->headerLine('Date', $this->MessageDate);
    // To be created automatically by mail()
    if ($this->SingleTo) {
      if ($this->Mailer != 'mail') {
        foreach ($this->to as $toaddr) {
          $this->SingleToArray[] = $this->addrFormat($toaddr);
        }
      }
    } else {
      if (count($this->to) > 0) {
        if ($this->Mailer != 'mail') {
          $result .= $this->addrAppend('To', $this->to);
        }
      } elseif (count($this->cc) == 0) {
        $result .= $this->headerLine('To', 'undisclosed-recipients:;');
      }
    }
    $result .= $this->addrAppend('From', [[trim($this->From), $this->FromName]]);
    // sendmail and mail() extract Cc from the header before sending
    if (count($this->cc) > 0) {
      $result .= $this->addrAppend('Cc', $this->cc);
    }
    // sendmail and mail() extract Bcc from the header before sending
    if (($this->Mailer == 'sendmail' or $this->Mailer == 'qmail' or $this->Mailer == 'mail') and count($this->bcc) > 0) {
      $result .= $this->addrAppend('Bcc', $this->bcc);
    }
    if (count($this->ReplyTo) > 0) {
      $result .= $this->addrAppend('Reply-To', $this->ReplyTo);
    }
    // mail() sets the subject itself
    if ($this->Mailer != 'mail') {
      $result .= $this->headerLine('Subject', $this->encodeHeader($this->secureHeader($this->Subject)));
    }
    if ($this->MessageID != '') {
      $this->lastMessageID = $this->MessageID;
    } else {
      $this->lastMessageID = sprintf('<%s@%s>', $this->uniqueid, $this->ServerHostname());
    }
    $result .= $this->headerLine('Message-ID', $this->lastMessageID);
    $result .= $this->headerLine('X-Priority', $this->Priority);
    if ($this->XMailer == '') {
      $result .= $this->headerLine('X-Mailer', 'PHPMailer ' . $this->Version . ' (https://github.com/PHPMailer/PHPMailer/)');
    } else {
      $myXmailer = trim($this->XMailer);
      if ($myXmailer) {
        $result .= $this->headerLine('X-Mailer', $myXmailer);
      }
    }
    if ($this->ConfirmReadingTo != '') {
      $result .= $this->headerLine('Disposition-Notification-To', '<' . trim($this->ConfirmReadingTo) . '>');
    }
    // Add custom headers
    foreach ($this->CustomHeader as $header) {
      $result .= $this->headerLine(trim($header[0]), $this->encodeHeader(trim($header[1])));
    }
    if (!$this->sign_key_file) {
      $result .= $this->headerLine('MIME-Version', '1.0');
      $result .= $this->getMailMIME();
    }
    return $result;
  }

  
  public function getMailMIME() {
    $result = '';
    $ismultipart = true;
    switch ($this->message_type) {
      case 'inline':
        $result .= $this->headerLine('Content-Type', 'multipart/related;');
        $result .= $this->textLine("\tboundary=\"" . $this->boundary[1] . '"');
        break;
      case 'attach':
      case 'inline_attach':
      case 'alt_attach':
      case 'alt_inline_attach':
        $result .= $this->headerLine('Content-Type', 'multipart/mixed;');
        $result .= $this->textLine("\tboundary=\"" . $this->boundary[1] . '"');
        break;
      case 'alt':
      case 'alt_inline':
        $result .= $this->headerLine('Content-Type', 'multipart/alternative;');
        $result .= $this->textLine("\tboundary=\"" . $this->boundary[1] . '"');
        break;
      default:
        // Catches case 'plain': and case '':
        $result .= $this->textLine('Content-Type: ' . $this->ContentType . '; charset=' . $this->CharSet);
        $ismultipart = false;
        break;
    }
    // RFC1341 part 5 says 7bit is assumed if not specified
    if ($this->Encoding != '7bit') {
      // RFC 2045 section 6.4 says multipart MIME parts may only use 7bit, 8bit or binary CTE
      if ($ismultipart) {
        if ($this->Encoding == '8bit') {
          $result .= $this->headerLine('Content-Transfer-Encoding', '8bit');
        }
        // The only remaining alternatives are quoted-printable and base64, which are both 7bit compatible
      } else {
        $result .= $this->headerLine('Content-Transfer-Encoding', $this->Encoding);
      }
    }
    if ($this->Mailer != 'mail') {
      $result .= $this->LE;
    }
    return $result;
  }

  
  public function getSentMIMEMessage() {
    return $this->MIMEHeader . $this->mailHeader . self::CRLF . $this->MIMEBody;
  }

  
  public function createBody() {
    $body = '';
    //Create unique IDs and preset boundaries
    $this->uniqueid = md5(uniqid(time()));
    $this->boundary[1] = 'b1_' . $this->uniqueid;
    $this->boundary[2] = 'b2_' . $this->uniqueid;
    $this->boundary[3] = 'b3_' . $this->uniqueid;
    if ($this->sign_key_file) {
      $body .= $this->getMailMIME() . $this->LE;
    }
    $this->setWordWrap();
    $bodyEncoding = $this->Encoding;
    $bodyCharSet = $this->CharSet;
    //Can we do a 7-bit downgrade?
    if ($bodyEncoding == '8bit' and !$this->has8bitChars($this->Body)) {
      $bodyEncoding = '7bit';
      $bodyCharSet = 'us-ascii';
    }
    //If lines are too long, and we're not already using an encoding that will shorten them,
    //change to quoted-printable transfer encoding
    if ('base64' != $this->Encoding and self::hasLineLongerThanMax($this->Body)) {
      $this->Encoding = 'quoted-printable';
      $bodyEncoding = 'quoted-printable';
    }
    $altBodyEncoding = $this->Encoding;
    $altBodyCharSet = $this->CharSet;
    //Can we do a 7-bit downgrade?
    if ($altBodyEncoding == '8bit' and !$this->has8bitChars($this->AltBody)) {
      $altBodyEncoding = '7bit';
      $altBodyCharSet = 'us-ascii';
    }
    //If lines are too long, change to quoted-printable transfer encoding
    if (self::hasLineLongerThanMax($this->AltBody)) {
      $altBodyEncoding = 'quoted-printable';
    }
    //Use this as a preamble in all multipart message types
    $mimepre = 'This is a multi-part message in MIME format.' . $this->LE . $this->LE;
    switch ($this->message_type) {
      case 'inline':
        $body .= $mimepre;
        $body .= $this->getBoundary($this->boundary[1], $bodyCharSet, '', $bodyEncoding);
        $body .= $this->encodeString($this->Body, $bodyEncoding);
        $body .= $this->LE . $this->LE;
        $body .= $this->attachAll('inline', $this->boundary[1]);
        break;
      case 'attach':
        $body .= $mimepre;
        $body .= $this->getBoundary($this->boundary[1], $bodyCharSet, '', $bodyEncoding);
        $body .= $this->encodeString($this->Body, $bodyEncoding);
        $body .= $this->LE . $this->LE;
        $body .= $this->attachAll('attachment', $this->boundary[1]);
        break;
      case 'inline_attach':
        $body .= $mimepre;
        $body .= $this->textLine('--' . $this->boundary[1]);
        $body .= $this->headerLine('Content-Type', 'multipart/related;');
        $body .= $this->textLine("\tboundary=\"" . $this->boundary[2] . '"');
        $body .= $this->LE;
        $body .= $this->getBoundary($this->boundary[2], $bodyCharSet, '', $bodyEncoding);
        $body .= $this->encodeString($this->Body, $bodyEncoding);
        $body .= $this->LE . $this->LE;
        $body .= $this->attachAll('inline', $this->boundary[2]);
        $body .= $this->LE;
        $body .= $this->attachAll('attachment', $this->boundary[1]);
        break;
      case 'alt':
        $body .= $mimepre;
        $body .= $this->getBoundary($this->boundary[1], $altBodyCharSet, 'text/plain', $altBodyEncoding);
        $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
        $body .= $this->LE . $this->LE;
        $body .= $this->getBoundary($this->boundary[1], $bodyCharSet, 'text/html', $bodyEncoding);
        $body .= $this->encodeString($this->Body, $bodyEncoding);
        $body .= $this->LE . $this->LE;
        if (!empty($this->Ical)) {
          $body .= $this->getBoundary($this->boundary[1], '', 'text/calendar; method=REQUEST', '');
          $body .= $this->encodeString($this->Ical, $this->Encoding);
          $body .= $this->LE . $this->LE;
        }
        $body .= $this->endBoundary($this->boundary[1]);
        break;
      case 'alt_inline':
        $body .= $mimepre;
        $body .= $this->getBoundary($this->boundary[1], $altBodyCharSet, 'text/plain', $altBodyEncoding);
        $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
        $body .= $this->LE . $this->LE;
        $body .= $this->textLine('--' . $this->boundary[1]);
        $body .= $this->headerLine('Content-Type', 'multipart/related;');
        $body .= $this->textLine("\tboundary=\"" . $this->boundary[2] . '"');
        $body .= $this->LE;
        $body .= $this->getBoundary($this->boundary[2], $bodyCharSet, 'text/html', $bodyEncoding);
        $body .= $this->encodeString($this->Body, $bodyEncoding);
        $body .= $this->LE . $this->LE;
        $body .= $this->attachAll('inline', $this->boundary[2]);
        $body .= $this->LE;
        $body .= $this->endBoundary($this->boundary[1]);
        break;
      case 'alt_attach':
        $body .= $mimepre;
        $body .= $this->textLine('--' . $this->boundary[1]);
        $body .= $this->headerLine('Content-Type', 'multipart/alternative;');
        $body .= $this->textLine("\tboundary=\"" . $this->boundary[2] . '"');
        $body .= $this->LE;
        $body .= $this->getBoundary($this->boundary[2], $altBodyCharSet, 'text/plain', $altBodyEncoding);
        $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
        $body .= $this->LE . $this->LE;
        $body .= $this->getBoundary($this->boundary[2], $bodyCharSet, 'text/html', $bodyEncoding);
        $body .= $this->encodeString($this->Body, $bodyEncoding);
        $body .= $this->LE . $this->LE;
        $body .= $this->endBoundary($this->boundary[2]);
        $body .= $this->LE;
        $body .= $this->attachAll('attachment', $this->boundary[1]);
        break;
      case 'alt_inline_attach':
        $body .= $mimepre;
        $body .= $this->textLine('--' . $this->boundary[1]);
        $body .= $this->headerLine('Content-Type', 'multipart/alternative;');
        $body .= $this->textLine("\tboundary=\"" . $this->boundary[2] . '"');
        $body .= $this->LE;
        $body .= $this->getBoundary($this->boundary[2], $altBodyCharSet, 'text/plain', $altBodyEncoding);
        $body .= $this->encodeString($this->AltBody, $altBodyEncoding);
        $body .= $this->LE . $this->LE;
        $body .= $this->textLine('--' . $this->boundary[2]);
        $body .= $this->headerLine('Content-Type', 'multipart/related;');
        $body .= $this->textLine("\tboundary=\"" . $this->boundary[3] . '"');
        $body .= $this->LE;
        $body .= $this->getBoundary($this->boundary[3], $bodyCharSet, 'text/html', $bodyEncoding);
        $body .= $this->encodeString($this->Body, $bodyEncoding);
        $body .= $this->LE . $this->LE;
        $body .= $this->attachAll('inline', $this->boundary[3]);
        $body .= $this->LE;
        $body .= $this->endBoundary($this->boundary[2]);
        $body .= $this->LE;
        $body .= $this->attachAll('attachment', $this->boundary[1]);
        break;
      default:
        // catch case 'plain' and case ''
        $body .= $this->encodeString($this->Body, $bodyEncoding);
        break;
    }
    if ($this->isError()) {
      $body = '';
    } elseif ($this->sign_key_file) {
      try {
        if (!defined('PKCS7_TEXT')) {
          throw new phpmailerException($this->lang('extension_missing') . 'openssl');
        }
        // @TODO would be nice to use php://temp streams here, but need to wrap for PHP < 5.1
        $file = tempnam(sys_get_temp_dir(), 'mail');
        if (false === file_put_contents($file, $body)) {
          throw new phpmailerException($this->lang('signing') . ' Could not write temp file');
        }
        $signed = tempnam(sys_get_temp_dir(), 'signed');
        //Workaround for PHP bug https://bugs.php.net/bug.php?id=69197
        if (empty($this->sign_extracerts_file)) {
          $sign = openssl_pkcs7_sign($file, $signed, 'file://' . realpath($this->sign_cert_file), ['file://' . realpath($this->sign_key_file), $this->sign_key_pass], null);
        } else {
          $sign = openssl_pkcs7_sign($file, $signed, 'file://' . realpath($this->sign_cert_file), ['file://' . realpath($this->sign_key_file), $this->sign_key_pass], null, PKCS7_DETACHED, $this->sign_extracerts_file);
        }
        if ($sign) {
          unlink($file);
          $body = file_get_contents($signed);
          unlink($signed);
          //The message returned by openssl contains both headers and body, so need to split them up
          $parts = explode("\n\n", $body, 2);
          $this->MIMEHeader .= $parts[0] . $this->LE . $this->LE;
          $body = $parts[1];
        } else {
          unlink($file);
          unlink($signed);
          throw new phpmailerException($this->lang('signing') . openssl_error_string());
        }
      } catch (phpmailerException $exc) {
        $body = '';
        if ($this->exceptions) {
          throw $exc;
        }
      }
    }
    return $body;
  }

  
  protected function getBoundary($boundary, $charSet, $contentType, $encoding) {
    $result = '';
    if ($charSet == '') {
      $charSet = $this->CharSet;
    }
    if ($contentType == '') {
      $contentType = $this->ContentType;
    }
    if ($encoding == '') {
      $encoding = $this->Encoding;
    }
    $result .= $this->textLine('--' . $boundary);
    $result .= sprintf('Content-Type: %s; charset=%s', $contentType, $charSet);
    $result .= $this->LE;
    // RFC1341 part 5 says 7bit is assumed if not specified
    if ($encoding != '7bit') {
      $result .= $this->headerLine('Content-Transfer-Encoding', $encoding);
    }
    $result .= $this->LE;
    return $result;
  }

  
  protected function endBoundary($boundary) {
    return $this->LE . '--' . $boundary . '--' . $this->LE;
  }

  
  protected function setMessageType() {
    $type = [];
    if ($this->alternativeExists()) {
      $type[] = 'alt';
    }
    if ($this->inlineImageExists()) {
      $type[] = 'inline';
    }
    if ($this->attachmentExists()) {
      $type[] = 'attach';
    }
    $this->message_type = implode('_', $type);
    if ($this->message_type == '') {
      $this->message_type = 'plain';
    }
  }

  
  public function headerLine($name, $value) {
    return $name . ': ' . $value . $this->LE;
  }

  
  public function textLine($value) {
    return $value . $this->LE;
  }

  
  public function addAttachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment') {
    try {
      if (!is_file($path)) {
        throw new phpmailerException($this->lang('file_access') . $path, self::STOP_CONTINUE);
      }
      // If a MIME type is not specified, try to work it out from the file name
      if ($type == '') {
        $type = self::filenameToType($path);
      }
      $filename = basename($path);
      if ($name == '') {
        $name = $filename;
      }
      $this->attachment[] = [
              0 => $path,
              1 => $filename,
              2 => $name,
              3 => $encoding,
              4 => $type,
              5 => false, // isStringAttachment
              6 => $disposition,
              7 => 0
      ];
    } catch (phpmailerException $exc) {
      $this->setError($exc->getMessage());
      $this->edebug($exc->getMessage());
      if ($this->exceptions) {
        throw $exc;
      }
      return false;
    }
    return true;
  }

  
  public function getAttachments() {
    return $this->attachment;
  }

  
  protected function attachAll($disposition_type, $boundary) {
    // Return text of body
    $mime = [];
    $cidUniq = [];
    $incl = [];
    // Add all attachments
    foreach ($this->attachment as $attachment) {
      // Check if it is a valid disposition_filter
      if ($attachment[6] == $disposition_type) {
        // Check for string attachment
        $string = '';
        $path = '';
        $bString = $attachment[5];
        if ($bString) {
          $string = $attachment[0];
        } else {
          $path = $attachment[0];
        }
        $inclhash = md5(serialize($attachment));
        if (in_array($inclhash, $incl)) {
          continue;
        }
        $incl[] = $inclhash;
        $name = $attachment[2];
        $encoding = $attachment[3];
        $type = $attachment[4];
        $disposition = $attachment[6];
        $cid = $attachment[7];
        if ($disposition == 'inline' && isset($cidUniq[$cid])) {
          continue;
        }
        $cidUniq[$cid] = true;
        $mime[] = sprintf('--%s%s', $boundary, $this->LE);
        $mime[] = sprintf('Content-Type: %s; name="%s"%s', $type, $this->encodeHeader($this->secureHeader($name)), $this->LE);
        // RFC1341 part 5 says 7bit is assumed if not specified
        if ($encoding != '7bit') {
          $mime[] = sprintf('Content-Transfer-Encoding: %s%s', $encoding, $this->LE);
        }
        if ($disposition == 'inline') {
          $mime[] = sprintf('Content-ID: <%s>%s', $cid, $this->LE);
        }
        // If a filename contains any of these chars, it should be quoted,
        // but not otherwise: RFC2183 & RFC2045 5.1
        // Fixes a warning in IETF's msglint MIME checker
        // Allow for bypassing the Content-Disposition header totally
        if (!(empty($disposition))) {
          $encoded_name = $this->encodeHeader($this->secureHeader($name));
          if (preg_match('/[ \(\)<>@,;:\\"\/\[\]\?=]/', $encoded_name)) {
            $mime[] = sprintf('Content-Disposition: %s; filename="%s"%s', $disposition, $encoded_name, $this->LE . $this->LE);
          } else {
            $mime[] = sprintf('Content-Disposition: %s; filename=%s%s', $disposition, $encoded_name, $this->LE . $this->LE);
          }
        } else {
          $mime[] = $this->LE;
        }
        // Encode as string attachment
        if ($bString) {
          $mime[] = $this->encodeString($string, $encoding);
          if ($this->isError()) {
            return '';
          }
          $mime[] = $this->LE . $this->LE;
        } else {
          $mime[] = $this->encodeFile($path, $encoding);
          if ($this->isError()) {
            return '';
          }
          $mime[] = $this->LE . $this->LE;
        }
      }
    }
    $mime[] = sprintf('--%s--%s', $boundary, $this->LE);
    return implode('', $mime);
  }

  
  protected function encodeFile($path, $encoding = 'base64') {
    try {
      if (!is_readable($path)) {
        throw new phpmailerException($this->lang('file_open') . $path, self::STOP_CONTINUE);
      }
      $file_buffer = file_get_contents($path);
      $file_buffer = $this->encodeString($file_buffer, $encoding);
      return $file_buffer;
    } catch (Exception $exc) {
      $this->setError($exc->getMessage());
      return '';
    }
  }

  
  public function encodeString($str, $encoding = 'base64') {
    $encoded = '';
    switch (strtolower($encoding)) {
      case 'base64':
        $encoded = chunk_split(base64_encode($str), 76, $this->LE);
        break;
      case '7bit':
      case '8bit':
        $encoded = $this->fixEOL($str);
        // Make sure it ends with a line break
        if (substr($encoded, -(strlen($this->LE))) != $this->LE) {
          $encoded .= $this->LE;
        }
        break;
      case 'binary':
        $encoded = $str;
        break;
      case 'quoted-printable':
        $encoded = $this->encodeQP($str);
        break;
      default:
        $this->setError($this->lang('encoding') . $encoding);
        break;
    }
    return $encoded;
  }

  
  public function encodeHeader($str, $position = 'text') {
    $matchcount = 0;
    switch (strtolower($position)) {
      case 'phrase':
        if (!preg_match('/[\200-\377]/', $str)) {
          // Can't use addslashes as we don't know the value of magic_quotes_sybase
          $encoded = addcslashes($str, "\0..\37\177\\\"");
          if (($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
            return ($encoded);
          } else {
            return ("\"$encoded\"");
          }
        }
        $matchcount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
        break;
      case 'comment':
        $matchcount = preg_match_all('/[()"]/', $str, $matches);
        // Intentional fall-through
      case 'text':
      default:
        $matchcount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
        break;
    }
    //There are no chars that need encoding
    if ($matchcount == 0) {
      return ($str);
    }
    $maxlen = 75 - 7 - strlen($this->CharSet);
    // Try to select the encoding which should produce the shortest output
    if ($matchcount > strlen($str) / 3) {
      // More than a third of the content will need encoding, so B encoding will be most efficient
      $encoding = 'B';
      if (function_exists('mb_strlen') && $this->hasMultiBytes($str)) {
        // Use a custom function which correctly encodes and wraps long
        // multibyte strings without breaking lines within a character
        $encoded = $this->base64EncodeWrapMB($str, "\n");
      } else {
        $encoded = base64_encode($str);
        $maxlen -= $maxlen % 4;
        $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
      }
    } else {
      $encoding = 'Q';
      $encoded = $this->encodeQ($str, $position);
      $encoded = $this->wrapText($encoded, $maxlen, true);
      $encoded = str_replace('=' . self::CRLF, "\n", trim($encoded));
    }
    $encoded = preg_replace('/^(.*)$/m', ' =?' . $this->CharSet . "?$encoding?\\1?=", $encoded);
    $encoded = trim(str_replace("\n", $this->LE, $encoded));
    return $encoded;
  }

  
  public function hasMultiBytes($str) {
    if (function_exists('mb_strlen')) {
      return (strlen($str) > mb_strlen($str, $this->CharSet));
    } else { // Assume no multibytes (we can't handle without mbstring functions anyway)
      return false;
    }
  }

  
  public function has8bitChars($text) {
    return (boolean)preg_match('/[\x80-\xFF]/', $text);
  }

  
  public function base64EncodeWrapMB($str, $linebreak = null) {
    $start = '=?' . $this->CharSet . '?B?';
    $end = '?=';
    $encoded = '';
    if ($linebreak === null) {
      $linebreak = $this->LE;
    }
    $mb_length = mb_strlen($str, $this->CharSet);
    // Each line must have length <= 75, including $start and $end
    $length = 75 - strlen($start) - strlen($end);
    // Average multi-byte ratio
    $ratio = $mb_length / strlen($str);
    // Base64 has a 4:3 ratio
    $avgLength = floor($length * $ratio * .75);
    for ($i = 0; $i < $mb_length; $i += $offset) {
      $lookBack = 0;
      do {
        $offset = $avgLength - $lookBack;
        $chunk = mb_substr($str, $i, $offset, $this->CharSet);
        $chunk = base64_encode($chunk);
        $lookBack++;
      } while (strlen($chunk) > $length);
      $encoded .= $chunk . $linebreak;
    }
    // Chomp the last linefeed
    $encoded = substr($encoded, 0, -strlen($linebreak));
    return $encoded;
  }

  
  public function encodeQP($string, $line_max = 76) {
    // Use native function if it's available (>= PHP5.3)
    if (function_exists('quoted_printable_encode')) {
      return $this->fixEOL(quoted_printable_encode($string));
    }
    // Fall back to a pure PHP implementation
    $string = str_replace(['%20', '%0D%0A.', '%0D%0A', '%'], [' ', "\r\n=2E", "\r\n", '='], rawurlencode($string));
    $string = preg_replace('/[^\r\n]{' . ($line_max - 3) . '}[^=\r\n]{2}/', "$0=\r\n", $string);
    return $this->fixEOL($string);
  }

  public function encodeQPphp($string, $line_max = 76, $space_conv = false) {
    return $this->encodeQP($string, $line_max);
  }
  
  public function encodeQ($str, $position = 'text') {
    // There should not be any EOL in the string
    $pattern = '';
    $encoded = str_replace(["\r", "\n"], '', $str);
    switch (strtolower($position)) {
      case 'phrase':
        // RFC 2047 section 5.3
        $pattern = '^A-Za-z0-9!*+\/ -';
        break;

      case 'comment':
        // RFC 2047 section 5.2
        $pattern = '\(\)"';
        // intentional fall-through
        // for this reason we build the $pattern without including delimiters and []
      case 'text':
      default:
        // RFC 2047 section 5.1
        // Replace every high ascii, control, =, ? and _ characters
        $pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377' . $pattern;
        break;
    }
    $matches = [];
    if (preg_match_all("/[{$pattern}]/", $encoded, $matches)) {
      // If the string contains an '=', make sure it's the first thing we replace
      // so as to avoid double-encoding
      $eqkey = array_search('=', $matches[0]);
      if (false !== $eqkey) {
        unset($matches[0][$eqkey]);
        array_unshift($matches[0], '=');
      }
      foreach (array_unique($matches[0]) as $char) {
        $encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
      }
    }
    // Replace every spaces to _ (more readable than =20)
    return str_replace(' ', '_', $encoded);
  }

  
  public function addStringAttachment($string, $filename, $encoding = 'base64', $type = '', $disposition = 'attachment') {
    // If a MIME type is not specified, try to work it out from the file name
    if ($type == '') {
      $type = self::filenameToType($filename);
    }
    // Append to $attachment array
    $this->attachment[] = [
            0 => $string,
            1 => $filename,
            2 => basename($filename),
            3 => $encoding,
            4 => $type,
            5 => true, // isStringAttachment
            6 => $disposition,
            7 => 0
    ];
  }

  
  public function addEmbeddedImage($path, $cid, $name = '', $encoding = 'base64', $type = '', $disposition = 'inline') {
    if (!is_file($path)) {
      $this->setError($this->lang('file_access') . $path);
      return false;
    }
    // If a MIME type is not specified, try to work it out from the file name
    if ($type == '') {
      $type = self::filenameToType($path);
    }
    $filename = basename($path);
    if ($name == '') {
      $name = $filename;
    }
    // Append to $attachment array
    $this->attachment[] = [
            0 => $path,
            1 => $filename,
            2 => $name,
            3 => $encoding,
            4 => $type,
            5 => false, // isStringAttachment
            6 => $disposition,
            7 => $cid
    ];
    return true;
  }

  public function addStringEmbeddedImage($string, $cid, $name = '', $encoding = 'base64', $type = '', $disposition = 'inline') {
    // If a MIME type is not specified, try to work it out from the name
    if ($type == '') {
      $type = self::filenameToType($name);
    }
    // Append to $attachment array
    $this->attachment[] = [
            0 => $string,
            1 => $name,
            2 => $name,
            3 => $encoding,
            4 => $type,
            5 => true, // isStringAttachment
            6 => $disposition,
            7 => $cid
    ];
    return true;
  }

  
  public function inlineImageExists() {
    foreach ($this->attachment as $attachment) {
      if ($attachment[6] == 'inline') {
        return true;
      }
    }
    return false;
  }

  
  public function attachmentExists() {
    foreach ($this->attachment as $attachment) {
      if ($attachment[6] == 'attachment') {
        return true;
      }
    }
    return false;
  }

  
  public function alternativeExists() {
    return !empty($this->AltBody);
  }

  
  public function clearAddresses() {
    foreach ($this->to as $to) {
      unset($this->all_recipients[strtolower($to[0])]);
    }
    $this->to = [];
  }

  
  public function clearCCs() {
    foreach ($this->cc as $cc) {
      unset($this->all_recipients[strtolower($cc[0])]);
    }
    $this->cc = [];
  }

  
  public function clearBCCs() {
    foreach ($this->bcc as $bcc) {
      unset($this->all_recipients[strtolower($bcc[0])]);
    }
    $this->bcc = [];
  }

  
  public function clearReplyTos() {
    $this->ReplyTo = [];
  }

  
  public function clearAllRecipients() {
    $this->to = [];
    $this->cc = [];
    $this->bcc = [];
    $this->all_recipients = [];
  }

  
  public function clearAttachments() {
    $this->attachment = [];
  }

 
  public function clearCustomHeaders() {
    $this->CustomHeader = [];
  }

  
  protected function setError($msg) {
    $this->error_count++;
    if ($this->Mailer == 'smtp' and !is_null($this->smtp)) {
      $lasterror = $this->smtp->getError();
      if (!empty($lasterror['error'])) {
        $msg .= $this->lang('smtp_error') . $lasterror['error'];
        if (!empty($lasterror['detail'])) {
          $msg .= ' Detail: ' . $lasterror['detail'];
        }
        if (!empty($lasterror['smtp_code'])) {
          $msg .= ' SMTP code: ' . $lasterror['smtp_code'];
        }
        if (!empty($lasterror['smtp_code_ex'])) {
          $msg .= ' Additional SMTP info: ' . $lasterror['smtp_code_ex'];
        }
      }
    }
    $this->ErrorInfo = $msg;
  }

  
  public static function rfcDate() {
    // Set the time zone to whatever the default is to avoid 500 errors
    // Will default to UTC if it's not set properly in php.ini
    date_default_timezone_set(date_default_timezone_get());
    return date('D, j M Y H:i:s O');
  }

  
  protected function serverHostname() {
    $result = 'localhost.localdomain';
    if (!empty($this->Hostname)) {
      $result = $this->Hostname;
    } elseif (isset($_SERVER) and array_key_exists('SERVER_NAME', $_SERVER) and !empty($_SERVER['SERVER_NAME'])) {
      $result = $_SERVER['SERVER_NAME'];
    } elseif (function_exists('gethostname') && gethostname() !== false) {
      $result = gethostname();
    } elseif (php_uname('n') !== false) {
      $result = php_uname('n');
    }
    return $result;
  }

  
  protected function lang($key) {
    if (count($this->language) < 1) {
      $this->setLanguage('en'); // set the default language
    }
    if (array_key_exists($key, $this->language)) {
      if ($key == 'smtp_connect_failed') {
        //Include a link to troubleshooting docs on SMTP connection failure
        //this is by far the biggest cause of support questions
        //but it's usually not PHPMailer's fault.
        return $this->language[$key] . ' https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting';
      }
      return $this->language[$key];
    } else {
      //Return the key as a fallback
      return $key;
    }
  }

  
  public function isError() {
    return ($this->error_count > 0);
  }

  
  public function fixEOL($str) {
    // Normalise to \n
    $nstr = str_replace(["\r\n", "\r"], "\n", $str);
    // Now convert LE as needed
    if ($this->LE !== "\n") {
      $nstr = str_replace("\n", $this->LE, $nstr);
    }
    return $nstr;
  }

  
  public function addCustomHeader($name, $value = null) {
    if ($value === null) {
      // Value passed in as name:value
      $this->CustomHeader[] = explode(':', $name, 2);
    } else {
      $this->CustomHeader[] = [$name, $value];
    }
  }

  
  public function getCustomHeaders() {
    return $this->CustomHeader;
  }

  
  public function msgHTML($message, $basedir = '', $advanced = false) {
    preg_match_all('/(src|background)=["\'](.*)["\']/Ui', $message, $images);
    if (isset($images[2])) {
      foreach ($images[2] as $imgindex => $url) {
        // Convert data URIs into embedded images
        if (preg_match('#^data:(image[^;,]*)(;base64)?,#', $url, $match)) {
          $data = substr($url, strpos($url, ','));
          if ($match[2]) {
            $data = base64_decode($data);
          } else {
            $data = rawurldecode($data);
          }
          $cid = md5($url) . '@phpmailer.0'; // RFC2392 S 2
          if ($this->addStringEmbeddedImage($data, $cid, '', 'base64', $match[1])) {
            $message = str_replace($images[0][$imgindex], $images[1][$imgindex] . '="cid:' . $cid . '"', $message);
          }
        } elseif (!preg_match('#^[A-z]+://#', $url)) {
          // Do not change urls for absolute images (thanks to corvuscorax)
          $filename = basename($url);
          $directory = dirname($url);
          if ($directory == '.') {
            $directory = '';
          }
          $cid = md5($url) . '@phpmailer.0'; // RFC2392 S 2
          if (strlen($basedir) > 1 && substr($basedir, -1) != '/') {
            $basedir .= '/';
          }
          if (strlen($directory) > 1 && substr($directory, -1) != '/') {
            $directory .= '/';
          }
          if ($this->addEmbeddedImage($basedir . $directory . $filename, $cid, $filename, 'base64', self::_mime_types((string)self::mb_pathinfo($filename, PATHINFO_EXTENSION)))) {
            $message = preg_replace('/' . $images[1][$imgindex] . '=["\']' . preg_quote($url, '/') . '["\']/Ui', $images[1][$imgindex] . '="cid:' . $cid . '"', $message);
          }
        }
      }
    }
    $this->isHTML(true);
    // Convert all message body line breaks to CRLF, makes quoted-printable encoding work much better
    $this->Body = $this->normalizeBreaks($message);
    $this->AltBody = $this->normalizeBreaks($this->html2text($message, $advanced));
    if (empty($this->AltBody)) {
      $this->AltBody = 'To view this email message, open it in a program that understands HTML!' .
              self::CRLF . self::CRLF;
    }
    return $this->Body;
  }

  public function html2text($html, $advanced = false) {
    if (is_callable($advanced)) {
      return call_user_func($advanced, $html);
    }
    return html_entity_decode(trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $html))), ENT_QUOTES, $this->CharSet);
  }
  
  public static function _mime_types($ext = '') {
    $mimes = [
            'xl' => 'application/excel',
            'js' => 'application/javascript',
            'hqx' => 'application/mac-binhex40',
            'cpt' => 'application/mac-compactpro',
            'bin' => 'application/macbinary',
            'doc' => 'application/msword',
            'word' => 'application/msword',
            'class' => 'application/octet-stream',
            'dll' => 'application/octet-stream',
            'dms' => 'application/octet-stream',
            'exe' => 'application/octet-stream',
            'lha' => 'application/octet-stream',
            'lzh' => 'application/octet-stream',
            'psd' => 'application/octet-stream',
            'sea' => 'application/octet-stream',
            'so' => 'application/octet-stream',
            'oda' => 'application/oda',
            'pdf' => 'application/pdf',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'mif' => 'application/vnd.mif',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wmlc' => 'application/vnd.wap.wmlc',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'dxr' => 'application/x-director',
            'dvi' => 'application/x-dvi',
            'gtar' => 'application/x-gtar',
            'php3' => 'application/x-httpd-php',
            'php4' => 'application/x-httpd-php',
            'php' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'phps' => 'application/x-httpd-php-source',
            'swf' => 'application/x-shockwave-flash',
            'sit' => 'application/x-stuffit',
            'tar' => 'application/x-tar',
            'tgz' => 'application/x-tar',
            'xht' => 'application/xhtml+xml',
            'xhtml' => 'application/xhtml+xml',
            'zip' => 'application/zip',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'mpga' => 'audio/mpeg',
            'aif' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'ram' => 'audio/x-pn-realaudio',
            'rm' => 'audio/x-pn-realaudio',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'ra' => 'audio/x-realaudio',
            'wav' => 'audio/x-wav',
            'bmp' => 'image/bmp',
            'gif' => 'image/gif',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'eml' => 'message/rfc822',
            'css' => 'text/css',
            'html' => 'text/html',
            'htm' => 'text/html',
            'shtml' => 'text/html',
            'log' => 'text/plain',
            'text' => 'text/plain',
            'txt' => 'text/plain',
            'rtx' => 'text/richtext',
            'rtf' => 'text/rtf',
            'vcf' => 'text/vcard',
            'vcard' => 'text/vcard',
            'xml' => 'text/xml',
            'xsl' => 'text/xml',
            'mpeg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mov' => 'video/quicktime',
            'qt' => 'video/quicktime',
            'rv' => 'video/vnd.rn-realvideo',
            'avi' => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie'
    ];
    if (array_key_exists(strtolower($ext), $mimes)) {
      return $mimes[strtolower($ext)];
    }
    return 'application/octet-stream';
  }

  
  public static function filenameToType($filename) {
    // In case the path is a URL, strip any query string before getting extension
    $qpos = strpos($filename, '?');
    if (false !== $qpos) {
      $filename = substr($filename, 0, $qpos);
    }
    $pathinfo = self::mb_pathinfo($filename);
    return self::_mime_types($pathinfo['extension']);
  }

  
  public static function mb_pathinfo($path, $options = null) {
    $ret = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
    $pathinfo = [];
    if (preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $path, $pathinfo)) {
      if (array_key_exists(1, $pathinfo)) {
        $ret['dirname'] = $pathinfo[1];
      }
      if (array_key_exists(2, $pathinfo)) {
        $ret['basename'] = $pathinfo[2];
      }
      if (array_key_exists(5, $pathinfo)) {
        $ret['extension'] = $pathinfo[5];
      }
      if (array_key_exists(3, $pathinfo)) {
        $ret['filename'] = $pathinfo[3];
      }
    }
    switch ($options) {
      case PATHINFO_DIRNAME:
      case 'dirname':
        return $ret['dirname'];
      case PATHINFO_BASENAME:
      case 'basename':
        return $ret['basename'];
      case PATHINFO_EXTENSION:
      case 'extension':
        return $ret['extension'];
      case PATHINFO_FILENAME:
      case 'filename':
        return $ret['filename'];
      default:
        return $ret;
    }
  }

  
  public function set($name, $value = '') {
    if (property_exists($this, $name)) {
      $this->$name = $value;
      return true;
    } else {
      $this->setError($this->lang('variable_set') . $name);
      return false;
    }
  }

  
  public function secureHeader($str) {
    return trim(str_replace(["\r", "\n"], '', $str));
  }

  
  public static function normalizeBreaks($text, $breaktype = "\r\n") {
    return preg_replace('/(\r\n|\r|\n)/ms', $breaktype, $text);
  }

  
  public function sign($cert_filename, $key_filename, $key_pass, $extracerts_filename = '') {
    $this->sign_cert_file = $cert_filename;
    $this->sign_key_file = $key_filename;
    $this->sign_key_pass = $key_pass;
    $this->sign_extracerts_file = $extracerts_filename;
  }

  
  public function DKIM_QP($txt) {
    $line = '';
    for ($i = 0; $i < strlen($txt); $i++) {
      $ord = ord($txt[$i]);
      if (((0x21 <= $ord) && ($ord <= 0x3A)) || $ord == 0x3C || ((0x3E <= $ord) && ($ord <= 0x7E))) {
        $line .= $txt[$i];
      } else {
        $line .= '=' . sprintf('%02X', $ord);
      }
    }
    return $line;
  }

  
  public function DKIM_Sign($signHeader) {
    if (!defined('PKCS7_TEXT')) {
      if ($this->exceptions) {
        throw new phpmailerException($this->lang('extension_missing') . 'openssl');
      }
      return '';
    }
    $privKeyStr = file_get_contents($this->DKIM_private);
    if ($this->DKIM_passphrase != '') {
      $privKey = openssl_pkey_get_private($privKeyStr, $this->DKIM_passphrase);
    } else {
      $privKey = $privKeyStr;
    }
    if (openssl_sign($signHeader, $signature, $privKey)) {
      return base64_encode($signature);
    }
    return '';
  }

  
  public function DKIM_HeaderC($signHeader) {
    $signHeader = preg_replace('/\r\n\s+/', ' ', $signHeader);
    $lines = explode("\r\n", $signHeader);
    foreach ($lines as $key => $line) {
      list($heading, $value) = explode(':', $line, 2);
      $heading = strtolower($heading);
      $value = preg_replace('/\s+/', ' ', $value); // Compress useless spaces
      $lines[$key] = $heading . ':' . trim($value); // Don't forget to remove WSP around the value
    }
    $signHeader = implode("\r\n", $lines);
    return $signHeader;
  }

  
  public function DKIM_BodyC($body) {
    if ($body == '') {
      return "\r\n";
    }
    // stabilize line endings
    $body = str_replace("\r\n", "\n", $body);
    $body = str_replace("\n", "\r\n", $body);
    // END stabilize line endings
    while (substr($body, strlen($body) - 4, 4) == "\r\n\r\n") {
      $body = substr($body, 0, strlen($body) - 2);
    }
    return $body;
  }

  
  public function DKIM_Add($headers_line, $subject, $body) {
    $DKIMsignatureType = 'rsa-sha1'; // Signature & hash algorithms
    $DKIMcanonicalization = 'relaxed/simple'; // Canonicalization of header/body
    $DKIMquery = 'dns/txt'; // Query method
    $DKIMtime = time(); // Signature Timestamp = seconds since 00:00:00 - Jan 1, 1970 (UTC time zone)
    $subject_header = "Subject: $subject";
    $headers = explode($this->LE, $headers_line);
    $from_header = '';
    $to_header = '';
    $current = '';
    foreach ($headers as $header) {
      if (strpos($header, 'From:') === 0) {
        $from_header = $header;
        $current = 'from_header';
      } elseif (strpos($header, 'To:') === 0) {
        $to_header = $header;
        $current = 'to_header';
      } else {
        if (!empty($$current) && strpos($header, ' =?') === 0) {
          $$current .= $header;
        } else {
          $current = '';
        }
      }
    }
    $from = str_replace('|', '=7C', $this->DKIM_QP($from_header));
    $to = str_replace('|', '=7C', $this->DKIM_QP($to_header));
    $subject = str_replace('|', '=7C', $this->DKIM_QP($subject_header)); // Copied header fields (dkim-quoted-printable)
    $body = $this->DKIM_BodyC($body);
    $DKIMlen = strlen($body); // Length of body
    $DKIMb64 = base64_encode(pack('H*', sha1($body))); // Base64 of packed binary SHA-1 hash of body
    if ('' == $this->DKIM_identity) {
      $ident = '';
    } else {
      $ident = ' i=' . $this->DKIM_identity . ';';
    }
    $dkimhdrs = 'DKIM-Signature: v=1; a=' .
            $DKIMsignatureType . '; q=' .
            $DKIMquery . '; l=' .
            $DKIMlen . '; s=' .
            $this->DKIM_selector .
            ";\r\n" .
            "\tt=" . $DKIMtime . '; c=' . $DKIMcanonicalization . ";\r\n" .
            "\th=From:To:Subject;\r\n" .
            "\td=" . $this->DKIM_domain . ';' . $ident . "\r\n" .
            "\tz=$from\r\n" .
            "\t|$to\r\n" .
            "\t|$subject;\r\n" .
            "\tbh=" . $DKIMb64 . ";\r\n" .
            "\tb=";
    $toSign = $this->DKIM_HeaderC($from_header . "\r\n" . $to_header . "\r\n" . $subject_header . "\r\n" . $dkimhdrs);
    $signed = $this->DKIM_Sign($toSign);
    return $dkimhdrs . $signed . "\r\n";
  }

 
  public static function hasLineLongerThanMax($str) {
    //+2 to include CRLF line break for a 1000 total
    return (boolean)preg_match('/^(.{' . (self::MAX_LINE_LENGTH + 2) . ',})/m', $str);
  }

  
  public function getToAddresses() {
    return $this->to;
  }

  
  public function getCcAddresses() {
    return $this->cc;
  }

  
  public function getBccAddresses() {
    return $this->bcc;
  }

  
  public function getReplyToAddresses() {
    return $this->ReplyTo;
  }

  
  public function getAllRecipientAddresses() {
    return $this->all_recipients;
  }

  
  protected function doCallback($isSent, $to, $cc, $bcc, $subject, $body, $from) {
    if (!empty($this->action_function) && is_callable($this->action_function)) {
      $params = [$isSent, $to, $cc, $bcc, $subject, $body, $from];
      call_user_func_array($this->action_function, $params);
    }
  }
}


class phpmailerException extends Exception
{
  
  public function errorMessage() {
    $errorMsg = '<strong>' . $this->getMessage() . "</strong><br>\n";
    return $errorMsg;
  }
}
