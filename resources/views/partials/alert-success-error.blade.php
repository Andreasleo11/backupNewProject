@if ($message = Session::get('success'))
  <div
    class="alert alert-success alert-dismissible fade show d-flex align-items-center auto-dismiss-alert"
    role="alert">
    <i class='bx bx-check-circle me-2' style="font-size:20px;"></i>
    {{ $message }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@elseif ($message = Session::get('error'))
  <div
    class="alert alert-danger alert-dismissible fade show d-flex align-items-center auto-dismiss-alert"
    role="alert">
    <i class='bx bx-error-circle me-2' style="font-size:20px;"></i>
    {{ $message }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  {{-- @elseif ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center auto-dismiss-alert"
            role="alert">
            <i class='bx bx-error-circle me-2' style="font-size:20px;"></i>
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endforeach --}}
@endif

{{-- <!-- auto close after 5 seconds -->
<script>
    // Auto dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            document.querySelectorAll('.auto-dismiss-alert').forEach(alert => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            });
        }, 5000); // 5000ms = 5 seconds
    });
</script> --}}
