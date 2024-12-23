@extends('adminlte::page')

@section('content')

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Edit User: {{ $user->name }}</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- User Info Section -->
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                </div>

                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current password)</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                <hr>

                <!-- Role Section -->
                <h4>Change Role</h4>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" class="form-control">
                        <option value="">-- Select Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ $user->roles->pluck('name')->contains($role->name) ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <hr>

                <!-- Permissions Section -->
                <h4>Manage Permissions</h4>
                <div class="form-group">
                    <label for="permissions">Permissions</label>
                    <select name="permissions[]" class="form-control select2" multiple="multiple" style="width: 100%;">
                        @foreach($permissions as $permission)
                            <option value="{{ $permission->name }}" {{ $user->permission->pluck('name')->contains($permission->name) ? 'selected' : '' }}>
                                {{ ucfirst($permission->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@stop

@section('css')
    <!-- Include Select2 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
@stop

@section('js')
    <!-- Include Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select permissions"
            });
        });
    </script>
@stop
