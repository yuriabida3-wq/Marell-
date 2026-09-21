<?php

namespace App\Http\Controllers;

use App\Services\DefaulterPredictionService;

class PredictionController extends Controller
{
    public function index()
    {
        $all = DefaulterPredictionService::predictAll();

        // Only show students with score >= 40 (yellow or red)
        $predictions = array_slice(array_values(array_filter($all, fn($p) => $p['score'] >= 40)), 0, 30);

        $redCount = count(array_filter($all, fn($p) => $p['score'] >= 70));
        $yellowCount = count(array_filter($all, fn($p) => $p['score'] >= 40 && $p['score'] < 70));
        $totalAtRisk = array_sum(array_map(fn($p) => (float) $p['student']->balance, array_filter($all, fn($p) => $p['score'] >= 40)));

        return view('principal.predictions', compact('predictions', 'redCount', 'yellowCount', 'totalAtRisk'));
    }
}
