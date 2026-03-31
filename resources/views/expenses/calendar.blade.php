@extends('layouts.app')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&family=DM+Mono:wght@400;500&display=swap');

    .cal-root {
        font-family: 'DM Sans', sans-serif;
        --cal-bg: #0f1117;
        --cal-surface: #181c27;
        --cal-surface-2: #1e2435;
        --cal-border: rgba(255,255,255,0.07);
        --cal-accent: #4f8ef7;
        --cal-accent-2: #7c5cfc;
        --cal-text: #e8eaf0;
        --cal-muted: #6b7280;
        --cal-success: #34d399;
        --cal-warn: #fbbf24;
        --cal-radius: 16px;
        --cal-radius-sm: 10px;
        color: var(--cal-text);
    }

    /* Back link */
    .cal-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        color: var(--cal-muted);
        text-decoration: none;
        margin-bottom: 28px;
        letter-spacing: 0.01em;
        transition: color 0.2s;
    }
    .cal-back:hover { color: var(--cal-text); }
    .cal-back svg { transition: transform 0.2s; }
    .cal-back:hover svg { transform: translateX(-3px); }

    /* Page header */
    .cal-header {
        margin-bottom: 32px;
    }
    .cal-header h1 {
        font-size: 26px;
        font-weight: 600;
        letter-spacing: -0.03em;
        margin: 0 0 4px;
        color: var(--cal-text);
    }
    .cal-header p {
        font-size: 13px;
        color: var(--cal-muted);
        margin: 0;
    }

    /* Grid */
    .cal-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }
    @media (min-width: 1024px) {
        .cal-grid { grid-template-columns: 1.1fr 0.9fr; }
    }

    /* Cards */
    .cal-card {
        background: var(--cal-surface);
        border: 1px solid var(--cal-border);
        border-radius: var(--cal-radius);
        overflow: hidden;
        position: relative;
    }
    .cal-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(79,142,247,0.4), transparent);
    }

    .cal-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 20px 24px 18px;
        border-bottom: 1px solid var(--cal-border);
    }
    .cal-card-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .cal-card-icon.blue { background: rgba(79,142,247,0.15); color: var(--cal-accent); }
    .cal-card-icon.purple { background: rgba(124,92,252,0.15); color: var(--cal-accent-2); }

    .cal-card-title {
        font-size: 14px;
        font-weight: 600;
        letter-spacing: -0.01em;
        color: var(--cal-text);
        margin: 0;
    }
    .cal-card-sub {
        font-size: 11px;
        color: var(--cal-muted);
        margin-top: 1px;
    }

    /* Iframe wrapper */
    .cal-iframe-wrap {
        padding: 20px 24px 24px;
    }
    .cal-iframe-inner {
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 24px rgba(0,0,0,0.3);
    }
    .cal-iframe-inner iframe {
        display: block;
        width: 100%;
        height: 420px;
        border: none;
    }

    /* Error / warning states */
    .cal-notice {
        margin: 20px 24px 24px;
        padding: 14px 16px;
        border-radius: var(--cal-radius-sm);
        font-size: 13px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .cal-notice.warn {
        background: rgba(251,191,36,0.08);
        border: 1px solid rgba(251,191,36,0.2);
        color: var(--cal-warn);
    }
    .cal-notice a {
        color: var(--cal-accent);
        text-decoration: none;
        font-weight: 500;
    }
    .cal-notice a:hover { text-decoration: underline; }

    /* Events list */
    .cal-events {
        padding: 8px 0 4px;
        max-height: 520px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--cal-border) transparent;
    }

    .cal-event-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 14px 24px;
        border-bottom: 1px solid var(--cal-border);
        transition: background 0.15s;
        cursor: default;
        position: relative;
    }
    .cal-event-item:last-child { border-bottom: none; }
    .cal-event-item:hover { background: var(--cal-surface-2); }

    .cal-event-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--cal-accent);
        flex-shrink: 0;
        margin-top: 6px;
        box-shadow: 0 0 6px rgba(79,142,247,0.5);
    }
    .cal-event-dot.allday {
        background: var(--cal-accent-2);
        box-shadow: 0 0 6px rgba(124,92,252,0.5);
    }

    .cal-event-body { flex: 1; min-width: 0; }

    .cal-event-title {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--cal-text);
        margin: 0 0 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cal-event-time {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: var(--cal-muted);
        margin-bottom: 4px;
        letter-spacing: 0.02em;
    }

    .cal-event-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 500;
        padding: 2px 7px;
        border-radius: 999px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .cal-event-badge.timed {
        background: rgba(79,142,247,0.12);
        color: var(--cal-accent);
        border: 1px solid rgba(79,142,247,0.2);
    }
    .cal-event-badge.allday {
        background: rgba(124,92,252,0.12);
        color: var(--cal-accent-2);
        border: 1px solid rgba(124,92,252,0.2);
    }

    .cal-event-desc {
        font-size: 12px;
        color: var(--cal-muted);
        margin: 6px 0 0;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Empty state */
    .cal-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 48px 24px;
        text-align: center;
        gap: 10px;
    }
    .cal-empty-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: var(--cal-surface-2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
        color: var(--cal-muted);
    }
    .cal-empty h3 {
        font-size: 14px;
        font-weight: 600;
        color: var(--cal-text);
        margin: 0;
    }
    .cal-empty p {
        font-size: 12px;
        color: var(--cal-muted);
        margin: 0;
    }

    /* Connect CTA */
    .cal-connect {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 40px 24px;
        gap: 12px;
    }
    .cal-connect-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: rgba(79,142,247,0.1);
        border: 1px solid rgba(79,142,247,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cal-accent);
        margin-bottom: 4px;
    }
    .cal-connect h3 {
        font-size: 15px;
        font-weight: 600;
        color: var(--cal-text);
        margin: 0;
    }
    .cal-connect p {
        font-size: 12.5px;
        color: var(--cal-muted);
        margin: 0;
        max-width: 220px;
        line-height: 1.6;
    }
    .cal-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        margin-top: 4px;
        background: var(--cal-accent);
        color: #fff;
        box-shadow: 0 2px 12px rgba(79,142,247,0.25);
    }
    .cal-btn:hover {
        background: #6fa3ff;
        box-shadow: 0 4px 20px rgba(79,142,247,0.4);
        transform: translateY(-1px);
    }

    /* Count badge */
    .cal-count {
        margin-left: auto;
        font-size: 11px;
        font-weight: 600;
        color: var(--cal-muted);
        background: var(--cal-surface-2);
        border: 1px solid var(--cal-border);
        padding: 2px 8px;
        border-radius: 999px;
    }
</style>

<div class="cal-root">

    {{-- Back link --}}
    <a href="{{ route('expenses.index') }}" class="cal-back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Back to Dashboard
    </a>

    {{-- Page heading --}}
    <div class="cal-header">
        <h1>Calendar</h1>
        <p>Your schedule and upcoming events</p>
    </div>

    {{-- Two-column grid --}}
    <div class="cal-grid">

        {{-- Left: Embedded Calendar --}}
        <div class="cal-card">
            <div class="cal-card-header">
                <div class="cal-card-icon blue">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <div class="cal-card-title">My Google Calendar</div>
                    <div class="cal-card-sub">Live view from your account</div>
                </div>
            </div>

            @if($embedUrl)
                <div class="cal-iframe-wrap">
                    <div class="cal-iframe-inner">
                        <iframe src="{{ $embedUrl }}"
                                frameborder="0"
                                scrolling="no"
                                title="Google Calendar">
                        </iframe>
                    </div>
                </div>
            @else
                <div class="cal-notice warn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Unable to load calendar embed. Please check your connection.
                </div>
            @endif
        </div>

        {{-- Right: Upcoming Events --}}
        <div class="cal-card">
            <div class="cal-card-header">
                <div class="cal-card-icon purple">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div>
                    <div class="cal-card-title">Upcoming Events</div>
                    <div class="cal-card-sub">What's ahead</div>
                </div>
                @if($events !== null && count($events) > 0)
                    <span class="cal-count">{{ count($events) }}</span>
                @endif
            </div>

            @if($events !== null)
                @if(count($events) > 0)
                    <div class="cal-events">
                        @foreach($events as $event)
                            @php
                                $isAllDay = !$event->getStart()->getDateTime();
                                $start = $isAllDay
                                    ? null
                                    : \Carbon\Carbon::parse($event->getStart()->getDateTime());
                                $end = (!$isAllDay && $event->getEnd() && $event->getEnd()->getDateTime())
                                    ? \Carbon\Carbon::parse($event->getEnd()->getDateTime())
                                    : null;
                            @endphp
                            <div class="cal-event-item">
                                <div class="cal-event-dot {{ $isAllDay ? 'allday' : '' }}"></div>
                                <div class="cal-event-body">
                                    <div class="cal-event-title">{{ $event->getSummary() }}</div>

                                    @if($isAllDay)
                                        <div class="cal-event-time">
                                            {{ \Carbon\Carbon::parse($event->getStart()->getDate())->format('M d, Y') }}
                                        </div>
                                        <span class="cal-event-badge allday">All-day</span>
                                    @else
                                        <div class="cal-event-time">
                                            {{ $start->format('M d, Y') }}
                                            &nbsp;·&nbsp;
                                            {{ $start->format('h:i A') }}{{ $end ? ' – ' . $end->format('h:i A') : '' }}
                                        </div>
                                        <span class="cal-event-badge timed">Scheduled</span>
                                    @endif

                                    @if($event->getDescription())
                                        <p class="cal-event-desc">{{ $event->getDescription() }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="cal-empty">
                        <div class="cal-empty-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <h3>No upcoming events</h3>
                        <p>Your schedule looks clear for now.</p>
                    </div>
                @endif
            @else
                <div class="cal-connect">
                    <div class="cal-connect-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 2H3v16h5v4l4-4h5l4-4V2zm-10 9V7m4 4V7"/>
                        </svg>
                    </div>
                    <h3>Connect Google Calendar</h3>
                    <p>Link your account to see events and stay on top of your schedule.</p>
                    <a href="{{ route('google.auth') }}" class="cal-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        Connect Calendar
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
