@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title">
                        <i class="fas fa-university"></i> 
                        {{ isset($institute) ? 'Edit' : 'Add' }} Institute
                    </h3>
                </div>
                <form action="{{ isset($institute) ? route('institutes.update', $institute->id) : route('institutes.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($institute))
                        @method('POST')
                    @endif
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $institute->name ?? '') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $institute->email ?? '') }}" required>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" id="phone" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $institute->phone ?? '') }}">
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea name="address" id="address" rows="3" 
                                              class="form-control @error('address') is-invalid @enderror">{{ old('address', $institute->address ?? '') }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="logo" class="form-label">Institute Logo</label>
                                    <input class="form-control @error('logo') is-invalid @enderror" 
                                           type="file" id="logo" name="logo">
                                    @error('logo')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    @if(isset($institute) && $institute->logo)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/'.$institute->logo) }}" 
                                                 alt="Institute Logo" class="img-thumbnail" width="100">
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" name="is_active" id="is_active" 
                                               class="custom-control-input" value="1"
                                               @if(old('is_active', $institute->is_active ?? true)) checked @endif>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ isset($institute) ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{ route('institutes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection