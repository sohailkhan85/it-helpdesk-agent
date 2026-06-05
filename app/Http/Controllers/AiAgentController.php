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
        'system'     => config('branding.system_prompt'),
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

    public function dashboard()
    {
        $leads = DB::table('helpdesk_leads')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('dashboard', compact('leads'));
    }
    public function export()
    {
        $leads = DB::table('helpdesk_leads')->orderBy('created_at', 'desc')->get();

        $csv = "ID,Name,Email,Issue,Date\n";
        foreach ($leads as $lead) {
            $csv .= "{$lead->id},{$lead->name},{$lead->email},\"{$lead->issue}\",{$lead->created_at}\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads.csv"',
        ]);
    }

    public function home()
{
    return view('home');
}
}
