@if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class='bx bx-check-circle me-2' style="font-size:20px;"></i>
        {{ $message }}
        <button id="closeAlertButton" type="button" class="btn-close close-alert" data-bs-dismiss="alert"
            aria-label="Close"></button>
    </div>
@elseif ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissable d-flex align-items-center fade show" role="alert">
            <i class='bx bx-error-circle me-2' style="font-size:20px;"></i>
            {{ $error }}
            <button id="closeAlertButton" type="button" class="ms-auto btn-close close-alert" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
    @endforeach
@endif

<script>
    const closeAlertButtons = document.querySelectorAll('.close-alert');
    setTimeout(() => {
        if (closeAlertButtons) {
            closeAlertButtons.forEach(btn => {
                btn.click();
            });
        }
    }, 5000);
</script>
