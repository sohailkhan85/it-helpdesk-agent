<?php

return [
    'company_name'  => env('BRAND_COMPANY_NAME', 'IT Helpdesk Assistant'),
    'tagline'       => env('BRAND_TAGLINE', 'Powered by AI • Always Online'),
    'primary_color' => env('BRAND_PRIMARY_COLOR', '#1d4ed8'),
    'logo_emoji'    => env('BRAND_LOGO_EMOJI', '🤖'),
    'welcome_msg'   => env('BRAND_WELCOME_MSG', 'Hello! I am your IT Helpdesk Assistant. I specialize in networking, surveillance systems, and IT infrastructure. How can I help you today?'),
    'placeholder'   => env('BRAND_PLACEHOLDER', 'Describe your IT issue...'),
    'system_prompt' => env('BRAND_SYSTEM_PROMPT', 'You are an expert IT Helpdesk Assistant for a managed IT services company. You specialize in networking, surveillance systems, fiber optics, and general IT infrastructure support. Give concise, professional answers. If you need more info to diagnose an issue, ask one question at a time.'),
];