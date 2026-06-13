<?php

namespace App\Http\Controllers;

use ClaudePhp\Laravel\Facades\Claude;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;

class PdfChatController extends Controller
{
    public function index()
    {
        return view('pdfchat');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        $file = $request->file('pdf');
        $path = $file->store('pdfs');

        // Extract text from PDF
        $parser = new Parser();
        //$pdf = $parser->parseFile(storage_path('app/' . $path));
        $pdf = $parser->parseFile(storage_path('app/private/' . $path));
        $text = $pdf->getText();

        // Limit text to avoid huge context (first 50000 chars)
        $text = substr($text, 0, 50000);

        $sessionId = session()->getId();

        // Save to database
        $docId = DB::table('pdf_documents')->insertGetId([
            'filename'       => $file->getClientOriginalName(),
            'extracted_text' => $text,
            'session_id'     => $sessionId,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return response()->json([
            'success'  => true,
            'doc_id'   => $docId,
            'filename' => $file->getClientOriginalName(),
            'preview'  => substr($text, 0, 300) . '...',
        ]);
    }

    public function ask(Request $request)
    {
        $docId = $request->input('doc_id');
        $question = $request->input('question');
        $history = $request->input('history', []);

        $document = DB::table('pdf_documents')->find($docId);

        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        $history[] = [
            'role'    => 'user',
            'content' => $question
        ];

$systemPrompt = "You are an AI assistant that answers questions about a PDF document. 
ONLY answer based on the following document content. If the answer is not in the document, 
say 'I cannot find that information in this document.' Do not make up information.
Keep responses concise — use short paragraphs and bullet points, max 150 words unless 
the user explicitly asks for full details.

DOCUMENT CONTENT:
" . $document->extracted_text;

        $response = Claude::messages()->create([
            'model'      => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2048,
            'system'     => $systemPrompt,
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
}