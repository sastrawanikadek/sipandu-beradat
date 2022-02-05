<?php
    require_once("classes/class.phpmailer.php");
    
    function send_mail($subject, $dest, $message) {
        $mail = new PHPMailer; 
        $mail->IsSMTP();
        $mail->SMTPSecure = 'tls'; 
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPDebug = 0;
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = "";
        $mail->Password = "";
        $mail->SetFrom("sipanduberadat@gmail.com");
        $mail->Subject = $subject;
        $mail->AddAddress($dest);
        $mail->MsgHTML($message);
        
        return $mail->Send();
    }