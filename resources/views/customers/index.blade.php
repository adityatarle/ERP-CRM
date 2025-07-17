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
        padding: 0.75rem 1rem;
    }
    .table td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
    }
    .import-export-section {
        background-color: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: .375rem;
    }
    .action-buttons form {
        display: inline-block; /* Aligns delete button correctly */
    }
</style>

<body class="act-customers">
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
                        <i class="fa fa-address-book me-2"></i>
                        Manage Customers
                    </h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('customers.create') }}" class="btn btn-light btn-sm"><i class="fa fa-plus me-1"></i> Add Customer</a>
                        <a href="#" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#import-export-collapse" aria-expanded="false" aria-controls="import-export-collapse">
                            <i class="fa fa-upload me-1"></i> Import/Export
                        </a>
                    </div>
                </div>

                {{-- Collapsible Import/Export Form --}}
                <div class="collapse" id="import-export-collapse">
                    <div class="import-export-section p-3 m-3">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Import Customers from Excel/CSV</h6>
                                <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="input-group">
                                        <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-check me-1"></i> Import
                                        </button>
                                    </div>
                                    @error('file')<div class="text-danger mt-2 small">{{ $message }}</div>@enderror
                                </form>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <h6>Export All Customers</h6>
                                <p class="small text-muted">Click the button below to download an Excel file of all customers.</p>
                                <a href="{{ route('customers.export') }}" class="btn btn-success">
                                    <i class="fa fa-file-excel me-1"></i> Export to Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Form -->
                <div class="card-body border-bottom">
                    <form action="{{ route('customers.index') }}" method="GET" class="mb-0">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone, or GST..." value="{{ $searchTerm ?? '' }}">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Search</button>
                            @if(isset($searchTerm))
                                <a href="{{ route('customers.index') }}" class="btn btn-secondary" type="button"><i class="fa fa-times"></i> Clear</a>
                            @endif
                        </div>
                    </form>
                </div>


                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customers as $customer)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-primary">{{ $customer->name }}</div>
                                        </td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->phone ?? 'N/A' }}</td>
                                        <td>
                                            @if($customer->address)
                                                {{ \Illuminate\Support\Str::limit($customer->address, 40, '...') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-end pe-3 action-buttons">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-outline-warning" title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer? All related invoices and sales will also be affected.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center p-4">
                                            @if(isset($searchTerm))
                                                No customers found matching your search for "<strong>{{ $searchTerm }}</strong>".
                                                <a href="{{ route('customers.index') }}" class="d-block mt-2">Clear search</a>
                                            @else
                                                No customers found. Click "Add Customer" to get started.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if ($customers->hasPages())
                    <div class="card-footer bg-light border-top">
                        {{-- This automatically includes the search query in pagination links --}}
                        {{ $customers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
@include('layout.footer')