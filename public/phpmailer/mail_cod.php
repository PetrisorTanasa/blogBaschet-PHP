<?php

namespace App\Controller;
//sursa: https://github.com/PHPMailer/PHPMailer
//tutorial: https://alexwebdevelop.com/phpmailer-tutorial/
//Gmail restriction: https://support.google.com/mail/answer/22370?hl=en

require_once('class.phpmailer.php');
require_once('mail_config.php');


class EmailSend
{
  public function sendEmail(string $email, string $username, string $parola, string $site = "a", string $mesajFeedback = "feed")
  {
// Mesajul
    if($mesajFeedback == "feed"){
    $message = "Salut,<br>Multumim pentru ca ti-ai facut cont pe platforma noastra. Ai aici credentialele:<br>Email: " . $email . "<br>Parola: " . $parola . "<br><br>Numele contului tau este " . $username;

    if($site != "a") {
      $message .= "<br><br>Pentru a activa contul este necesar sa intrati pe acest link: " . $site;
    }
    }else{
      $message = "Salut " . $username . ",<br>Multumim mult pentru feedback ul transmis. O sa incercam sa introducem aceste functionalitati pe viitor. In caz ca ai uitat, ai lasat " . $parola . "/5 stele , iar acesta este feedback ul lasat de tine:<br>" . $mesajFeedback;
    }

    $mail = new \PHPMailer(true);

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
      if($mesajFeedback == "feed") {
        $mail->Subject = 'Noul tau cont!';
      }else{
        $mail->Subject = 'Feedback baschet-bucurestean';
      }
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
