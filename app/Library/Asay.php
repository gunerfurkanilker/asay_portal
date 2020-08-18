<?php
/**
 * Created by IntelliJ IDEA.
 * User: serkan.erdinc
 * Date: 18.08.2020
 * Time: 16:20
 */

namespace App\Library;


use Illuminate\Support\Facades\Mail;

class Asay
{
    public static function sendMail($to,$cc="",$subject,$message,$title="",$attach="")
    {
        Mail::send([], [], function ($email) use($to,$cc,$subject,$message,$title,$attach) {
            if($title==""){
                $title="aSAY Group";
            }
            $email->from('sender@asay.com.tr', $title);
            $email->to($to);
            if($cc<>"")
                $email->cc($cc);
            $email->subject($subject);
            if($attach<>"")
            {
                $email->attach($attach);
            }
            $email->setBody($message, 'text/html');
        });
    }

}
