<?php
function tng_sendmail($from_name, $from_email, $to_name, $to_email, $subject, $body, $returnpath, $replyto) {
  global $session_charset;
  global $envelope;
  global $tngconfig;

  if (function_exists('filter_var') && !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
    $success = false;
  } else {
    if ($tngconfig['usesmtp']) {
      include_once 'PHPMailerAutoload.php';
      
      //require_once("class.phpmailer.php");
      //require_once("class.smtp.php");

      //need host, port, username & password
      $options = new stdClass();
      $options->charset = $session_charset;
      $mail = new PHPMailer($options, true);
      try {
        $mail->IsSMTP();                           // set mailer to use SMTP
        $mail->Host = $tngconfig['mailhost'];      // specify main and backup server or localhost
        $mail->SMTPAuth = true;                    // turn on SMTP authentication
        $mail->Username = $tngconfig['mailuser'];  // SMTP username
        $mail->Password = $tngconfig['mailpass'];  // SMTP password
        if (!empty($tngconfig['mailenc'])) {
          $mail->SMTPSecure = $tngconfig['mailenc'];
        } // SMTP encryption
        //It should be same as that of the SMTP user
        $mail->Port = $tngconfig['mailport'];

        $mail->From = $from_email;    // Default From email same as smtp user
        $mail->FromName = $from_name;

        $mail->AddAddress($to_email, $to_name); //Email address where you wish to receive/collect those emails.
        $mail->AddReplyTo($replyto, $from_name);

        $mail->CharSet = strtolower($session_charset);
        //$mail->WordWrap = 50;
        if ($session_charset && strtoupper($session_charset) != 'ISO-8859-1') {
          $mail->IsHTML(true);                                  // set email format to HTML
          $body = nl2br($body);
        }

        $mail->Subject = $subject;
        $mail->Body = $body;

        $success = $mail->Send();
      } catch (phpmailerException $e) {
        // [ts]        echo $e->errorMessage();
      } catch (Exception $e) {
        // [ts]        echo $e->getMessage();
      }
    } else {
      //normal procedure
      if (strtoupper($session_charset) != 'ISO-8859-1') {
        //$from_name = '=?utf-8?B?'.base64_encode($from_name).'?=';
        //$subject = '=?utf-8?B?'.base64_encode($subject).'?=';
        $headers = "MIME-Version: 1.0\nContent-type: text/html; charset=$session_charset\nFrom: $from_name <$from_email>\nReply-to: $replyto\nReturn-Path: $from_email";
        $body = "<html>\n<head>\n<meta http-equiv=\"Content-type\" content=\"text/html; charset=$session_charset\">\n</head>\n<body>\n" . nl2br($body) . "</body>\n</html>\n";
      } else {
        $headers = "From: $from_name <$from_email>\nReply-to: $replyto\nReturn-Path: $returnpath\nDate:" . date('r', time());
      }
      if ($envelope) {
        $success = mail($to_email, $subject, $body, $headers, '-f' . $from_email);
      } else {
        $success = mail($to_email, $subject, $body, $headers);
      }
    }
  }
  return $success;
}