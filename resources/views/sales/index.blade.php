@include('layout.header')

<body class="act-sales">
    <div class="main-content-area">
        @if (session('success'))
            <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">

                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h5 class="mb-2 mb-md-0">Sales</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('sales.import.form') }}" class="btn btn-light">
                            <i class="fa fa-upload me-1"></i>Import Excel
                        </a>
                        <a href="{{ route('sales.create') }}" class="btn btn-light">Create Sale</a>
                    </div>
                </div>

                <div class="table-responsive shadow-sm"
                    style="background-color: #ffffff; border-radius: 10px; overflow: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="padding: 15px; font-weight: 600;">ID</th>
                                <th scope="col" style="padding: 15px; font-weight: 600;">Customer</th>
                                <th scope="col" style="padding: 15px; font-weight: 600;">Product</th>
                                <th scope="col" style="padding: 15px; font-weight: 600;">Quantity</th>
                                <th scope="col" style="padding: 15px; font-weight: 600;">Total Price</th>
                                <th scope="col" style="padding: 15px; font-weight: 600;">Status</th>
                                <th scope="col" style="padding: 15px; font-weight: 600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sales as $sale)
                                <tr style="transition: background-color 0.2s;">
                                    <td style="padding: 15px;">{{ $sale->id }}</td>
                                    <td style="padding: 15px;">{{ $sale->customer->name }}</td>
                                    <td style="padding: 15px; vertical-align: middle;">
                                        {{ $sale->saleItems->first()->product->name ?? 'No product' }}
                                    </td>
                                    <td style="padding: 15px;">{{ $sale->quantity }}</td>
                                    <td style="padding: 15px;">{{ $sale->total_price }}</td>
                                    <td style="padding: 15px;">
                                        <form method="POST" action="{{ route('sales.update-status', $sale->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" style="width: auto;" onchange="this.form.submit()">
                                                <option value="pending" {{ $sale->status === 'pending' ? 'selected' : '' }}>
                                                    Pending</option>
                                                <option value="confirmed" {{ $sale->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="canceled" {{ $sale->status === 'canceled' ? 'selected' : '' }}>
                                                    Canceled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-info btn-sm">
                                                View
                                            </a>
                                            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning btn-sm">
                                                Edit
                                            </a>
                                            <!-- <form action="{{ route('sales.destroy', $sale) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this sale?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>
                                            </form> -->
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding: 20px; text-align: center; color: #6c757d;">
                                        No sales found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function () {
            console.log('Dropdown changed:', this.value, this.getAttribute('data-sale-id')); // Debug
            const saleId = this.getAttribute('data-sale-id');
            const newStatus = this.value;

            axios.post('/sales/update-status', {
                sale_id: saleId,
                status: newStatus
            })
                .then(response => {
                    console.log('Response:', response.data); // Debug
                    if (response.data.success) {
                        alert('Status updated successfully!');
                    } else {
                        alert('Failed to update status: ' + response.data.message);
                        this.value = response.data.previous_status;
                    }
                })
                .catch(error => {
                    console.error('Error:', error); // Debug
                    alert('An error occurred while updating the status.');
                });
        });
    });
</script>

@include('layout.footer')