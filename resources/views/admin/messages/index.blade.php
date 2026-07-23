@extends('layouts.admin')

@section('title', 'Messages')
@section('heading', 'Messages')
@section('subheading', $unread > 0 ? $unread.' unread of '.number_format($messages->total()) : 'Inbox clear · '.number_format($messages->total()).' total')

@section('content')
    <div class="ad-card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-5 py-4">
            <div class="min-w-[200px] flex-1">
                <label for="q" class="ad-label">Search</label>
                <input id="q" name="q" value="{{ request('q') }}" placeholder="Name, email, subject or body…" class="ad-input">
            </div>
            <div class="w-[150px]">
                <label for="filter" class="ad-label">Show</label>
                <select id="filter" name="filter" class="ad-input">
                    @foreach(['' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('filter') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="ad-btn-primary">Filter</button>
            @if(request()->hasAny(['q', 'filter']))
                <a href="{{ route('admin.messages.index') }}" class="ad-btn">Clear</a>
            @endif
        </form>

        @if($messages->isEmpty())
            <x-admin.empty icon="✉" title="No messages match"
                           body="Everything sent through the storefront contact form lands here." />
        @else
            <div class="flex flex-col divide-y divide-slate-100">
                @foreach($messages as $message)
                    <a href="{{ route('admin.messages.show', $message) }}"
                       class="flex items-start gap-4 px-5 py-4 transition-colors hover:bg-slate-50">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $message->read_at ? 'bg-transparent' : 'bg-slate-900' }}"
                              title="{{ $message->read_at ? 'Read' : 'Unread' }}"></span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                                <span class="{{ $message->read_at ? 'font-normal' : 'font-semibold' }} text-[14px]">{{ $message->name }}</span>
                                <span class="text-[11.5px] font-normal text-slate-400">{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="mt-0.5 truncate text-[12.5px] font-normal text-slate-400">{{ $message->email }}</div>
                            @if($message->subject)
                                <div class="mt-1.5 text-[13px] {{ $message->read_at ? 'font-normal text-slate-600' : 'font-medium text-slate-800' }}">{{ $message->subject }}</div>
                            @endif
                            <p class="mt-1 line-clamp-1 text-[13px] font-normal text-slate-500">{{ $message->message }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            @include('partials.admin.pagination', ['paginator' => $messages])
        @endif
    </div>
@endsection
