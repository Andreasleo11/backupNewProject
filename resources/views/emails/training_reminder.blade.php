<x-mail::message>
# Training Reminder untuk Evaluasi 3 Bulan

<strong>Employee Name:</strong> {{ $training->employee->Nama }} <br>
<strong>Employee NIK:</strong> {{ $training->employee->NIK }} <br>
<strong>Training Description:</strong> {{ $training->description }} <br>
<strong>Last Training Date:</strong> {{ \Carbon\Carbon::parse($training->last_training_at)->format('d-m-Y') }} <br>
<strong>Evaluated:</strong> {{ $training->evaluated ? 'Yes' : 'No' }}

<x-mail::button :url="url('/employee_trainings/' . $training->id)">
    View Order
</x-mail::button>

Thank you! <br>
{{ config('app.name') }}
</x-mail::message>
