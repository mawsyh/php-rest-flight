<?php
if (!defined('MyConst')) {
    die('Direct access not permitted');
}

function encrypt($data)
{
    $encryptionKey = base64_decode("8a4a0dc0a65495bb");
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-gcm'));
    $encrypted = openssl_encrypt($data, 'aes-256-gcm', $encryptionKey, 0, $iv, $tag);
    $encoded = base64_encode($encrypted . '::' . $iv . '::' . $tag);
    return str_replace('/', '-', $encoded);
}

function decrypt($data)
{
    $encryptionKey = base64_decode("8a4a0dc0a65495bb");
    list($encryptedData, $iv, $tag) = explode('::', base64_decode($data), 3);
    return openssl_decrypt($encryptedData, 'aes-256-gcm', $encryptionKey, 0, $iv, $tag);
}

function sendMail($email, $link,$subject, $message)
{
    // Create a new PHPMailer instance
    $mail = new PHPMailer\PHPMailer\PHPMailer();

    // Set SMTP settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = 'tls';
    $mail->Port = SMTP_PORT;
    $mail->XMailer = ' ';

    // Set email content
    $mail->setFrom(SMTP_USER);
    $mail->addAddress($email);
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->isHTML(true);

    // Send the email
    if (!$mail->send()) {
        echo 'Error sending email: ' . $mail->ErrorInfo;
    } else {
        echo 'Email sent successfully!';
    }
}

function sendActivation($email, $verify)
{
    $exptime = time() + 300;
    $sign = $verify . ':' . $exptime . ':' . EMAIL_HASH;
    $hashedSign = sha1($sign);
    $link = DOMAIN . '/verify/' . $verify . '/' . $exptime . '/' . $hashedSign;
    $subject = 'Verify your email address';
    $message = '<html><body style="background-color: #f2f2f2; font-family: Arial, sans-serif; font-size: 16; line-height: 1.5;">';
    $message .= '<div style="background-color: #ffffff; border-radius: 10px; padding: 20px;">';
    $message .= '<h1 style="color: #007bff;">Activate your account</h1>';
    $message .= '<p>Please click the following link to activate your account:</p>';
    $message .= '<a href="' . $link . '" style="background-color: #007bff; color: #ffffff; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Activate Now</a>';
    $message .= '</div>';
    $message .= '</body></html>';
    sendMail($email, $link, $subject, $message);
}

function sendResetPassword($email)
{
    $exptime = time() + 300;
    $sign = $exptime . ':' . EMAIL_HASH;
    $hashedSign = sha1($sign);
    $link = DOMAIN . '/reset-password/' . $exptime . '/' . $hashedSign;
    $subject = 'Reset Password';
    $message = '<html><body style="background-color: #f2f2f2; font-family: Arial, sans-serif; font-size: 16; line-height: 1.5;">';
    $message .= '<div style="background-color: #ffffff; border-radius: 10px; padding: 20px;">';
    $message .= '<h1 style="color: #007bff;">Reset your acount password</h1>';
    $message .= '<p>Please click the following link to reset your password:</p>';
    $message .= '<a href="' . $link . '" style="background-color: #007bff; color: #ffffff; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Reset Password</a>';
    $message .= '</div>';
    $message .= '</body></html>';
    sendMail($email, $link, $subject, $message);
}

?>