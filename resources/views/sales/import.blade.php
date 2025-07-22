@include('layout.header')

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-content-area {
        min-height: 100vh;
        padding: 2rem 0;
    }

    .page-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .page-title {
        background: linear-gradient(45deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        text-align: center;
    }

    .page-subtitle {
        text-align: center;
        color: #6c757d;
        font-size: 1.1rem;
        margin-top: 0.5rem;
    }

    .import-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
    }

    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border: none;
    }

    .header-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        border-radius: 12px;
        padding: 0.6rem 1.2rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        font-weight: 500;
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .upload-area {
        border: 3px dashed #e0e6ed;
        border-radius: 20px;
        padding: 4rem 2rem;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: linear-gradient(135deg, #f8fbff 0%, #f0f4ff 100%);
        position: relative;
        overflow: hidden;
        margin: 2rem 0;
    }

    .upload-area::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(102, 126, 234, 0.05) 50%, transparent 70%);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }

    .upload-area:hover::before {
        transform: translateX(100%);
    }

    .upload-area:hover {
        border-color: #667eea;
        background: linear-gradient(135deg, #e7f1ff 0%, #f0f8ff 100%);
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
    }

    .upload-area.dragover {
        border-color: #667eea;
        background: linear-gradient(135deg, #e7f1ff 0%, #f0f8ff 100%);
        transform: scale(1.02);
        box-shadow: 0 25px 50px rgba(102, 126, 234, 0.2);
    }

    .upload-icon {
        font-size: 4rem;
        background: linear-gradient(45deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1.5rem;
        filter: drop-shadow(0 4px 8px rgba(102, 126, 234, 0.3));
    }

    .upload-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .upload-subtitle {
        color: #718096;
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    .btn-upload {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        border-radius: 15px;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .btn-upload:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-submit {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        border: none;
        color: white;
        border-radius: 15px;
        padding: 1rem 2.5rem;
        font-size: 1.2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(72, 187, 120, 0.3);
    }

    .btn-submit:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(72, 187, 120, 0.4);
        color: white;
    }

    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .instructions {
        background: linear-gradient(135deg, #fff7ed 0%, #fef5e7 100%);
        border: 1px solid #fed7aa;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 25px rgba(251, 146, 60, 0.1);
    }

    .instructions-title {
        color: #ea580c;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .instruction-section h6 {
        color: #7c2d12;
        font-weight: 600;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .instruction-section ul {
        color: #9a3412;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .instruction-section li {
        margin-bottom: 0.5rem;
        padding-left: 0.5rem;
    }

    .alert-custom {
        background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
        border: 1px solid #7dd3fc;
        border-radius: 15px;
        padding: 1.2rem;
        color: #0c4a6e;
        font-weight: 500;
    }

    .format-section {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 20px;
        padding: 2rem;
        margin-top: 3rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    .format-title {
        color: #4a5568;
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .format-table {
        font-size: 0.9rem;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
    }

    .format-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        padding: 1rem 0.8rem;
        border: none;
        font-size: 0.85rem;
    }

    .format-table td {
        padding: 0.8rem;
        border-bottom: 1px solid #e2e8f0;
        background: white;
        font-size: 0.85rem;
    }

    .format-table tbody tr:hover {
        background: #f7fafc;
    }

    .file-info {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border: 1px solid #6ee7b7;
        border-radius: 15px;
        padding: 1rem 1.5rem;
        color: #065f46;
        font-weight: 500;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
    }

    .progress-bar {
        height: 6px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .alert {
        border-radius: 15px;
        border: none;
        padding: 1.2rem 1.5rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .loading-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    @media (max-width: 768px) {
        .page-title {
            font-size: 2rem;
        }
        
        .upload-area {
            padding: 2rem 1rem;
        }
        
        .header-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn-glass {
            width: 100%;
            justify-content: center;
        }
        
        .format-table {
            font-size: 0.75rem;
        }
        
        .format-table th,
        .format-table td {
            padding: 0.5rem 0.3rem;
        }
    }
</style>

<body class="act-sales">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            <!-- Page Header -->
            <div class="page-header fade-in-up">
                <h1 class="page-title">
                    <i class="fas fa-cloud-upload-alt me-3"></i>
                    Excel Import Center
                </h1>
                <p class="page-subtitle">Transform your Excel data into actionable sales records</p>
            </div>

            <!-- Alert Messages -->
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show fade-in-up" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show fade-in-up" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('warning') }}
                @if (session('errors'))
                    <br><small>{{ session('errors') }}</small>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show fade-in-up" role="alert">
                <i class="fas fa-times-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show fade-in-up" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="import-card fade-in-up">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h1 class="header-title">
                        <i class="fas fa-file-excel"></i>
                        Sales Data Import
                    </h1>
                    <div class="header-actions">
                        <a href="{{ route('sales.import.template') }}" class="btn btn-glass">
                            <i class="fas fa-download me-2"></i> Download Template
                        </a>
                        <a href="{{ route('sales.index') }}" class="btn btn-glass">
                            <i class="fas fa-arrow-left me-2"></i> Back to Sales
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Instructions -->
                    <div class="instructions">
                        <h5 class="instructions-title">
                            <i class="fas fa-lightbulb"></i>
                            Smart Import Instructions
                        </h5>
                        <div class="row">
                            <div class="col-md-6 instruction-section">
                                <h6>📋 Preparation Steps:</h6>
                                <ul class="mb-3">
                                    <li>Download the template file first</li>
                                    <li>Use the exact column headers as shown</li>
                                    <li>Fill in your data following the sample format</li>
                                    <li>Save as Excel (.xlsx) or CSV format</li>
                                </ul>
                            </div>
                            <div class="col-md-6 instruction-section">
                                <h6>🚀 What gets imported:</h6>
                                <ul class="mb-3">
                                    <li><strong>Sales records</strong> with customer details</li>
                                    <li><strong>Invoices</strong> with GST calculations</li>
                                    <li><strong>New customers</strong> if not found</li>
                                    <li><strong>New products</strong> if not found</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="alert-custom mb-0">
                            <i class="fas fa-robot me-2"></i>
                            <strong>AI-Powered Matching:</strong> The system automatically matches existing customers by name or GST number, and products by name or item code. New records are created intelligently when no matches are found.
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('sales.import.excel') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h5 class="upload-title">Drag & drop your Excel file here</h5>
                            <p class="upload-subtitle">or click to browse files</p>
                            <input type="file" name="excel_file" id="excelFile" class="d-none" accept=".xlsx,.xls,.csv" required>
                            <button type="button" class="btn btn-upload" onclick="document.getElementById('excelFile').click()">
                                <i class="fas fa-folder-open me-2"></i>Choose File
                            </button>
                            <div id="fileName" class="mt-4 file-info" style="display: none;"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                <small><i class="fas fa-info-circle me-1"></i>Supported: Excel (.xlsx, .xls), CSV • Max: 10MB</small>
                            </div>
                            <button type="submit" class="btn btn-submit" id="submitBtn" disabled>
                                <i class="fas fa-rocket me-2"></i>Import Data
                            </button>
                        </div>
                    </form>

                    <!-- Format Reference -->
                    <div class="format-section">
                        <h5 class="format-title">
                            <i class="fas fa-table"></i>
                            Expected Data Format
                        </h5>
                        <div class="table-responsive">
                            <table class="table format-table">
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
                        <div class="alert-custom mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Format Note:</strong> The first row contains the main sale/invoice information, subsequent rows contain individual product details.
                        </div>
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
                showNotification('Please select a valid Excel or CSV file.', 'error');
                resetFileInput();
                return;
            }

            if (file.size > 10 * 1024 * 1024) { // 10MB
                showNotification('File size must be less than 10MB.', 'error');
                resetFileInput();
                return;
            }

            fileName.innerHTML = `
                <i class="fas fa-file-excel me-2"></i>
                <strong>Selected:</strong> ${file.name} 
                <span class="ms-2 badge bg-success">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
            `;
            fileName.style.display = 'block';
            submitBtn.disabled = false;

            // Add success animation
            fileName.classList.add('fade-in-up');
        }

        function resetFileInput() {
            fileInput.value = '';
            fileName.style.display = 'none';
            submitBtn.disabled = true;
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : 'success'} position-fixed`;
            notification.style.cssText = `
                top: 20px; 
                right: 20px; 
                z-index: 9999; 
                max-width: 400px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            `;
            notification.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Form submission with enhanced loading state
        document.getElementById('importForm').addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <div class="loading-spinner me-2"></div>
                Processing Import...
            `;
            
            // Show progress indication
            showNotification('Upload started! Please wait while we process your file.', 'info');
        });
    </script>
</body>
@include('layout.footer')