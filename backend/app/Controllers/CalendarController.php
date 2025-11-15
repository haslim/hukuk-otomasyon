<?php

namespace App\Controllers;

use App\Models\Hearing;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CalendarController extends Controller
{
    public function events(Request $request, Response $response): Response
    {
        $events = Hearing::with(['case'])
            ->where('hearing_date', '>=', now())
            ->orderBy('hearing_date', 'asc')
            ->get()
            ->map(function (Hearing $hearing) {
                return [
                    'id' => $hearing->id,
                    'title' => 'Hearing: ' . $hearing->case->case_number,
                    'start' => $hearing->hearing_date->toDateTimeString(),
                    'end' => $hearing->hearing_date->addHours(1)->toDateTimeString(),
                    'type' => 'hearing',
                    'caseId' => $hearing->case_id,
                    'caseNumber' => $hearing->case->case_number,
                    'description' => $hearing->description ?? 'Court hearing for case ' . $hearing->case->case_number,
                ];
            });

        return $this->json($response, $events);
    }
}