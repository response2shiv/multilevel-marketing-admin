<?php

namespace App;

use App\Setting;
use Twilio\Rest\Client;
use Illuminate\Database\Eloquent\Model;

class Twilio extends Model {

    private $sid;
    private $token;
    private $from;

    /**
     * @return array
     */
    private static function getConfig()
    {
        $token = Setting::where('param', 'SMS_TOKEN')->first();
        $sid = Setting::where('param', 'SMS_SID')->first();
        $from = Setting::where('param', 'SMS_FROM')->first();

        return ['token' => $token, 'sid' => $sid, 'from' => $from]; 
    }

    public static function sendSMS($to, $message) {
        $config = self::getConfig();
        $sid = 'AC9e6cb008b3daf41f0ac81e0698117fd7';
        $token = '6e2f6471dbf99ef91bf9ad7b1aace792';
        $from = '+13212332212';
        //
        $twilio = new Client($sid, $token);
        try {
            $response = $twilio->messages->create(
                    // the number you'd like to send the message to
                    $to, array(
                // A Twilio phone number you purchased at twilio.com/console
                'from' => $from,
                // the body of the text message you'd like to send
                'body' => $message
                    )
            );
            //
            return array('status' => 'success', 'sid' => (string) $response->sid, 'sms_status' => (string) $response->status, 'msg' => '');
        } catch (\Exception $ex) {
            return array('status' => 'error', 'msg' => $ex->getMessage());
        }
    }

}
