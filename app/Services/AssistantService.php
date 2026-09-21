<?php

namespace App\Services;

use App\Models\AssistantChat;

class AssistantService
{
    /**
     * Intent rules — ordered by priority.
     * Each rule: keywords (all must appear) → intent + reply + suggested action link.
     */
    public const INTENTS = [
        [
            'intent'   => 'greeting',
            'keywords' => ['hi', 'hello', 'hey', 'habari', 'jambo'],
            'reply'    => "Hello! I'm Marell Helper. I can help you with fees, results, timetable, admissions, and more. What do you need?",
            'actions'  => [],
        ],
        [
            'intent'   => 'help',
            'keywords' => ['help', 'what can you do', 'menu', 'options'],
            'reply'    => "I can help with:\n\n• 💰 Fee balance & payment\n• 📊 Exam results\n• 📅 Timetable\n• 📝 Admissions\n• 📞 Contact school\n• 🕊️ Report a concern\n\nJust type what you need.",
            'actions'  => [
                ['label' => 'Pay Fees', 'url' => '/pay'],
                ['label' => 'Check Results', 'url' => '/results'],
            ],
        ],
        [
            'intent'   => 'pay_fees',
            'keywords' => ['pay', 'mpesa', 'm-pesa', 'lipa', 'pesa'],
            'reply'    => "You can pay fees anytime via M-Pesa STK Push. It works 24/7 — even at midnight. Enter your child's ADM number and amount, approve on your phone, get instant SMS + PDF receipt.",
            'actions'  => [
                ['label' => 'Pay Now', 'url' => '/pay'],
                ['label' => 'View Fee Structure', 'url' => '/fees'],
            ],
        ],
        [
            'intent'   => 'balance',
            'keywords' => ['balance', 'salio', 'deni', 'owe'],
            'reply'    => "To check your child's fee balance:\n\n1. Log into the Parent Portal with your phone number\n2. We send a 6-digit code via SMS\n3. See balance, statements, and receipts instantly\n\nNo password needed.",
            'actions'  => [
                ['label' => 'Parent Portal Login', 'url' => '/parent/login'],
                ['label' => 'Check by ADM', 'url' => '/pay'],
            ],
        ],
        [
            'intent'   => 'results',
            'keywords' => ['result', 'matokeo', 'score', 'marks', 'grade'],
            'reply'    => "Enter your child's ADM number to see exam results. If balance is above KES 5,000, you'll need to clear it first to unlock the full result slip.",
            'actions'  => [
                ['label' => 'Check Results', 'url' => '/results'],
            ],
        ],
        [
            'intent'   => 'timetable',
            'keywords' => ['timetable', 'schedule', 'period', 'lesson'],
            'reply'    => "You can view any class timetable online — pick a class and see the full weekly schedule with teachers.",
            'actions'  => [
                ['label' => 'View Timetable', 'url' => '/timetable'],
            ],
        ],
        [
            'intent'   => 'admissions',
            'keywords' => ['admission', 'apply', 'join', 'enroll', 'enrol', 'register'],
            'reply'    => "Applications are open. Fill the online form (takes 3 minutes), and we'll call you within 48 hours. You can apply from your phone at any time.",
            'actions'  => [
                ['label' => 'Apply Now', 'url' => '/admissions'],
                ['label' => 'Fee Structure', 'url' => '/fees'],
            ],
        ],
        [
            'intent'   => 'fees_structure',
            'keywords' => ['fee', 'structure', 'how much', 'cost'],
            'reply'    => "Our fee structure is transparent. Fees can be paid in any number of installments. Baby Class through Grade 9.",
            'actions'  => [
                ['label' => 'View Fee Structure', 'url' => '/fees'],
                ['label' => 'Pay Fees', 'url' => '/pay'],
            ],
        ],
        [
            'intent'   => 'contact',
            'keywords' => ['contact', 'call', 'phone', 'email', 'reach', 'location', 'where'],
            'reply'    => "You can call us on +254 700 000 000, email info@marell.ac.ke, or visit us on Kanduyi Road, Bungoma. Office hours: Mon–Fri 7:30AM–5PM, Sat 8AM–1PM.",
            'actions'  => [
                ['label' => 'Contact Form', 'url' => '/contact'],
            ],
        ],
        [
            'intent'   => 'report',
            'keywords' => ['report', 'complain', 'concern', 'corruption', 'fraud', 'whistle'],
            'reply'    => "You can send an anonymous report directly to the Director. No name, no IP, no trace. Use it for any concern — finance, staff, safety.",
            'actions'  => [
                ['label' => 'Report Anonymously', 'url' => '/report'],
            ],
        ],
        [
            'intent'   => 'receipt',
            'keywords' => ['receipt', 'risiti', 'verify', 'proof'],
            'reply'    => "Every receipt has a QR code you can scan to verify online. If it's genuine, you'll see the full details. If it's fake, it won't verify.",
            'actions'  => [
                ['label' => 'Verify a Receipt', 'url' => '/verify-receipt/REC-2026-00003'],
            ],
        ],
        [
            'intent'   => 'news',
            'keywords' => ['news', 'event', 'announcement', 'update'],
            'reply'    => "See the latest news and events from Marell Academy.",
            'actions'  => [
                ['label' => 'View News', 'url' => '/news'],
            ],
        ],
        [
            'intent'   => 'staff_login',
            'keywords' => ['staff', 'teacher login', 'portal', 'employee'],
            'reply'    => "Staff login is available at /login. Use your school email and password. Contact the Director if you've forgotten your credentials.",
            'actions'  => [
                ['label' => 'Staff Login', 'url' => '/login'],
            ],
        ],
    ];

    public static function reply(string $message, string $sessionKey, ?string $ip = null): array
    {
        $text = strtolower(trim($message));
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        $best = null;
        $bestScore = 0;

        foreach (self::INTENTS as $rule) {
            $score = 0;
            foreach ($rule['keywords'] as $kw) {
                if (str_contains($text, $kw)) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $rule;
            }
        }

        if (!$best || $bestScore === 0) {
            $best = [
                'intent'  => 'unknown',
                'reply'   => "I'm not sure about that yet. Here are the popular things I can help with:",
                'actions' => [
                    ['label' => 'Pay Fees', 'url' => '/pay'],
                    ['label' => 'Check Results', 'url' => '/results'],
                    ['label' => 'Parent Portal', 'url' => '/parent/login'],
                    ['label' => 'Contact Us', 'url' => '/contact'],
                ],
            ];
        }

        // Log it
        try {
            AssistantChat::create([
                'session_key'    => $sessionKey,
                'user_message'   => substr($message, 0, 500),
                'bot_reply'      => substr($best['reply'], 0, 2000),
                'matched_intent' => $best['intent'],
                'ip'             => $ip,
            ]);
        } catch (\Throwable $e) {
            // silent
        }

        return [
            'reply'   => $best['reply'],
            'intent'  => $best['intent'],
            'actions' => $best['actions'],
        ];
    }
}
