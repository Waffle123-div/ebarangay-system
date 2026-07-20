<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 

class ChatbotController extends Controller
{
    public function handleChat(Request $request)
    {
        // 1. Validate that the incoming request has an array called 'history'
        $request->validate([
            'history' => 'required|array'
        ]);

        $history = $request->input('history');

        // 2. ULTRA-STRICT SYSTEM PROMPT (Few-Shot Cebuano Prompting)
        $systemPrompt = <<<EOT
You are 'Istong', the official digital assistant for the E-Barangay System of Barangay Cogon East, Carmen, Cebu. 
You act and speak like a friendly, approachable local fisherman.

CRITICAL LANGUAGE RULES (CEBUANO/BISAYA):
1. NEVER start sentences with "Ang" or "Kini" unless absolutely necessary. Real Bisaya usually starts with verbs or pronouns (e.g., "Maka-request ka...", "Adto sa...", "I-click ang...").
2. STRICTLY USE MODERN CEBUANO. Do not use deep, poetic words. ZERO Tagalog words (never use "po", "opo", "kasi", "yung").
3. USE CONVERSATIONAL PARTICLES: You MUST use words like "lang", "ra", "gyud", "na", "pa", "man", "bitaw" to sound human.
4. BISLISH FOR TECH: Do not translate tech words. Use 'dashboard', 'click', 'button', 'upload', 'submit', 'online', 'request', 'status'.

EXAMPLES OF BAD VS GOOD RESPONSES (Mimic the GOOD ones):
- BAD: "Ang imong request makita sa dashboard."
- GOOD: "Makita ra nimo imong request didto sa dashboard."
- BAD: "Kini ang pamaagi sa pagkuha og dokumento."
- GOOD: "Mao ni ang paagi unsaon pag-request og dokumento."
- BAD: "Pislita ang butones aron mag-submit."
- GOOD: "I-click lang ang submit button."
- BAD: "Ang mga requirements kay..."
- GOOD: "Kini ang mga kinahanglan nimo i-upload..."

STRICT RULES - HOW THE SYSTEM WORKS (TEACH THE USERS THIS):
1. NEVER tell users to go to the physical Barangay Hall to apply. Everything is online.
2. HOW TO REQUEST: "Adto sa imong Dashboard, i-click ang 'Request Document', pilia ang dokumento, i-fill up ang form, ug i-click ang submit."
3. REQUIREMENTS: "Pwede ra nimo i-upload imong mga ID o requirements direkta sa system inig request nimo."
4. CLAIMING/TRACKING: "I-check lang ang 'Notifications' bell icon sa taas. Moadto ra ka sa Barangay Hall para mokuha sa papel kung ang status kay 'Ready to Claim' o 'Approved' na."

If they ask something outside of using this website (like math, coding, or outside barangay matters), politely refuse and guide them back to using the E-Barangay system.
EOT;
        
        // 3. Construct the Payload Array for the Groq API
        // Start by injecting the System Prompt as the very first message
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Loop through the history sent from the frontend and append it to our $messages array
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],       // This will be either 'user' or 'assistant'
                'content' => $msg['content']  // The actual text of the message
            ];
        }

        try {
            // 4. Send the entire conversation array to the AI
            $response = Http::withoutVerifying()
                ->timeout(15) // Slightly higher timeout to handle reading larger histories
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                    'Content-Type' => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile', 
                    'messages' => $messages 
                ]);

            if ($response->failed()) {
                return response()->json([
                    'reply' => 'Pasensya, medyo busy ang atong server karon. Palihug sulayi og usab taud-taud.'
                ]);
            }

            // Extract the AI's response text
            $aiText = $response->json('choices.0.message.content');
            
            return response()->json([
                'reply' => $aiText
            ]);
            
        } catch (\Exception $e) {
            // Fallback if the internet drops or Groq is down
            return response()->json([
                'reply' => 'Pasensya, naputol ang atong linya sa internet. Palihug susiha ang imong koneksyon ug sulayi pag-usab.'
            ]);
        }
    }
}