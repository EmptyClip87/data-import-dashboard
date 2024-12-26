@extends('adminlte::page')

@section('content')
    <div class="container">
        <h1 class="mb-4">{{ $title }}</h1>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="GET" action="{{ url()->current() }}" class="mb-3 d-flex justify-content-between align-items-center">
            <div class="input-group" style="width: 200px;">
                <input
                    type="text"
                    name="search"
                    class="form-control form-control-sm"
                    placeholder="Search..."
                    value="{{ request('search') }}">
                <div class="input-group-append">
                    @if(request('search'))
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            onclick="window.location='{{ url()->current() }}'">
                            &times;
                        </button>
                    @endif
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <a
                    href="{{ route('files.export', ['type' => $type, 'file' => $file, 'search' => request('search')]) }}"
                    class="btn btn-sm btn-success">
                    Export to XLSX
                </a>
            </div>
        </form>

        <table class="table table-sm table-bordered table-hover">
            <thead>
            <tr>
                @foreach($config['headers_to_db'] as $header => $details)
                    <th>{{ $details['label'] }}</th>
                @endforeach
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($records as $record)
                <tr>
                    @foreach($config['headers_to_db'] as $header => $details)
                        <td>{{ $record->$header }}</td>
                    @endforeach
                    <td class="text-center">
                        <form method="POST" action="{{ url()->current() }}/{{ $record->id }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this record?')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($config['headers_to_db']) }}" class="text-center">No records found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $records->links() }}
    </div>
@endsection
