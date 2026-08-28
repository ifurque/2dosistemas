<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\AppointmentConfirmation;

class ManagementController extends Controller
{
    public function index(Business $business)
    {
        $this->ownerOnly($business);
        return view('management.index', [
            'business' => $business->load(['products', 'members']),
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'appointments' => $business->appointments()->with(['client', 'product'])->latest('appointment_date')->limit(12)->get(),
            'inquiries' => $business->inquiries()->with('client')->latest()->limit(12)->get(),
            'stockMovements' => $business->stockMovements()->with(['product', 'creator'])->latest()->limit(12)->get(),
            'socialLinks' => $business->socialLinks()->get(),
        ]);
    }

    public function appointment(Request $request, Business $business)
    {
        $rules = ['product_id' => ['nullable', 'exists:products,id'], 'appointment_date' => ['required', 'date', 'after_or_equal:today'], 'start_time' => ['required', 'date_format:H:i'], 'notes' => ['nullable', 'string', 'max:500']];
        if ($request->user()) {
            $data = $request->validate($rules);
            $data += ['client_id' => $request->user()->id, 'status' => 'pending'];
            $recipient = $request->user()->email;
            $name = $request->user()->name;
        } else {
            $data = $request->validate($rules + ['guest_name' => ['required', 'string', 'max:100'], 'guest_email' => ['required', 'email', 'max:180']]);
            $data += ['guest_name' => $data['guest_name'], 'guest_email' => $data['guest_email'], 'status' => 'awaiting_confirmation'];
            $recipient = $data['guest_email'];
            $name = $data['guest_name'];
        }
        $appointment = $business->appointments()->create($data + ['confirmation_token' => Str::random(64)]);
        $appointment->setAttribute('guest_name', $name);
        Mail::to($recipient)->send(new AppointmentConfirmation($appointment));
        return back()->with('success', 'Revisa tu correo para confirmar el turno.');
    }

    public function confirmAppointment(string $token)
    {
        $appointment = \App\Models\Appointment::where('confirmation_token', $token)->firstOrFail();
        $appointment->update(['status' => 'pending', 'confirmed_at' => now(), 'confirmation_token' => null]);
        return redirect()->route('business.show', $appointment->business)->with('success', 'Turno confirmado. El emprendimiento recibió tu solicitud.');
    }

    public function appointmentStatus(Request $request, Business $business, $appointment)
    {
        $this->ownerOnly($business);
        $data = $request->validate(['status' => ['required', 'in:pending,confirmed,cancelled,completed']]);
        $business->appointments()->findOrFail($appointment)->update($data);
        return back()->with('success', 'Estado del turno actualizado.');
    }

    public function inquiry(Request $request, Business $business)
    {
        abort_unless($request->user(), 403);
        $data = $request->validate(['subject' => ['required', 'string', 'max:120'], 'message' => ['required', 'string', 'max:1000']]);
        $business->inquiries()->create($data + ['client_id' => $request->user()->id]);
        return back()->with('success', 'Consulta enviada.');
    }

    public function inquiryStatus(Request $request, Business $business, $inquiry)
    {
        $this->ownerOnly($business);
        $data = $request->validate(['status' => ['required', 'in:pending,answered,closed']]);
        $business->inquiries()->findOrFail($inquiry)->update($data + ($data['status'] === 'answered' ? ['answered_at' => now()] : []));
        return back()->with('success', 'Consulta actualizada.');
    }

    public function social(Request $request, Business $business)
    {
        $this->ownerOnly($business);
        $data = $request->validate(['platform' => ['required', 'in:instagram,facebook,whatsapp,tiktok'], 'url' => ['required', 'url', 'max:255']]);
        $business->socialLinks()->updateOrCreate(['platform' => $data['platform']], $data);
        return back()->with('success', 'Red social guardada.');
    }

    public function member(Request $request, Business $business)
    {
        $this->ownerOnly($business);
        $data = $request->validate(['email' => ['required', 'email', 'exists:users,email'], 'role' => ['required', 'in:administrator,employee']]);
        $user = User::where('email', $data['email'])->firstOrFail();
        $business->members()->syncWithoutDetaching([$user->id => ['role' => $data['role'], 'joined_at' => now()]]);
        return back()->with('success', 'Miembro asociado al emprendimiento.');
    }

    private function ownerOnly(Business $business): void
    {
        abort_unless($business->owner_id === Auth::id() || Auth::user()?->role === 'superadmin', 403);
    }
}
