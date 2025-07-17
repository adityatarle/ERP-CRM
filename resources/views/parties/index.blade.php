@include('layout.header')

<style>
    /* Page & Card Styling */
    body {
        background-color: #f4f7f9;
    }
    .main-content-area {
        min-height: 100vh;
    }
    .card-header h1 {
        font-size: 1.25rem;
        font-weight: 600;
    }
    .table thead th {
        background-color: #e9ecef;
        font-weight: 600;
        color: #495057;
        white-space: nowrap;
    }
    .table td {
        vertical-align: middle;
    }
    .import-section {
        background-color: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: .375rem;
    }
</style>

<body class="act-parties">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
             @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex flex-column flex-md-row justify-content-between align-items-md-center text-white p-3">
                    <h1 class="mb-2 mb-md-0 h5 text-white">
                        <i class="fa fa-users me-2"></i>
                        Manage Parties
                    </h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('parties.create') }}" class="btn btn-light btn-sm"><i class="fa fa-plus me-1"></i> Add Party</a>
                        <a href="#" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#import-form-collapse" aria-expanded="false" aria-controls="import-form-collapse">
                            <i class="fa fa-upload me-1"></i> Import/Export
                        </a>
                    </div>
                </div>

                {{-- Collapsible Import Form --}}
                <div class="collapse" id="import-form-collapse">
                    <div class="import-section p-3 m-3">
                        <h6>Import Parties from Excel/CSV</h6>
                        <form action="{{ route('parties.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" name="file" id="import-file" accept=".xlsx, .csv" class="form-control" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-check me-1"></i> Upload and Import
                                </button>
                            </div>
                            <small class="form-text text-muted">
                                Please ensure your file has columns: 'name', 'gst_in', 'email', 'phone_number', 'address'.
                            </small>
                        </form>
                    </div>
                </div>

                {{-- Search Form --}}
                <div class="card-body border-bottom">
                    <form action="{{ route('parties.index') }}" method="GET" class="mb-0">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, GSTIN, email, or phone..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button>
                            @if(request('search'))
                                <a href="{{ route('parties.index') }}" class="btn btn-secondary" type="button"><i class="fa fa-times"></i> Clear</a>
                            @endif
                        </div>
                    </form>
                </div>


                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Party Name</th>
                                    <th>GSTIN</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parties as $party)
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('parties.show', $party) }}" class="text-decoration-none">
                                                <div class="fw-bold">{{ $party->name }}</div>
                                            </a>
                                        </td>
                                        <td>{{ $party->gst_in ?? 'N/A' }}</td>
                                        <td>{{ $party->email ?? 'N/A' }}</td>
                                        <td>{{ $party->phone_number ?? 'N/A' }}</td>
                                        <td>
                                            @if($party->address)
                                                {{ \Illuminate\Support\Str::limit($party->address, 50, '...') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="{{ route('parties.show', $party) }}" class="btn btn-sm btn-outline-warning" title="View"><i class="fa fa-eye"></i></a>
                                                <a href="{{ route('parties.edit', $party) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                                <form action="{{ route('parties.destroy', $party) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this party?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center p-4">
                                            @if(request('search'))
                                                No parties found matching your search for "<strong>{{ request('search') }}</strong>".
                                                <a href="{{ route('parties.index') }}" class="d-block mt-2">Clear search</a>
                                            @else
                                                No parties found. You can <a href="{{ route('parties.create') }}">add one</a> or import them using the button above.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if ($parties->hasPages())
                    <div class="card-footer">
                        {{-- This will automatically include the search query in pagination links --}}
                        {{ $parties->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
@include('layout.footer')