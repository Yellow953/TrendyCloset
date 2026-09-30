<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpeningHour;
use Illuminate\Http\Request;

class OpeningHourController extends Controller
{
    public function edit()
    {
        return view('admin.hours', [
            'active' => 'hours',
            'week' => OpeningHour::week(),
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];

        foreach (array_keys(OpeningHour::DAYS) as $day) {
            $open = ! $request->boolean("days.$day.is_closed");
            $rules["days.$day.is_closed"] = ['nullable', 'boolean'];
            $rules["days.$day.opens_at"] = [$open ? 'required' : 'nullable', 'date_format:H:i'];
            $rules["days.$day.closes_at"] = [$open ? 'required' : 'nullable', 'date_format:H:i', ...($open ? ["after:days.$day.opens_at"] : [])];
        }

        $request->validate($rules, [
            'days.*.opens_at.required' => 'Set an opening time, or mark the day closed.',
            'days.*.closes_at.required' => 'Set a closing time, or mark the day closed.',
            'days.*.closes_at.after' => 'Closing time must be after opening time.',
        ]);

        foreach (array_keys(OpeningHour::DAYS) as $day) {
            OpeningHour::updateOrCreate(['day' => $day], [
                'opens_at' => $request->input("days.$day.opens_at") ?: null,
                'closes_at' => $request->input("days.$day.closes_at") ?: null,
                'is_closed' => $request->boolean("days.$day.is_closed"),
            ]);
        }

        return back()->with('status', 'Opening hours saved.');
    }
}
