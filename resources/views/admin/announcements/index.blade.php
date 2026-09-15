@extends('layouts.admin')

@section('title', 'Announcements')
@section('heading', 'Announcements')
@section('subheading', 'The pre-header bar above the main nav. With more than one live, the bar rotates through them; hidden ones are skipped.')

@section('actions')
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="bo-btn">View home ↗</a>
    <a href="{{ route('admin.announcements.create') }}" class="bo-btn-primary">＋ New announcement</a>
@endsection

@section('content')
    <div class="bo-card">
        @if($announcements->isEmpty())
            <x-admin.empty icon="announcement" title="No announcements yet"
                           body="With none published the bar falls back to the standing delivery message. Add one to take it over.">
                <a href="{{ route('admin.announcements.create') }}" class="bo-btn-primary">＋ New announcement</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="bo-table">
                    <thead>
                        <tr>
                            <th>Message</th>
                            <th>Link</th>
                            <th class="text-right">Position</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($announcements as $announcement)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="block max-w-[420px] truncate font-medium hover:text-slate-900">
                                        {{ $announcement->message }}
                                    </a>
                                </td>

                                <td>
                                    <span class="block truncate text-[13px]">{{ $announcement->link_label ?: '—' }}</span>
                                    <span class="mt-0.5 block max-w-[240px] truncate text-[11.5px] font-normal text-slate-400">{{ $announcement->link_url ?: '/shop' }}</span>
                                </td>

                                <td class="bo-figure text-right font-normal text-slate-400">{{ $announcement->position }}</td>

                                <td>
                                    <form method="POST" action="{{ route('admin.announcements.toggle', $announcement) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="bo-badge {{ $announcement->is_active ? 'bo-badge-good' : 'bo-badge-neutral' }}"
                                                title="{{ $announcement->is_active ? 'Hide this announcement' : 'Show this announcement' }}">
                                            {{ $announcement->is_active ? 'Live' : 'Hidden' }}
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.announcements.edit', $announcement) }}" class="bo-btn bo-btn-sm">Edit</a>
                                        <button type="button" data-modal-open="delete-announcement-{{ $announcement->id }}"
                                                class="bo-btn bo-btn-sm text-rose-600 hover:border-rose-600 hover:text-rose-600" title="Delete">✕</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($announcements->isNotEmpty() && $announcements->every(fn ($announcement) => ! $announcement->is_active))
        <p class="mt-4 text-[13px] text-slate-500">
            Every announcement is hidden, so the bar is showing the standing delivery message. Set one live to take it over.
        </p>
    @endif
@endsection

@section('modals')
    @foreach($announcements as $announcement)
        <x-admin.confirm :id="'delete-announcement-'.$announcement->id"
                         :action="route('admin.announcements.destroy', $announcement)"
                         title="Delete this announcement?"
                         confirm="Delete announcement"
                         body="It is removed from the bar's rotation straight away." />
    @endforeach
@endsection
