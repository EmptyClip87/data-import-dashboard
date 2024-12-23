@extends('adminlte::page')

@section('content')
    <div class="box" style="max-width: 600px; margin: 0 auto;">
        <div class="box-header with-border">
            <h3 class="box-title">Permissions</h3>
            <div class="box-tools">
                <button id="add-permission-btn" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Permission
                </button>
            </div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <!-- Add New Permission Form -->
            <div id="add-permission-form" style="display: none; margin-bottom: 20px;">
                <form action="{{ route('permissions.store') }}" method="POST" id="create-permission-form">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="name" id="new-permission-name" class="form-control form-control-sm" placeholder="Permission Name" required>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Permissions Table -->
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th style="width: 20%;">ID</th>
                    <th style="width: 50%;">Permission Name</th>
                    <th style="width: 30%;">Actions</th>
                </tr>
                </thead>
                <tbody id="permissions-list">
                @foreach($permissions as $permission)
                    <tr>
                        <td>{{ $permission->id }}</td>
                        <td>
                            <form action="{{ route('permissions.update', $permission->id) }}" method="POST" class="inline-form">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" class="form-control form-control-sm permission-name"
                                       value="{{ $permission->name }}" data-original-name="{{ $permission->name }}" required>
                        </td>
                        <td>
                            <button type="submit" class="btn btn-success btn-sm save-btn" disabled>
                                <i class="fas fa-check"></i> Save
                            </button>
                            </form>
                            <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this permission?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <style>
        .box {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-control-sm {
            display: inline-block;
            width: 100%;
        }
        .inline-form {
            display: flex;
            align-items: center;
        }
        .save-btn {
            margin-right: 5px;
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addPermissionBtn = document.getElementById('add-permission-btn');
            const addPermissionForm = document.getElementById('add-permission-form');
            const permissionsList = document.getElementById('permissions-list');
            const createPermissionForm = document.getElementById('create-permission-form');
            const newPermissionName = document.getElementById('new-permission-name');

            // Toggle add new permission form
            addPermissionBtn.addEventListener('click', function () {
                addPermissionForm.style.display = addPermissionForm.style.display === 'none' ? 'block' : 'none';
                newPermissionName.focus();
            });

            // Add permission dynamically to the list after saving
            createPermissionForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(createPermissionForm);
                fetch("{{ route('permissions.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Add new permission row to the table
                            const newRow = document.createElement('tr');
                            newRow.innerHTML = `
                                <td>${data.permission.id}</td>
                                <td>
                                    <form action="{{ route('permissions.update', ':id') }}".replace(':id', data.permission.id) method="POST" class="inline-form">
                                        @csrf
                            @method('PUT')
                            <input type="text" name="name" class="form-control form-control-sm permission-name"
                                   value="${data.permission.name}" data-original-name="${data.permission.name}" required>
                                        <button type="submit" class="btn btn-success btn-sm save-btn" disabled>
                                            <i class="fas fa-check"></i> Save
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{ route('permissions.destroy', ':id') }}".replace(':id', data.permission.id) method="POST" style="display: inline-block;">
                                        @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this permission?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
`;
                            permissionsList.appendChild(newRow);
                            createPermissionForm.reset();
                            addPermissionForm.style.display = 'none';
                        } else {
                            alert('Error creating permission: ' + data.message);
                        }
                    });
            });
        });
    </script>
@stop
