@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Email Settings') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('email.update') }}">
                            @csrf

                            <div class="form-group row">
                                <label for="feature" class="col-md-4 col-form-label text-md-right">{{ __('Select Feature') }}</label>

                                <div class="col-md-6">
                                    <select id="feature" class="form-control" name="feature">
                                        @foreach($featureNames as $feature)
                                            <option value="{{ $feature }}">{{ ucfirst($feature) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="to" class="col-md-4 col-form-label text-md-right">{{ __('To') }}</label>

                                <div class="col-md-6">
                                    <input id="to" type="email" class="form-control @error('to') is-invalid @enderror" name="to" required autofocus>

                                    @error('to')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="cc" class="col-md-4 col-form-label text-md-right">{{ __('Cc') }}</label>

                                <div class="col-md-6">
                                    <textarea id="cc" class="form-control @error('cc') is-invalid @enderror" name="cc" rows="4" required></textarea>

                                    @error('cc')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extraJs')
    <script>
        // Function to update the 'to' and 'cc' fields based on the selected feature
        function updateEmailSettings(selectedFeature) {
            // Retrieve the email settings for the selected feature from the server
            fetch(`/get-email-settings/${selectedFeature}`)
                .then(response => response.json())
                .then(data => {
                    // Update the 'to' and 'cc' fields with the retrieved data
                    document.getElementById('to').value = data.to;
                    document.getElementById('cc').value = data.cc.join(';');
                })
                .catch(error => console.error('Error:', error));
        }

        // Event listener for the feature dropdown
        document.getElementById('feature').addEventListener('change', function() {
            const selectedFeature = this.value;
            updateEmailSettings(selectedFeature);
        });

        // Initialize email settings based on the default selected feature
        const defaultFeature = document.getElementById('feature').value;
        updateEmailSettings(defaultFeature);
    </script>
@endpush
