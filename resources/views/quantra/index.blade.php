@include('layout.header')

<div class="main-content-area">
    <div class="container p-3 mx-auto">
        <div class="card shadow-sm w-100">
            <div class="card-header bg-primary text-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center p-3">
                <h5 class="mb-0 text-white">Quantra Entries</h5>
                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    <a href="{{ route('quantra.create') }}" class="btn btn-light btn-sm">
                        <i class="fa fa-plus me-2"></i>Create Quantra Entry
                    </a>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- Success Message -->
                @if (session('success'))
                    <div class="alert alert-success mt-3">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Quantra Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Note</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quantras as $quantra)
                                <tr>
                                    <td>{{ number_format($quantra->amount, 2) }}</td>
                                    <td>{{ $quantra->note ?? 'N/A' }}</td>
                                    <td>{{ $quantra->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No Quantra entries found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layout.footer')