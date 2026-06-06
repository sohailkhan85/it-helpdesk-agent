<?php

namespace App\Http\Controllers;

use ClaudePhp\Laravel\Facades\Claude;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\NewLeadAlert;
use Illuminate\Support\Facades\Mail;

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

    $name  = $request->input('name');
    $email = $request->input('email');
    $issue = $request->input('issue', 'No issue provided'); // ← default value

    DB::table('helpdesk_leads')->insert([
        'name'       => $name,
        'email'      => $email,
        'issue'      => $issue,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Mail::to(env('ALERT_EMAIL'))->send(new NewLeadAlert($name, $email, $issue));

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
    public function updateTranscript(Request $request)
    {
        DB::table('helpdesk_leads')
            ->where('email', $request->input('email'))
            ->orderBy('id', 'desc')
            ->limit(1)
            ->update(['transcript' => $request->input('transcript')]);

        return response()->json(['success' => true]);
    }
}
