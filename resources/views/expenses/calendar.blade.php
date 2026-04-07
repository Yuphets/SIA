@extends('layouts.app')

@section('content')
<style>
    .gc-wrap {
        max-width: 1100px;
        margin: 0 auto;
        display: grid;
        gap: 16px;
    }

    .gc-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .gc-title {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #0b2b4f;
    }

    .gc-sub {
        margin: 4px 0 0;
        color: #48607a;
        font-size: 0.95rem;
    }

    .gc-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .gc-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 10px;
        padding: 10px 14px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.92rem;
        border: 1px solid transparent;
    }

    .gc-btn-primary {
        background: #0b2b4f;
        color: #fff;
    }

    .gc-btn-primary:hover { background: #154372; }

    .gc-btn-outline {
        border-color: #bfd0e3;
        color: #0b2b4f;
        background: #f7fbff;
    }

    .gc-btn-outline:hover { background: #ebf4ff; }

    .gc-alert {
        border-radius: 12px;
        padding: 12px 14px;
        border: 1px solid #f4df9a;
        background: #fff8de;
        color: #6a5400;
        font-size: 0.92rem;
    }

    .gc-card {
        background: #fff;
        border: 1px solid #e4edf7;
        border-radius: 14px;
        overflow: hidden;
    }

    .gc-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px;
        border-bottom: 1px solid #e8eef5;
        background: #f9fcff;
    }

    .gc-card-head h2 {
        margin: 0;
        font-size: 1rem;
        color: #0b2b4f;
    }

    .gc-pill {
        font-size: 0.78rem;
        color: #375573;
        background: #e9f3ff;
        border: 1px solid #cce2fb;
        border-radius: 999px;
        padding: 4px 9px;
        font-weight: 600;
    }

    .gc-list {
        display: grid;
        gap: 0;
    }

    .gc-item {
        display: grid;
        grid-template-columns: 10px 1fr;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #eff4fa;
    }

    .gc-item:last-child { border-bottom: none; }

    .gc-dot {
        width: 10px;
        height: 10px;
        margin-top: 5px;
        border-radius: 999px;
        background: #0b2b4f;
    }

    .gc-dot-all { background: #7a4b2e; }

    .gc-name {
        margin: 0;
        font-weight: 700;
        color: #0f2f53;
        font-size: 0.95rem;
    }

    .gc-time {
        margin-top: 4px;
        color: #405971;
        font-size: 0.88rem;
    }

    .gc-badge {
        margin-left: 8px;
        font-size: 0.72rem;
        font-weight: 700;
        color: #6e411e;
        background: #f9e9dd;
        border: 1px solid #efcfb8;
        border-radius: 999px;
        padding: 2px 7px;
        vertical-align: middle;
    }

    .gc-desc {
        margin: 8px 0 0;
        color: #607389;
        font-size: 0.86rem;
        line-height: 1.45;
        word-break: break-word;
    }

    .gc-empty {
        padding: 28px 16px;
        text-align: center;
        color: #58718b;
        font-size: 0.92rem;
    }
</style>

<div class="gc-wrap">
    <div class="gc-top">
        <div>
            <h1 class="gc-title">Google Calendar</h1>
            <p class="gc-sub">Upcoming events are loaded directly from your connected account.</p>
        </div>

        <div class="gc-actions">
            <a href="{{ route('expenses.index') }}" class="gc-btn gc-btn-outline">Back to Dashboard</a>

            @if($isConnected)
                <a href="{{ $calendarWebUrl }}" target="_blank" rel="noopener" class="gc-btn gc-btn-primary">Open My Google Calendar</a>
                <a href="{{ route('google.auth') }}" class="gc-btn gc-btn-outline">Reconnect</a>
            @else
                <a href="{{ route('google.auth') }}" class="gc-btn gc-btn-primary">Connect Google Calendar</a>
            @endif
        </div>
    </div>

    @if($isConnected && $connectionIssue)
        <div class="gc-alert">
            Calendar connection needs refresh. Use <strong>Reconnect</strong> to renew your Google access token.
        </div>
    @endif

    <div class="gc-card">
        <div class="gc-card-head">
            <h2>Upcoming Events</h2>
            @if($events !== null)
                <span class="gc-pill">{{ count($events) }} loaded</span>
            @endif
        </div>

        @if(!$isConnected)
            <div class="gc-empty">Connect your Google account to load events here.</div>
        @elseif($events === null)
            <div class="gc-empty">Unable to read events right now. Reconnect and try again.</div>
        @elseif(count($events) === 0)
            <div class="gc-empty">No upcoming events found.</div>
        @else
            <div class="gc-list">
                @foreach($events as $event)
                    @php
                        $isAllDay = !$event->getStart()->getDateTime();
                        $startDate = $isAllDay
                            ? \Carbon\Carbon::parse($event->getStart()->getDate())
                            : \Carbon\Carbon::parse($event->getStart()->getDateTime());
                        $endDate = (!$isAllDay && $event->getEnd() && $event->getEnd()->getDateTime())
                            ? \Carbon\Carbon::parse($event->getEnd()->getDateTime())
                            : null;
                    @endphp

                    <div class="gc-item">
                        <span class="gc-dot {{ $isAllDay ? 'gc-dot-all' : '' }}"></span>
                        <div>
                            <p class="gc-name">
                                {{ $event->getSummary() ?: 'Untitled Event' }}
                                @if($isAllDay)
                                    <span class="gc-badge">ALL DAY</span>
                                @endif
                            </p>

                            <div class="gc-time">
                                @if($isAllDay)
                                    {{ $startDate->format('M d, Y') }}
                                @else
                                    {{ $startDate->format('M d, Y h:i A') }}
                                    @if($endDate)
                                        to {{ $endDate->format('h:i A') }}
                                    @endif
                                @endif
                            </div>

                            @if($event->getDescription())
                                <p class="gc-desc">{{ \Illuminate\Support\Str::limit($event->getDescription(), 180) }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
