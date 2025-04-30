@include('layout.header')

<style>
    body {
        background-color: #f8f9fa;
    }

    .card {
        border-radius: 10px;
    }

    .card-header {
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
</style>

<div class="container py-3 px-3 px-md-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center p-3">
            <h5 class="mb-0">Customers</h5>
            <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                <a href="{{ route('customers.create') }}" class="btn btn-light btn-sm">Add Customer</a>
                <a href="{{ route('customers.export') }}" class="btn btn-success btn-sm">Export to Excel</a>
            </div>
        </div>
        <div class="card-body p-3">
            <!-- Import Form -->
            <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                @csrf
                <div class="input-group flex-row gap-2 gap-md-0">
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                    <button type="submit" class="btn btn-primary">Import Customers</button>
                </div>
                @error('file')
                <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
                @if (session('success'))
                <div class="alert alert-success mt-2">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                <div class="alert alert-danger mt-2">{{ session('error') }}</div>
                @endif
            </form>

            <!-- Customer Table -->
            @if ($customers->isEmpty())
            <p class="text-muted">No customers found.</p>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                            <td>{{ $customer->address ?? 'N/A' }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-info btn-sm">View</a>
                                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

@include('layout.footer')