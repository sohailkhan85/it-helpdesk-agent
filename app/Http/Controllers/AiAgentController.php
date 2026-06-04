<?php

namespace App\Http\Controllers;

use ClaudePhp\Laravel\Facades\Claude;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiAgentController extends Controller
{
    public function index()
    {
        return view('helpdesk');
    }

    public function ask(Request $request)
    {
        $history = $request->input('history', []);

        $history[] = [
            'role'    => 'user',
            'content' => $request->input('question', 'Hello!')
        ];

        $response = Claude::messages()->create([
            'model'      => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'system'     => 'You are an expert IT Helpdesk Assistant for a managed IT services company. 
                             You specialize in networking, surveillance systems, fiber optics, 
                             and general IT infrastructure support. 
                             Give concise, professional answers.
                             If you need more info to diagnose an issue, ask one question at a time.',
            'messages'   => $history
        ]);

        $answer = $response->content[0]['text'];

        $history[] = [
            'role'    => 'assistant',
            'content' => $answer
        ];

        return response()->json([
            'answer'  => $answer,
            'history' => $history
        ]);
    }

    public function saveLead(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|max:100',
        ]);

        // Save to database
        DB::table('helpdesk_leads')->insert([
            'name'       => $request->input('name'),
            'email'      => $request->input('email'),
            'issue'      => $request->input('issue', ''),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}