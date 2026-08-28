<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmation;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BusinessInteractionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_request_and_confirm_an_appointment_by_email(): void
    {
        Mail::fake();
        $business = Business::create(['owner_id' => User::factory()->create()->id, 'name' => 'Taller Norte', 'category' => 'Oficios', 'description' => 'Un taller', 'location' => 'Centro']);

        $response = $this->post(route('appointments.store', $business), [
            'guest_name' => 'Ana Pérez', 'guest_email' => 'ana@example.com', 'appointment_date' => now()->addDay()->format('Y-m-d'), 'start_time' => '10:00',
        ]);

        $appointment = $business->appointments()->first();
        $response->assertSessionHas('success');
        $this->assertSame('awaiting_confirmation', $appointment->status);
        Mail::assertSent(AppointmentConfirmation::class, fn ($mail) => $mail->hasTo('ana@example.com'));
        $this->get(route('appointments.confirm', $appointment->confirmation_token))->assertRedirect();
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'pending', 'confirmation_token' => null]);
    }

    public function test_anyone_can_leave_a_five_star_review(): void
    {
        $business = Business::create(['owner_id' => User::factory()->create()->id, 'name' => 'Cocina Sur', 'category' => 'Gastronomía', 'description' => 'Comida', 'location' => 'Barrio']);

        $this->post(route('reviews.store', $business), ['reviewer_name' => 'Luis', 'rating' => 5, 'body' => 'Excelente atención.'])->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', ['business_id' => $business->id, 'rating' => 5, 'body' => 'Excelente atención.']);
    }

    public function test_authenticated_user_can_open_a_public_business_profile(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $business = Business::create(['owner_id' => $owner->id, 'name' => 'Estudio Centro', 'category' => 'Servicios', 'description' => 'Atención', 'location' => 'Centro']);

        $this->actingAs($visitor)->get(route('business.show', $business))->assertOk();
    }
}