@include('layout.header')

<style>
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

    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        padding: 3rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .upload-area:hover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }

    .upload-area.dragover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
        transform: scale(1.02);
    }

    .upload-icon {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .instructions {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 0.375rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .format-table {
        font-size: 0.875rem;
    }

    .format-table th {
        background-color: #e9ecef;
        font-weight: 600;
        padding: 0.5rem;
    }

    .format-table td {
        padding: 0.5rem;
        border-bottom: 1px solid #dee2e6;
    }
</style>

<body class="act-sales">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                @if (session('errors'))
                    <br><small>{{ session('errors') }}</small>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white p-3">
                    <h1 class="mb-0 h5 text-white">
                        <i class="fa fa-upload me-2"></i>
                        Import Sales Data
                    </h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('sales.import.template') }}" class="btn btn-light btn-sm">
                            <i class="fa fa-download me-1"></i> Download Template
                        </a>
                        <a href="{{ route('sales.index') }}" class="btn btn-light btn-sm">
                            <i class="fa fa-arrow-left me-1"></i> Back to Sales
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Instructions -->
                    <div class="instructions">
                        <h5 class="text-warning mb-3">
                            <i class="fa fa-info-circle me-2"></i>
                            Instructions for Excel Import
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>📋 Preparation:</h6>
                                <ul class="mb-3">
                                    <li>Download the template file first</li>
                                    <li>Use the exact column headers as shown</li>
                                    <li>Fill in your data following the sample format</li>
                                    <li>Save as Excel (.xlsx) or CSV format</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>🔍 What will be imported:</h6>
                                <ul class="mb-3">
                                    <li><strong>Sales records</strong> with customer details</li>
                                    <li><strong>Invoices</strong> with GST calculations</li>
                                    <li><strong>New customers</strong> if not found</li>
                                    <li><strong>New products</strong> if not found</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <strong>Note:</strong> The system will automatically match existing customers by name or GST number, and products by name or item code. New records will be created if no matches are found.
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('sales.import.excel') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-icon">
                                <i class="fa fa-cloud-upload-alt"></i>
                            </div>
                            <h5 class="mb-2">Drag & drop your Excel file here</h5>
                            <p class="text-muted mb-3">or click to browse files</p>
                            <input type="file" name="excel_file" id="excelFile" class="d-none" accept=".xlsx,.xls,.csv" required>
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('excelFile').click()">
                                <i class="fa fa-folder-open me-2"></i>Choose File
                            </button>
                            <div id="fileName" class="mt-3 text-success" style="display: none;"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                <small>Supported formats: Excel (.xlsx, .xls), CSV • Max size: 10MB</small>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                                <i class="fa fa-upload me-2"></i>Import Data
                            </button>
                        </div>
                    </form>

                    <!-- Format Reference -->
                    <div class="mt-5">
                        <h5 class="text-secondary mb-3">
                            <i class="fa fa-table me-2"></i>
                            Expected Data Format
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered format-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Particulars</th>
                                        <th>Buyer</th>
                                        <th>Voucher No.</th>
                                        <th>GSTIN/UIN</th>
                                        <th>Quantity</th>
                                        <th>Rate</th>
                                        <th>Value</th>
                                        <th>Gross Total</th>
                                        <th>CGST@9%</th>
                                        <th>SGST@9%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>02-Jun-25</td>
                                        <td>COMPANY NAME</td>
                                        <td>COMPANY NAME</td>
                                        <td>2025/26/217</td>
                                        <td>27AABCP7335H1ZC</td>
                                        <td>9 NOS</td>
                                        <td></td>
                                        <td>23868.00</td>
                                        <td>28164.24</td>
                                        <td>2148.12</td>
                                        <td>2148.12</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>PRODUCT-001 Product Description</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>1 NOS</td>
                                        <td>4242.00/NOS</td>
                                        <td>4242.00</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">
                            <strong>Note:</strong> The first row contains the main sale/invoice information, subsequent rows contain individual product details.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // File upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('excelFile');
        const fileName = document.getElementById('fileName');
        const submitBtn = document.getElementById('submitBtn');

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                updateFileName(files[0]);
            }
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                updateFileName(e.target.files[0]);
            }
        });

        function updateFileName(file) {
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                                 'application/vnd.ms-excel', 
                                 'text/csv'];
            
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid Excel or CSV file.');
                fileInput.value = '';
                fileName.style.display = 'none';
                submitBtn.disabled = true;
                return;
            }

            if (file.size > 10 * 1024 * 1024) { // 10MB
                alert('File size must be less than 10MB.');
                fileInput.value = '';
                fileName.style.display = 'none';
                submitBtn.disabled = true;
                return;
            }

            fileName.innerHTML = `<i class="fa fa-file-excel me-2"></i>Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            fileName.style.display = 'block';
            submitBtn.disabled = false;
        }

        // Form submission with loading state
        document.getElementById('importForm').addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Importing...';
        });
    </script>
</body>
@include('layout.footer')