@include('layout.header')

<body class="act-enquiry">
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h1 class="mb-0 h5 text-white">Enquiries</h1>
                    <a href="{{ route('enquiry.create') }}" class="btn btn-light">Add Enquiry</a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer Name</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($enquiries as $enquiry)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $enquiry->customer_name }}</td>
                                    <td>{{ $enquiry->contact_number }}</td>
                                    <td>{{ $enquiry->email ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($enquiry->status) }}</td>
                                    <td>
                                        <a href="{{ route('enquiry.show', $enquiry) }}" class="btn btn-sm btn-warning">View</a>
                                        <a href="{{ route('enquiry.edit', $enquiry) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('enquiry.destroy', $enquiry) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $enquiries->links() }}
                </div>
            </div>
        </div>
    </div>
</body>

@include('layout.footer')