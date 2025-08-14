<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daijo Industrial Support System | DISS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/login-style.css">
</head>

<body>

    @if ($message = Session::get('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <p>{{ $message }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="container d-flex align-items-center min-vh-100 justify-content-center">
        <div class="card rounded-5">
            <div class="row">
                <!-- Left box -->
                <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box">
                    <div class="featured-image mb-3">
                        <img src="image/1.png" alt="image" class="img-fluid">
                    </div>
                    <p class="text-white fs-2">Be Verified</p>
                    <small class="text-white text-wrap text-center">Join experienced designers on this platform.</small>
                </div>
                <!-- Right box -->
                <div class="col-md-6 right-box">
                    <div class="row-align-items-center">
                        <div class="header-text mb-4 text-wrap text-md-start">
                            <h2 class="fs-3">Hello, Again</h2>
                            <small>We are happy to have you back.</small>
                        </div>
                        <form method="POST" action="{{ route('login') }}" class="pe-4">
                            @csrf

                            <div class="row mb-3">
                                <label for="email" class="col-form-label">{{ __('Email Address') }}</label>

                                <div class="col">
                                    <input id="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="password" class="col-form-label">{{ __('Password') }}</label>

                                <div class="col">
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        required autocomplete="current-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{--
                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                        <label class="form-check-label" for="remember">
                                            {{ __('Remember Me') }}
                                        </label>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="row mb-0 mt-4 px-3">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>
                                {{-- <div class="col">
                                    @if (Route::has('password.request'))
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                            {{ __('Forgot Your Password?') }}
                                        </a>
                                    @endif
                                </div> --}}
                            </div>
                        </form>
                        <div class="col mt-4 text-end">
                            Daily Employee Job?
                            <a href="{{ route('employee-login') }}" class="btn btn-outline-primary btn-sm">Click
                                Here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
