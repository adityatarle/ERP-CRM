@include('layout.header')

<style>
    /* Page & Form Styling */
    body {
        background-color: #f4f7f9; /* A light, neutral background */
    }
    .main-content-area {
        min-height: 100vh;
        display: flex;
        align-items: center; /* Vertically center the card */
    }
    .quantra-card {
        max-width: 700px; /* Constrain width on large screens */
        margin: auto;     /* Horizontally center the card */
        border: none;     /* Remove default border for a cleaner look with shadow */
    }
    .card-header h1 {
        font-size: 1.25rem;
        font-weight: 600;
    }
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    .input-group-text {
        background-color: #e9ecef;
        font-weight: bold;
    }
</style>

<body class="act-quantra">
    <div class="main-content-area">
        <div class="container p-3 p-md-4">
            <div class="card shadow-sm w-100 quantra-card">
                <div class="card-header bg-primary text-white p-3 d-flex align-items-center">
                    <i class="fa fa-file-invoice-dollar fa-lg me-2"></i>
                    <h1 class="mb-0 h5">New Quantra Expense Entry</h1>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <form action="{{ route('quantra.store') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-12">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="amount" id="amount" class="form-control form-control-lg" step="0.01" required placeholder="0.00" value="{{ old('amount') }}">
                                </div>
                                @error('amount')
                                    <div class="text-danger mt-1 small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="note" class="form-label">Note / Description</label>
                                <textarea name="note" id="note" class="form-control" rows="4" placeholder="e.g., Office supplies, travel expenses, software subscription, etc.">{{ old('note') }}</textarea>
                                @error('note')
                                     <div class="text-danger mt-1 small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="text-end">
                            <a href="{{ route('quantra.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check me-1"></i> Save Entry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@include('layout.footer')