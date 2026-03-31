<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use App\Models\User;


class GoogleCalendarService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $this->client->addScope(Calendar::CALENDAR_READONLY);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    public function index(GoogleCalendarService $calendarService)
{
    $user = auth()->user();
    $expenses = $user->expenses()->orderBy('expense_date', 'desc')->get();
    $totalExpenses = $user->getTotalExpenses();
    $remainingBudget = $user->getRemainingBudget();
    $budgetPercentage = $user->getBudgetPercentage();
    $cooldownDays = $user->getBudgetChangeCooldownDays();

    $categoryTotals = $user->expenses()
        ->select('category', DB::raw('SUM(amount) as total'))
        ->groupBy('category')
        ->pluck('total', 'category')
        ->toArray();

    $chartData = [
        'Food' => $categoryTotals['Food'] ?? 0,
        'Transport' => $categoryTotals['Transport'] ?? 0,
        'Utilities' => $categoryTotals['Utilities'] ?? 0,
        'Entertainment' => $categoryTotals['Entertainment'] ?? 0,
        'Others' => $categoryTotals['Others'] ?? 0,
    ];

    // Get upcoming events if Google Calendar connected
    $events = [];
    if ($user->google_calendar_token) {
        $events = $calendarService->getUserCalendarEvents($user);
    }

    return view('expenses.index', compact('expenses', 'totalExpenses', 'remainingBudget', 'budgetPercentage', 'cooldownDays', 'chartData', 'user', 'events'));
}

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate($code)
    {
        return $this->client->fetchAccessTokenWithAuthCode($code);
    }

    public function setAccessToken(User $user)
    {
        $token = json_decode($user->google_calendar_token, true);
        if ($token) {
            $this->client->setAccessToken($token);
            if ($this->client->isAccessTokenExpired()) {
                $this->refreshToken($user);
            }
        }
        return $this->client->getAccessToken() ? true : false;
    }

    protected function refreshToken(User $user)
    {
        $token = json_decode($user->google_calendar_token, true);
        $this->client->setAccessToken($token);
        $newToken = $this->client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
        if (!isset($newToken['error'])) {
            $newToken = array_merge($token, $newToken);
            $user->google_calendar_token = json_encode($newToken);
            $user->save();
        }
    }

    public function getCalendarList(User $user)
    {
        if (!$this->setAccessToken($user)) {
            return null;
        }
        $service = new Calendar($this->client);
        return $service->calendarList->listCalendarList();
    }

    public function getPrimaryCalendar(User $user)
    {
        $calendars = $this->getCalendarList($user);
        if ($calendars) {
            foreach ($calendars->getItems() as $calendar) {
                if ($calendar->getPrimary()) {
                    return $calendar;
                }
            }
        }
        return null;
    }

    public function getPrimaryCalendarEmbedUrl(User $user)
    {
        $primary = $this->getPrimaryCalendar($user);
        if ($primary) {
            $calendarId = urlencode($primary->getId());
            return "https://calendar.google.com/calendar/embed?src={$calendarId}&ctz=Asia/Manila";
        }
        return null;
    }

    public function getUserCalendarEvents(User $user, $maxResults = 10)
    {
        if (!$this->setAccessToken($user)) {
            return null;
        }

        $service = new Calendar($this->client);
        $calendarId = 'primary';
        $optParams = [
            'maxResults' => $maxResults,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        ];
        $results = $service->events->listEvents($calendarId, $optParams);
        return $results->getItems();
    }
}
