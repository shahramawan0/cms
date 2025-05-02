@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title text-white">
                        <i class="fas fa-user-cog text-white"></i> 
                        {{ isset($user) ? 'Edit' : 'Add' }} Admin User
                    </h3>
                </div>
                <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($user))
                        @method('POST')
                    @endif
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="institute_id">Institute <span class="text-danger">*</span></label>
                                    <select name="institute_id" id="institute_id" class="form-control @error('institute_id') is-invalid @enderror" required>
                                        <option value="">Select Institute</option>
                                        @foreach($institutes as $institute)
                                            <option value="{{ $institute->id }}" 
                                                {{ (isset($user) && $user->institute_id == $institute->id) || old('institute_id') == $institute->id ? 'selected' : '' }}>
                                                {{ $institute->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('institute_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $user->name ?? '') }}" required>
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
                                           value="{{ old('email', $user->email ?? '') }}" required>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea name="address" id="address" 
                                              class="form-control @error('address') is-invalid @enderror" 
                                              rows="3">{{ old('address', $user->address ?? '') }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Admin Role <span class="text-danger">*</span></label>
                                    <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                                        <option value="">Select Admin Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" 
                                                {{ (isset($user) && $user->hasRole($role->name)) || old('role') == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="designation">Designation</label>
                                    <input type="text" name="designation" id="designation" 
                                           class="form-control @error('designation') is-invalid @enderror" 
                                           value="{{ old('designation', $user->designation ?? '') }}">
                                    @error('designation')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" name="phone" id="phone" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $user->phone ?? '') }}">
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>


                                <div class="form-group">
                                    <label for="password">{{ isset($user) ? 'New ' : '' }}Password @if(!isset($user))<span class="text-danger">*</span>@endif</label>
                                    <input type="password" name="password" id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           {{ !isset($user) ? 'required' : '' }}>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password @if(!isset($user))<span class="text-danger">*</span>@endif</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" 
                                           class="form-control" {{ !isset($user) ? 'required' : '' }}>
                                </div>
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Profile Image</label>
                                    <input class="form-control @error('profile_image') is-invalid @enderror" 
                                           type="file" id="profile_image" name="profile_image">
                                    @error('profile_image')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    @if(isset($user) && $user->profile_image)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/'.$user->profile_image) }}" 
                                                 alt="Profile Image" class="img-thumbnail" width="100">
                                        </div>
                                    @endif
                                </div>



                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ isset($user) ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection