@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="container mt-3">
                        <div class="p-3">
                            <h4>Create New Project</h4>
                            <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Fuga aspernatur assumenda
                                cum
                                provident
                                aliquid hic praesentium nulla beatae, perspiciatis culpa?</p>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('pt.store') }}">
                                @csrf

                                <div class="form-group row mb-3">
                                    <label for="project_name"
                                        class="col-md-4 col-form-label text-md-right">{{ __('Project Name') }}</label>

                                    <div class="col-md-6">
                                        <input id="project_name" type="text"
                                            class="form-control @error('project_name') is-invalid @enderror"
                                            name="project_name" value="{{ old('project_name') }}" required
                                            autocomplete="project_name" autofocus>

                                        @error('project_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <label for="dept"
                                        class="col-md-4 col-form-label text-md-right">{{ __('Department') }}</label>

                                    <div class="col-md-6">
                                        <select id="dept" class="form-control @error('dept') is-invalid @enderror"
                                            name="dept" required>
                                            <option value="">Select Department</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>

                                        @error('dept')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <label for="request_date"
                                        class="col-md-4 col-form-label text-md-right">{{ __('Request Date') }}</label>

                                    <div class="col-md-6">
                                        <input id="request_date" type="date"
                                            class="form-control @error('request_date') is-invalid @enderror"
                                            name="request_date" value="{{ old('request_date') }}" required>

                                        @error('request_date')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <label for="pic"
                                        class="col-md-4 col-form-label text-md-right">{{ __('PIC') }}</label>

                                    <div class="col-md-6">
                                        <input id="pic" type="text"
                                            class="form-control @error('pic') is-invalid @enderror" name="pic"
                                            value="{{ old('pic') }}" required autocomplete="pic">

                                        @error('pic')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <label for="description"
                                        class="col-md-4 col-form-label text-md-right">{{ __('Description') }}</label>

                                    <div class="col-md-6">
                                        <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" required>{{ old('description') }}</textarea>

                                        @error('description')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6 offset-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Create Project') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
