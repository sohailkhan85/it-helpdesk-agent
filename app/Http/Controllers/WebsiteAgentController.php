<?php

namespace App\Http\Controllers;

use ClaudePhp\Laravel\Facades\Claude;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class WebsiteAgentController extends Controller
{
    public function index()
    {
        return view('websiteai');
    }

    public function scrape(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->input('url');

        try {
            // Create agent record
            $agentId = DB::table('website_agents')->insertGetId([
                'url'        => $url,
                'status'     => 'scraping',
                'session_id' => session()->getId(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Scrape the website
            $content = $this->scrapeWebsite($url);

            // Extract business name from title
            $businessName = $this->extractBusinessName($content, $url);

            // Update agent with scraped content
            DB::table('website_agents')->where('id', $agentId)->update([
                'scraped_content' => $content,
                'business_name'   => $businessName,
                'status'          => 'ready',
                'updated_at'      => now(),
            ]);

            return response()->json([
                'success'       => true,
                'agent_id'      => $agentId,
                'business_name' => $businessName,
                'preview'       => substr($content, 0, 500) . '...',
                'content_length'=> strlen($content),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to scrape website: ' . $e->getMessage()
            ], 500);
        }
    }

    private function scrapeWebsite($url)
    {
        $client = new Client([
            'timeout'         => 30,
            'verify'          => false,
            'headers'         => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        $content = '';
        $visited = [];
        $toVisit = [$url];
        $baseHost = parse_url($url, PHP_URL_HOST);
        $maxPages = 5; // scrape up to 5 pages
        $pagesScraped = 0;

        while (!empty($toVisit) && $pagesScraped < $maxPages) {
            $currentUrl = array_shift($toVisit);

            if (in_array($currentUrl, $visited)) continue;
            $visited[] = $currentUrl;

            try {
                $response = $client->get($currentUrl);
                $html = (string) $response->getBody();
                $crawler = new Crawler($html);

                // Extract page title
                $title = '';
                try {
                    $title = $crawler->filter('title')->text();
                } catch (\Exception $e) {}

                // Extract main text content
                $pageContent = '';
                $selectors = ['main', 'article', '.content', '#content', 'body'];
                foreach ($selectors as $selector) {
                    try {
                        $text = $crawler->filter($selector)->text();
                        if (strlen($text) > strlen($pageContent)) {
                            $pageContent = $text;
                        }
                    } catch (\Exception $e) {}
                }

                // Clean up whitespace
                $pageContent = preg_replace('/\s+/', ' ', $pageContent);
                $pageContent = trim($pageContent);

                if ($pageContent) {
                    $content .= "\n\n=== PAGE: $title ($currentUrl) ===\n" . substr($pageContent, 0, 3000);
                }

                // Find internal links for further scraping
                try {
                    $crawler->filter('a[href]')->each(function($node) use (&$toVisit, $baseHost, $url, $visited) {
                        $href = $node->attr('href');
                        if (!$href) return;

                        // Convert relative to absolute
                        if (strpos($href, 'http') !== 0) {
                            $href = rtrim($url, '/') . '/' . ltrim($href, '/');
                        }

                        $linkHost = parse_url($href, PHP_URL_HOST);

                        // Only follow internal links
                        if ($linkHost === $baseHost && !in_array($href, $visited)) {
                            // Skip common non-content pages
                            $skip = ['#', 'javascript', '.pdf', '.jpg', '.png', 'mailto', 'tel'];
                            foreach ($skip as $s) {
                                if (strpos($href, $s) !== false) return;
                            }
                            $toVisit[] = $href;
                        }
                    });
                } catch (\Exception $e) {}

                $pagesScraped++;

            } catch (\Exception $e) {
                continue;
            }
        }

        return substr($content, 0, 50000); // limit to 50k chars
    }

    private function extractBusinessName($content, $url)
    {
        // Try to extract from first page title
        preg_match('/=== PAGE: (.+?) \(/', $content, $matches);
        if (!empty($matches[1])) {
            $name = explode('|', $matches[1])[0];
            $name = explode('-', $name)[0];
            return trim($name);
        }

        // Fallback to domain name
        $host = parse_url($url, PHP_URL_HOST);
        $host = str_replace('www.', '', $host);
        return ucfirst(explode('.', $host)[0]);
    }

    public function ask(Request $request)
    {
        $agentId  = $request->input('agent_id');
        $question = $request->input('question');
        $history  = $request->input('history', []);
        $name     = $request->input('visitor_name', '');
        $email    = $request->input('visitor_email', '');

        $agent = DB::table('website_agents')->find($agentId);

        if (!$agent || $agent->status !== 'ready') {
            return response()->json(['error' => 'Agent not ready'], 404);
        }

        // Detect if issue needs escalation
        $escalationKeywords = ['urgent', 'complaint', 'refund', 'legal', 'lawsuit', 'broken', 'fraud', 'scam', 'angry', 'furious'];
        $needsEscalation = false;
        foreach ($escalationKeywords as $keyword) {
            if (stripos($question, $keyword) !== false) {
                $needsEscalation = true;
                break;
            }
        }

        $history[] = [
            'role'    => 'user',
            'content' => $question
        ];

        $systemPrompt = "You are a helpful AI Sales & Support Agent for {$agent->business_name}. 
You have been trained on the following website content. Answer questions ONLY based on this content.
If you cannot find the answer, say 'I don't have that specific information, but I can connect you with our team.'
Keep responses concise and friendly. 
If the visitor seems frustrated or has a complex issue, suggest escalating to a human agent.

WEBSITE CONTENT:
{$agent->scraped_content}";

        $response = Claude::messages()->create([
            'model'      => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'system'     => $systemPrompt,
            'messages'   => $history
        ]);

        $answer = $response->content[0]['text'];

        $history[] = [
            'role'    => 'assistant',
            'content' => $answer
        ];

        // Create support ticket if escalation needed
        $ticketId = null;
        if ($needsEscalation && $email) {
            $ticketId = DB::table('support_tickets')->insertGetId([
                'website_agent_id' => $agentId,
                'visitor_name'     => $name,
                'visitor_email'    => $email,
                'issue'            => $question,
                'priority'         => 'high',
                'status'           => 'escalated',
                'conversation'     => json_encode($history),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Send escalation email
            try {
                Mail::raw(
                    "🚨 ESCALATED TICKET #{$ticketId}\n\n" .
                    "Visitor: {$name} ({$email})\n" .
                    "Issue: {$question}\n" .
                    "Priority: HIGH\n\n" .
                    "Please respond immediately.",
                    function($m) use ($ticketId) {
                        $m->to(env('ALERT_EMAIL'))
                          ->subject("🚨 Urgent Support Ticket #{$ticketId} - Needs Immediate Attention");
                    }
                );
            } catch (\Exception $e) {}
        }

        return response()->json([
            'answer'      => $answer,
            'history'     => $history,
            'escalated'   => $needsEscalation,
            'ticket_id'   => $ticketId,
        ]);
    }

    public function saveLead(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:100',
            'agent_id' => 'required|integer',
        ]);

        $agent = DB::table('website_agents')->find($request->input('agent_id'));

        DB::table('helpdesk_leads')->insert([
            'name'       => $request->input('name'),
            'email'      => $request->input('email'),
            'issue'      => $request->input('issue', 'Website AI Agent Lead'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send email notification
        try {
            Mail::raw(
                "🎯 New Lead from Website AI Agent\n\n" .
                "Business: {$agent->business_name}\n" .
                "Name: {$request->input('name')}\n" .
                "Email: {$request->input('email')}\n" .
                "Issue: {$request->input('issue', 'General inquiry')}",
                function($m) {
                    $m->to(env('ALERT_EMAIL'))
                      ->subject('🎯 New Lead - Website AI Agent');
                }
            );
        } catch (\Exception $e) {}

        return response()->json(['success' => true]);
    }
}