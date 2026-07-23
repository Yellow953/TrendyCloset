@extends('layouts.admin')

@section('title', $message->subject ?: 'Message from '.$message->name)
@section('heading', $message->subject ?: 'Enquiry')
@section('subheading', 'Received '.$message->created_at->format('j F Y \a\t H:i'))

@section('breadcrumb')
    <a href="{{ route('admin.messages.index') }}" class="hover:text-slate-900">Messages</a>
    <span class="text-slate-200">/</span>
    <span class="text-slate-600">{{ $message->name }}</span>
@endsection

@section('actions')
    <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'your enquiry')) }}" class="ad-btn-primary">Reply by email</a>
    <form method="POST" action="{{ route('admin.messages.unread', $message) }}">
        @csrf @method('PATCH')
        <button type="submit" class="ad-btn">Mark unread</button>
    </form>
    <button type="button" data-modal-open="delete-message" class="ad-btn text-rose-600 hover:border-rose-600 hover:text-rose-600">Delete</button>
@endsection

@section('content')
    <div class="mx-auto max-w-[760px]">
        <div class="ad-card">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[15px] font-medium text-slate-900">
                        {{ strtoupper(mb_substr($message->name, 0, 1)) }}
                    </span>
                    <div>
                        <div class="text-[15px] font-medium">{{ $message->name }}</div>
                        <a href="mailto:{{ $message->email }}" class="text-[13px] font-normal text-slate-400 hover:text-slate-900">{{ $message->email }}</a>
                    </div>
                </div>
                <span class="ad-badge ad-badge-good">Read</span>
            </div>

            <div class="px-6 py-6">
                @if($message->subject)
                    <div class="ad-eyebrow">Subject</div>
                    <p class="mt-1 mb-5 text-[16px] font-medium">{{ $message->subject }}</p>
                @endif

                <div class="ad-eyebrow">Message</div>
                <p class="mt-2 text-[14.5px] leading-relaxed font-normal whitespace-pre-line text-slate-600">{{ $message->message }}</p>
            </div>

            <div class="border-t border-slate-100 bg-slate-50 px-6 py-4 text-[12.5px] font-normal text-slate-400">
                Sent {{ $message->created_at->format('l, j F Y \a\t H:i') }} · read {{ $message->read_at?->diffForHumans() }}
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <x-admin.confirm id="delete-message"
                     :action="route('admin.messages.destroy', $message)"
                     title="Delete this message?"
                     confirm="Delete message"
                     body="It is removed from the inbox for good. Reply first if you still need to." />
@endsection
