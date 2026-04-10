<?php

namespace App\Http\Controllers;

use App\Exceptions\CesgaApiException;
use App\Services\CesgaApiService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(CesgaApiService $api): View
    {
        try {
            $stats = $api->getProteinStats();
            $samples = $api->getSampleSequences();
        } catch (CesgaApiException) {
            $stats = null;
            $samples = [];
        }

        return view('home', compact('stats', 'samples'));
    }
}
