<?php

namespace App\Http\Controllers;

use ClaudePhp\Laravel\Facades\Claude;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesEmailController extends Controller
{
    public function index()
    {
        return view('salesemail');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'sender_name'     => 'required|string|max:100',
            'sender_company'  => 'required|string|max:100',
            'product_service' => 'required|string|max:200',
            'target_company'  => 'required|string|max:100',
            'pain_point'      => 'required|string|max:500',
            'tone'            => 'required|in:professional,friendly,urgent,consultative',
        ]);

        $senderName     = $request->input('sender_name');
        $senderCompany  = $request->input('sender_company');
        $productService = $request->input('product_service');
        $targetCompany  = $request->input('target_company');
        $targetName     = $request->input('target_name', 'there');
        $targetRole     = $request->input('target_role', 'Decision Maker');
        $painPoint      = $request->input('pain_point');
        $tone           = $request->input('tone');

        $prompt = "You are an expert B2B sales copywriter. Generate a complete sales email package.

SENDER INFO:
- Name: {$senderName}
- Company: {$senderCompany}
- Product/Service: {$productService}

TARGET INFO:
- Company: {$targetCompany}
- Contact Name: {$targetName}
- Role: {$targetRole}
- Pain Point: {$painPoint}

TONE: {$tone}

Generate the following in this EXACT format:

==SUBJECT_LINES==
Generate 3 compelling subject lines (one per line)

==COLD_EMAIL==
Write a personalized cold email (150-200 words max). 
No generic openers. Start with their specific pain point.
End with a soft CTA for a 15-minute call.

==FOLLOWUP_1==
Write follow-up email #1 (send after 3 days, 100 words max)

==FOLLOWUP_2==
Write follow-up email #2 (send after 7 days, 80 words max)

==FOLLOWUP_3==
Write follow-up email #3 (final breakup email, 60 words max)

==LINKEDIN==
Write a LinkedIn connection request message (300 characters max)

Keep everything specific, personalized and focused on VALUE not features.";

        $response = Claude::messages()->create([
            'model'      => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2048,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $content = $response->content[0]['text'];

        // Parse sections
        $subjectLines   = $this->extractSection($content, 'SUBJECT_LINES');
        $coldEmail      = $this->extractSection($content, 'COLD_EMAIL');
        $followup1      = $this->extractSection($content, 'FOLLOWUP_1');
        $followup2      = $this->extractSection($content, 'FOLLOWUP_2');
        $followup3      = $this->extractSection($content, 'FOLLOWUP_3');
        $linkedin       = $this->extractSection($content, 'LINKEDIN');

        $followupSequence = json_encode([
            'followup_1' => $followup1,
            'followup_2' => $followup2,
            'followup_3' => $followup3,
        ]);

        // Save to database
        DB::table('sales_emails')->insert([
            'sender_name'       => $senderName,
            'sender_company'    => $senderCompany,
            'product_service'   => $productService,
            'target_company'    => $targetCompany,
            'target_name'       => $targetName,
            'target_role'       => $targetRole,
            'pain_point'        => $painPoint,
            'tone'              => $tone,
            'generated_email'   => $coldEmail,
            'followup_sequence' => $followupSequence,
            'linkedin_message'  => $linkedin,
            'subject_lines'     => $subjectLines,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json([
            'success'       => true,
            'subject_lines' => $subjectLines,
            'cold_email'    => $coldEmail,
            'followup_1'    => $followup1,
            'followup_2'    => $followup2,
            'followup_3'    => $followup3,
            'linkedin'      => $linkedin,
        ]);
    }

    private function extractSection($content, $section)
    {
        $pattern = '/==' . $section . '==\s*(.*?)(?===\w|$)/s';
        preg_match($pattern, $content, $matches);
        return isset($matches[1]) ? trim($matches[1]) : '';
    }
}