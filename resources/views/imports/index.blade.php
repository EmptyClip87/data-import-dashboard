@extends('adminlte::page')

@section('content')
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Import Data</h3>
        </div>
        <div class="box-body">
            {{-- Display Success Message --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Display Error Message --}}
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Display Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('imports.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="import_type">Select Import Type:</label>
                    <select id="import_type" name="import_type" class="form-control" required>
                        <option value="" disabled selected>-- Select --</option>
                        @foreach ($importTypes as $key => $type)
                            <option value="{{ $key }}">{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="file_inputs"></div>

                <button type="submit" class="btn btn-primary">Start Import</button>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        const importConfig = @json($importTypes);

        document.getElementById('import_type').addEventListener('change', function () {
            const importType = this.value;
            const fileInputs = document.getElementById('file_inputs');
            fileInputs.innerHTML = ''; // Clear previous inputs

            if (importConfig[importType]) {
                const files = importConfig[importType].files;

                Object.keys(files).forEach((key) => {
                    const file = files[key];
                    const headers = Object.keys(file.headers_to_db)
                        .map(header => file.headers_to_db[header].label || header)
                        .join(', ');

                    fileInputs.innerHTML += `
                    <div class="form-group">
                        <label for="${key}">${file.label}</label>
                        <input type="file" name="${key}" id="${key}" class="form-control" data-required="true">
                        <small class="form-text text-muted">
                            <strong>Required Headers:</strong> ${headers}
                        </small>
                    </div>
                `;
                });
            }
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            const requiredInputs = document.querySelectorAll('[data-required="true"]');
            let atLeastOneFileUploaded = false;

            requiredInputs.forEach(input => {
                if (input.files.length > 0) {
                    atLeastOneFileUploaded = true;
                }
            });

            if (!atLeastOneFileUploaded) {
                e.preventDefault(); // Stop form submission
                alert('Please upload at least one file.');
            }
        });
    </script>
@stop

