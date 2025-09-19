@extends('layouts.app')

@section('content')
  <section class="header">
    <div class="row">
      <div class="col">
        <h1 class="h1">DELIVERY SCHEDULE</h1>
      </div>
      <div class="col-auto">
        <a href="{{ route('delsched.averagemonth') }}" class="btn btn-secondary"> Average PerMonth</a>
        <a href="{{ route('deslsched.step1') }}" class="btn btn-primary"> Update</a>
      </div>
    </div>
  </section>

  {{-- Realtime Progress Section --}}
  <section class="mt-4">
    <h5>Update Progress</h5>
    <div class="list-group mb-3">
      @foreach ([\App\Jobs\Step1DeliverySchedule::class, \App\Jobs\Step2DeliverySchedule::class, \App\Jobs\Step3DeliverySchedule::class, \App\Jobs\Step4DeliverySchedule::class] as $stepClass)
        @php
          $stepName = class_basename($stepClass);
        @endphp
        <div class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <strong>{{ $stepName }}</strong>
          </div>
          <span class="badge bg-secondary" id="{{ $stepClass }}-status">Pending</span>
        </div>
      @endforeach
    </div>

    <div class="progress mb-4">
      <div class="progress-bar" role="progressbar" id="progress-bar" style="width: 0%;"
        aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
        0%
      </div>
    </div>
  </section>

  {{-- Main Content --}}
  <section class="content">
    <div class="card mt-3">
      <div class="card-body">
        <div class="table-responsive">
          {{ $dataTable->table() }}
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-between mt-3 ">
      <a href="{{ route('indexfinalwip') }}" class="btn btn-secondary"> Delivery Schedule (WIP)</a>
      <a href="{{ route('rawdelsched') }}" class="btn btn-secondary"> Delivery Schedule (RAW)</a>
    </div>
  </section>
@endsection

@push('extraJs')
  {{ $dataTable->scripts() }}

  <script type="module">
    const steps = [
      @json(\App\Jobs\ProcessDeliveryScheduleStep1::class),
      @json(\App\Jobs\ProcessDeliveryScheduleStep2::class),
      @json(\App\Jobs\ProcessDeliveryScheduleStep3::class),
      @json(\App\Jobs\ProcessDeliveryScheduleStep4::class),
    ];

    let completedSteps = 0;

    Echo.private('delivery-schedule-progress')
      .listen('.step.progressed', (e) => {
        console.log('Step update:', e);
        const el = document.getElementById(`${e.stepClass}-status`);
        if (el) {
          if (e.status === 'completed') {
            el.className = 'badge bg-success';
            el.textContent = 'Completed';
            completedSteps++;
          } else if (e.status === 'failed') {
            el.className = 'badge bg-danger';
            el.textContent = 'Failed';
          }

          const percent = Math.round((completedSteps / steps.length) * 100);
          const progressBar = document.getElementById('progress-bar');
          progressBar.style.width = percent + '%';
          progressBar.textContent = percent + '%';
          progressBar.setAttribute('aria-valuenow', percent);
        }
      });
  </script>
@endpush
