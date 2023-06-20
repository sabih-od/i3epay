<?php

namespace App\Traits;

trait PHPCustomMail
{
    /**
     * @param $from
     * @param $to
     * @param $subject
     * @param $html
     * @return bool
     */
    public function customMail($from, $to, $subject, $html)
    {
        //$from = 'no-reply@tha-network.com';

        // To send HTML mail, the Content-type header must be set
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Create email headers
        $headers .= 'From: ' . $from . "\r\n" .
            'Reply-To: ' . $from . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        // Compose a simple HTML email message
        $message = $html;

        // Sending email
        if (mail($to, $subject, $message, $headers)) {
            return true;
        } else {
            return false;
        }
    }

}
