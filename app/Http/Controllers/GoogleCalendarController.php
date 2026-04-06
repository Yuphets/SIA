<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleCalendarService;
use App\Models\ActivityLog;

class GoogleCalendarController extends Controller
{
    public function redirectToGoogle(GoogleCalendarService $calendarService)
    {
        return redirect($calendarService->getAuthUrl());
    }

    public function disconnect()
{
    $user = auth()->user();
    
    // Clear the stored token
    $user->google_calendar_token = null;
    $user->save();
    
    ActivityLog::log(
        $user->id,
        $user->name,
        'GOOGLE_CALENDAR_DISCONNECT',
        'Disconnected Google Calendar'
    );
    
    return redirect()->route('expenses.index')->with('success', 'Google Calendar disconnected successfully.');
}

    public function handleGoogleCallback(Request $request, GoogleCalendarService $calendarService)
    {
        $code = $request->get('code');
        if (!$code) {
            return redirect()->route('expenses.index')->with('error', 'Google authorization failed.');
        }

        $token = $calendarService->authenticate($code);
        if (isset($token['error'])) {
            return redirect()->route('expenses.index')->with('error', 'Google authorization error: ' . $token['error']);
        }

        $user = auth()->user();
        $user->google_calendar_token = json_encode($token);
        $user->save();

        ActivityLog::log($user->id, $user->name, 'GOOGLE_CALENDAR_CONNECT', 'Connected Google Calendar');

        return redirect()->route('expenses.index')->with('success', 'Google Calendar connected successfully!');
    }

    public function viewCalendar(GoogleCalendarService $calendarService)
    {
        $user = auth()->user();
        $embedUrl = $calendarService->getPrimaryCalendarEmbedUrl($user);
        $events = $calendarService->getUserCalendarEvents($user);

        return view('expenses.calendar', compact('embedUrl', 'events'));
    }

    
}
