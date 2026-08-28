<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessAvailabilityHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function index(Business $business)
    {
        abort_unless($business->owner_id === Auth::id() || $business->members()->whereKey(Auth::id())->exists(), 403);

        $days = ['monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles', 'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado', 'sunday' => 'Domingo'];
        
        $availability = $business->availabilityHours()->get()->keyBy('day_of_week');

        // Create default availability for all days
        foreach ($days as $day => $label) {
            if (!$availability->has($day)) {
                $availability[$day] = new BusinessAvailabilityHours([
                    'day_of_week' => $day,
                    'opening_time' => '09:00',
                    'closing_time' => '18:00',
                    'is_closed' => false,
                ]);
            }
        }

        return view('business.availability', [
            'business' => $business,
            'days' => $days,
            'availability' => $availability,
        ]);
    }

    public function update(Request $request, Business $business)
    {
        abort_unless($business->owner_id === Auth::id() || $business->members()->whereKey(Auth::id())->exists(), 403);

        $data = $request->validate([
            'appointments_enabled' => 'boolean',
            'hours.*.day_of_week' => 'required|string',
            'hours.*.is_closed' => 'boolean',
            'hours.*.opening_time' => 'nullable|date_format:H:i',
            'hours.*.closing_time' => 'nullable|date_format:H:i',
        ]);

        $business->update(['appointments_enabled' => $data['appointments_enabled']]);

        foreach ($data['hours'] as $hours) {
            $is_closed = $hours['is_closed'] ?? false;
            
            BusinessAvailabilityHours::updateOrCreate(
                ['business_id' => $business->id, 'day_of_week' => $hours['day_of_week']],
                [
                    'opening_time' => $is_closed ? null : $hours['opening_time'],
                    'closing_time' => $is_closed ? null : $hours['closing_time'],
                    'is_closed' => $is_closed,
                ]
            );
        }

        return redirect()->route('availability.index', $business)->with('success', 'Horarios actualizados correctamente.');
    }
}
