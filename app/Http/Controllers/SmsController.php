<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// 1. Paste the two 'use' statements here at the top
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    // 2. Paste the entire function inside the class
    public function testEBarangaySms()
    {
        $apiKey = env('TEXTBEE_API_KEY');
        $deviceId = '6ab282bf9c363efd11d663cf'; 

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.textbee.dev/api/v1/gateway/send-sms', [
            'deviceId'   => $deviceId,
            'recipients' => ['+639123456789'], // Make sure to change to your number
            'message'    => 'E-Barangay System Test: Your SMS gateway is now live!'
        ]);

        if ($response->successful()) {
            return "Success! Check your phone for the text message.";
        }

        Log::error('Textbee Error: ' . $response->body());
        return "Failed to send. Check Laravel logs.";
    }
}