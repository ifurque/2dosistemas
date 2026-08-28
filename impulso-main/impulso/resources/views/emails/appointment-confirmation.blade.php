<h1>Confirma tu turno</h1>
<p>Hola {{ $appointment->guest_name }}, recibimos tu solicitud para {{ $appointment->business->name }}.</p>
<p>Fecha: {{ $appointment->appointment_date->format('d/m/Y') }} a las {{ $appointment->start_time }}</p>
<p><a href="{{ route('appointments.confirm', $appointment->confirmation_token) }}">Confirmar solicitud de turno</a></p>