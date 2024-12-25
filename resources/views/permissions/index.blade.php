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
                            // Create table row
                            const newRow = document.createElement('tr');

                            // Create ID cell
                            const idCell = document.createElement('td');
                            idCell.textContent = data.permission.id;
                            newRow.appendChild(idCell);

                            // Create permission name cell
                            const nameCell = document.createElement('td');
                            const updateForm = document.createElement('form');
                            updateForm.action = `{{ route('permissions.update', ':id') }}`.replace(':id', data.permission.id);
                            updateForm.method = 'POST';
                            updateForm.classList.add('inline-form');

                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = '{{ csrf_token() }}';
                            updateForm.appendChild(csrfInput);

                            const methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = 'PUT';
                            updateForm.appendChild(methodInput);

                            const nameInput = document.createElement('input');
                            nameInput.type = 'text';
                            nameInput.name = 'name';
                            nameInput.classList.add('form-control', 'form-control-sm', 'permission-name');
                            nameInput.value = data.permission.name;
                            nameInput.setAttribute('data-original-name', data.permission.name);
                            nameInput.required = true;
                            updateForm.appendChild(nameInput);

                            const saveButton = document.createElement('button');
                            saveButton.type = 'submit';
                            saveButton.classList.add('btn', 'btn-success', 'btn-sm', 'save-btn');
                            saveButton.disabled = true;
                            saveButton.innerHTML = '<i class="fas fa-check"></i> Save';
                            updateForm.appendChild(saveButton);

                            nameCell.appendChild(updateForm);
                            newRow.appendChild(nameCell);

                            // Create actions cell
                            const actionsCell = document.createElement('td');

                            // Create delete form
                            const deleteForm = document.createElement('form');
                            deleteForm.action = `{{ route('permissions.destroy', ':id') }}`.replace(':id', data.permission.id);
                            deleteForm.method = 'POST';
                            deleteForm.style.display = 'inline-block';

                            const deleteCsrfInput = document.createElement('input');
                            deleteCsrfInput.type = 'hidden';
                            deleteCsrfInput.name = '_token';
                            deleteCsrfInput.value = '{{ csrf_token() }}';
                            deleteForm.appendChild(deleteCsrfInput);

                            const deleteMethodInput = document.createElement('input');
                            deleteMethodInput.type = 'hidden';
                            deleteMethodInput.name = '_method';
                            deleteMethodInput.value = 'DELETE';
                            deleteForm.appendChild(deleteMethodInput);

                            const deleteButton = document.createElement('button');
                            deleteButton.type = 'submit';
                            deleteButton.classList.add('btn', 'btn-danger', 'btn-sm');
                            deleteButton.onclick = () => confirm('Are you sure you want to delete this permission?');
                            deleteButton.innerHTML = '<i class="fas fa-trash"></i>';
                            deleteForm.appendChild(deleteButton);

                            actionsCell.appendChild(deleteForm);
                            newRow.appendChild(actionsCell);

                            // Append the new row to the table
                            permissionsList.appendChild(newRow);

                            // Reset form and hide
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
