<?php

namespace App\Controller;
//sursa: https://github.com/PHPMailer/PHPMailer
//tutorial: https://alexwebdevelop.com/phpmailer-tutorial/
//Gmail restriction: https://support.google.com/mail/answer/22370?hl=en

require_once('class.phpmailer.php');
require_once('mail_config.php');

class EmailSend
{
  public function sendEmail(string $email, string $username, string $parola)
  {
// Mesajul
    $message = "Salut,<br>Multumim pentru ca ti-ai facut cont pe platforma noastra. Ai aici credentialele:<br>Username: " . $username . "<br>Parola: " . $parola;

// În caz că vre-un rând depășește N caractere, trebuie să utilizăm
// wordwrap()

    $mail = new PHPMailer(true);

    $mail->IsSMTP();

    try {

      $mail->SMTPAuth = true;

      $to = $email;
      $nume = $username;

      $mail->SMTPSecure = "ssl";
      $mail->Host = "smtp.gmail.com";
      $mail->Port = 465;
      $mail->Username = 'baschet.bucurestean@gmail.com';            // GMAIL username
      $mail->Password = 'nyoeyvnutvilynbp';            // GMAIL password
      $mail->AddReplyTo('moscraciun@gmail.com', 'Mos Craciun');
      $mail->AddAddress($to, $nume);

      $mail->SetFrom('baschet.bucurestean@gmail.com', 'Baschet Bucurestean');
      $mail->Subject = 'Noul tau cont!';
      $mail->AltBody = 'To view this post you need a compatible HTML viewer!';
      $mail->MsgHTML($message);
      $mail->Send();
    } catch (phpmailerException $e) {
      echo $e->errorMessage(); //error from PHPMailer
    } catch (Exception $e) {
      echo $e->getMessage(); //error from anything else!
    }
  }
}
?>
