<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class BakamlaCallController extends Controller
{
    public function makeCall(Request $request){
        $to = $request->input('to');
        $from = config('services.twilio.phone_number');

        $twilio = new Client(config('services.twilio.sid'), config('services.twilio.auth-token'));
        $twilio->calls->create($to, $from, ['url' => 'https://api.bakamla.barengsaya.com/api/twilio-voice']);
        return response()->json(['message' => 'Call initiated'], 200);
    }

    public function handleTwilioVoiceCallback(Request $request){
        $callSid = $request->input('CallSid');
        $from = $request->input('from');
        $to = $request->input('to');
        $callstatus = $request->input('callstatus');

        Log::info("Callback for Call SID: $callSid, From: $from, To: $to, Status: $callstatus");

        return response('<Response><Dial>' . $to . '</Dial></Response>', 200)
        ->header('Content-Type', 'application/xml');
    }
}
