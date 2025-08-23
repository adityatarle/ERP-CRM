<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Mauli Solutions</title> {{-- Or use a dynamic title: <title>{{ $title ?? 'MAULI' }}</title>
    --}}
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="{{ asset('assets/img/favicon.ico') }}" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> {{-- CDN
    --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet"> {{-- CDN
    --}}

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('assets/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <style>
        .card-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            height: 100%;
        }

        .card i {
            font-size: 1.5rem;
        }

        .quick-action-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background-color: #f8f9fa;
            overflow: visible;
            position: relative;
            z-index: 1;
        }

        .quick-action-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            z-index: 2;
        }

        .quick-action-card .card-body {
            padding: 1rem;
        }

        .quick-action-card i {
            color: #007bff;
        }

        .quick-action-card span {
            font-size: 1rem;
        }

        /* Hover List Styles */
        .hover-list {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            z-index: 10;
            border: 1px solid #e9ecef;
            background-color: #ffffff;
            border-radius: 8px;
            margin-top: 5px;
            padding: 0.5rem 0;
        }

        .quick-action-card:hover .hover-list {
            display: block;
            /* Show on hover */
        }

        .hover-list li {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            color: #333;
            transition: background-color 0.2s ease;
        }

        .hover-list li:hover {
            background-color: #f1f3f5;
        }

        .hover-list i {
            color: #007bff;
            font-size: 0.9rem;
        }

        .hover-list a {
            display: flex;
            align-items: center;
        }

        @media (max-width: 576px) {
            .quick-action-card .card-body {
                padding: 0.75rem;
            }

            .quick-action-card i {
                font-size: 1.5rem;
            }

            .quick-action-card span {
                font-size: 0.9rem;
            }

            .hover-list li {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
        }

        .shortcut-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            min-height: 120px;
            /* Ensures all cards have a minimum height */
        }

        .shortcut-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* 4/7/25 */
        .card-subtle-primary {
            background-color: #e6f0fa !important;
            color: #1a73e8 !important;
        }

        .card-subtle-info {
            background-color: #e7f3fe !important;
            color: #007bff !important;
        }

        .card-subtle-secondary {
            background-color: #f1f3f5 !important;
            color: #6c757d !important;
        }

        .card-subtle-success {
            background-color: #e6f4ea !important;
            color: #28a745 !important;
        }

        .card-subtle-warning {
            background-color: #fff4e5 !important;
            color: #f0ad4e !important;
        }

        .card-subtle-danger {
            background-color: #fce8e6 !important;
            color: #dc3545 !important;
        }

        .card-subtle-primary:hover,
        .card-subtle-info:hover,
        .card-subtle-secondary:hover,
        .card-subtle-success:hover,
        .card-subtle-warning:hover,
        .card-subtle-danger:hover {
            filter: brightness(95%);
        }
    </style>
</head>

<body class="act-dash">
    <div class="">
        <!-- Navbar Start -->
        {{-- This is the TOP NAVBAR, not the sidebar --}}
        <nav class="navbar navbar-expand sticky-top px-4 py-0" style="background-color: #0050a0;">
            <a href="{{ route('dashboard') }}" class="navbar-brand d-flex d-lg-none me-4">
                <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
            </a>
            <form class="d-none d-md-flex ms-4">
                <input class="form-control border-0" type="search" placeholder="Search">
            </form>
            <div class="navbar-nav align-items-center ms-auto">
                {{-- Messages Dropdown --}}
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown">
                        <i class="fa fa-envelope me-lg-2"></i>
                        <span class="d-none d-lg-inline-flex">Message</span>
                        {{-- Add message count badge if you have this functionality --}}
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        {{-- Example Static Message Items --}}
                        <a href="#" class="dropdown-item">
                            <div class="d-flex align-items-center">
                                <img class="rounded-circle" src="{{ asset('assets/img/user-profile.png') }}" alt="" style="width: 40px; height: 40px;">
                                <div class="ms-2">
                                    <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                    <small>15 minutes ago</small>
                                </div>
                            </div>
                        </a>
                        <hr class="dropdown-divider">
                        <a href="#" class="dropdown-item text-center">See all message</a>
                    </div>
                </div>

                {{-- Notifications Logic --}}
                @if (Auth::check())
                @php
                $currentUser = Auth::user();
                $allUnreadNotifications = $currentUser->unreadNotifications;

                $unreadUnlockRequests = collect();
                if ($currentUser->role === 'superadmin') {
                $unreadUnlockRequests = $allUnreadNotifications->filter(function ($notification) {
                return $notification->type === App\Notifications\InvoiceUnlockRequested::class;
                });
                }
                $unreadUnlockRequestsCount = $unreadUnlockRequests->count();
                $unlockRequestsForDisplay = $unreadUnlockRequests->take(5);

                $totalUnreadCount = $allUnreadNotifications->count();
                @endphp

                {{-- Unlock Requests Dropdown (Only for Superadmin if there are requests) --}}
                @if ($currentUser->role === 'superadmin' && $unreadUnlockRequestsCount > 0)
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" title="Unlock Requests">
                        <i class="fa fa-key me-lg-2 text-warning"></i>
                        <span class="d-none d-lg-inline-flex">Unlocks</span>
                        <span class="badge bg-warning rounded-pill ms-1 text-dark">{{ $unreadUnlockRequestsCount }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                        <h6 class="dropdown-header sticky-top bg-light py-2">Pending Invoice Unlock Requests</h6>
                        @forelse ($unlockRequestsForDisplay as $notification)
                        <a href="{{ $notification->data['action_url'] ?? '#' }}?notification_id={{ $notification->id }}" class="dropdown-item">
                            <div>
                                <small class="fw-bold"><i class="fa fa-file-invoice me-1"></i> {{ $notification->data['invoice_number'] ?? 'N/A' }}</small>
                                <small class="float-end text-muted">{{ $notification->created_at->diffForHumans(null, true, true) }}</small>
                            </div>
                            <div class="small text-muted truncate-text" title="{{ $notification->data['message'] ?? 'New unlock request.' }}">
                                {{ Str::limit($notification->data['message'] ?? 'New unlock request.', 70) }}
                            </div>
                        </a>
                        @if (!$loop->last)
                        <hr class="dropdown-divider my-1"> @endif
                        @empty
                        <span class="dropdown-item text-center text-muted">No new unlock requests</span>
                        @endforelse
                        @if ($unreadUnlockRequestsCount > 0)
                        <hr class="dropdown-divider my-0">
                        <a href="{{ route('invoices.pending_unlock_requests') }}" class="dropdown-item text-center py-2 bg-light-subtle sticky-bottom">
                            <small>View All Pending Unlock Requests</small>
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                {{-- General Notification Bell --}}
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" title="Notifications">
                        <i class="fa fa-bell me-lg-2 text-white"></i>
                        <span class="d-none d-lg-inline-flex text-white">Notifications</span>
                        @if ($totalUnreadCount > 0)
                        <span class="badge bg-danger rounded-pill ms-1">{{ $totalUnreadCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                        <h6 class="dropdown-header sticky-top bg-light py-2">Recent Notifications</h6>
                        @if ($allUnreadNotifications->isEmpty())
                        <span class="dropdown-item text-center text-muted">No new notifications</span>
                        @else
                        @foreach ($allUnreadNotifications->take(5) as $notification)
                        @php
                        $isUnlockRequestType = $notification->type === App\Notifications\InvoiceUnlockRequested::class;
                        // For superadmin, if it's an unlock request, they might have already seen it in the dedicated dropdown
                        // You could choose to skip it here or display it differently
                        // if ($isUnlockRequestType && $currentUser->role === 'superadmin') continue;

                        $iconClass = $isUnlockRequestType ? "fa-key text-warning" : "fa-info-circle text-primary";
                        $title = $isUnlockRequestType ? ($notification->data['invoice_number'] ?? 'Unlock Request') : 'Notification';
                        $message = Str::limit($notification->data['message'] ?? 'New notification.', 70);
                        $actionUrl = $notification->data['action_url'] ?? '#';
                        if (strpos($actionUrl, '?') === false) {
                        $actionUrl .= '?notification_id='.$notification->id;
                        } else {
                        $actionUrl .= '¬ification_id='.$notification->id;
                        }
                        @endphp
                        <a href="{{ $actionUrl }}" class="dropdown-item">
                            <div>
                                <small class="fw-bold"><i class="fa {{ $iconClass }} me-1"></i> {{ $title }}</small>
                                <small class="float-end text-muted">{{ $notification->created_at->diffForHumans(null, true, true) }}</small>
                            </div>
                            <div class="small text-muted truncate-text" title="{{ $notification->data['message'] ?? 'New notification.' }}">
                                {{ $message }}
                            </div>
                        </a>
                        @if (!$loop->last && !($loop->iteration >= 5))
                        <hr class="dropdown-divider my-1"> @endif
                        @endforeach
                        @if ($totalUnreadCount > 0)
                        <hr class="dropdown-divider my-0">
                        <a href="#" class="dropdown-item text-center py-2 bg-light-subtle sticky-bottom"> {{-- Link to an "all notifications" page --}}
                            <small>View All Notifications</small>
                        </a>
                        @endif
                        @endif
                    </div>
                </div>
                @endif

                {{-- Profile Dropdown --}}
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <img class="rounded-circle me-lg-2" src="{{ asset('assets/img/user-profile.png') }}" alt="" style="width: 40px; height: 40px;">
                        <span class="d-none d-lg-inline-flex text-white">{{ Auth::user()->name ?? 'Guest' }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                        <a href="#" class="dropdown-item">My Profile</a>
                        <a href="#" class="dropdown-item">Settings</a>
                        <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Log Out
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </nav>
        <!-- TOP HEADER END -->
        <div class="main-content-area">
            <div class="container-fluid py-4 px-4">
                <div class="container-fluid">
                    <!-- New sidebar start -->
                    <div class="bg-white shadow rounded-3 p-4">
                        <h5 class="fw-bold text-dark mb-4">Menu</h5>
                        <div class="row">
                            <div class="col-12">
                                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                                    <!-- Dashboard with Hover List -->
                                    <div class="col">
                                        <div class="quick-action-card card h-100 text-decoration-none position-relative {{ Route::is('dashboard') ? 'active' : '' }}">
                                            <a href="{{ route('dashboard') }}" class="card-body d-flex align-items-center">
                                                <i class="fas fa-tachometer-alt fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Dashboard</span>
                                            </a>
                                        </div>
                                    </div>
                                    <!-- Products with Hover List -->
                                    <div class="col">
                                        <div class="quick-action-card card h-100 text-decoration-none position-relative">
                                            <a href="#" class="card-body d-flex align-items-center">
                                                <i class="fas fa-box fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Products</span>
                                            </a>
                                            <!-- Hover List -->
                                            <ul class="hover-list list-unstyled bg-white shadow-sm rounded p-2">
                                                <li><a href="{{ route('products.index') }}" class="text-decoration-none text-dark"><i class="fas fa-list me-2"></i>Products</a></li>
                                                <li><a href="{{ route('products.create') }}" class="text-decoration-none text-dark"><i class="fas fa-plus-circle me-2"></i>Add Product</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- Parties -->
                                    <div class="col">
                                        <a href="{{ route('parties.index') }}" class="{{ Route::is('parties.index') ? 'active' : '' }} quick-action-card card h-100 text-decoration-none">
                                            <div class="card-body d-flex align-items-center">
                                                <i class="fas fa-users fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Parties</span>
                                            </div>
                                        </a>
                                    </div>
                                    <!-- Customers -->
                                    <div class="col">
                                        <a href="{{ route('customers.index') }}" class="{{ Route::is('customers.index') ? 'active' : '' }} quick-action-card card h-100 text-decoration-none">
                                            <div class="card-body d-flex align-items-center">
                                                <i class="fas fa-user-friends fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Customers</span>
                                            </div>
                                        </a>
                                    </div>
                                    <!-- Purchase Orders -->
                                    <div class="col">
                                        <div class="quick-action-card card h-100 text-decoration-none position-relative">
                                            <a href="#" class="card-body d-flex align-items-center">
                                                <i class="fas fa-file-invoice fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Purchase Orders</span>
                                            </a>
                                            <ul class="hover-list list-unstyled bg-white shadow-sm rounded p-2">
                                                <li><a href="{{ route('purchase_orders.index') }}" class="text-decoration-none text-dark"><i class="fas fa-list me-2"></i>All Purchase Order</a></li>
                                                <li><a href="{{ route('purchase_orders.create') }}" class="text-decoration-none text-dark"><i class="fas fa-file-invoice me-2"></i>Create Purchase Order</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- Payments -->
                                    <div class="col">
                                        <div class="quick-action-card card h-100 text-decoration-none position-relative">
                                            <a href="#" class="card-body d-flex align-items-center">
                                                <i class="fas fa-money-bill-wave fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Payments</span>
                                            </a>
                                            <ul class="hover-list list-unstyled bg-white shadow-sm rounded p-2">
                                                <li><a href="{{ route('payables') }}" class="text-decoration-none text-dark"><i class="fas fa-money-check-alt me-2"></i>Payables</a></li>
                                                <li><a href="{{ route('receivables') }}" class="text-decoration-none text-dark"><i class="fas fa-hand-holding-dollar me-2"></i>Receivables</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- Purchase Entries -->
                                    <div class="col">
                                        <a href="{{ route('purchase_entries.index') }}" class="quick-action-card card h-100 text-decoration-none">
                                            <div class="card-body d-flex align-items-center">
                                                <i class="fas fa-receipt fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Purchase Entries</span>
                                            </div>
                                        </a>
                                    </div>
                                    <!-- Receipt Note -->
                                    <div class="col">
                                        <div class="quick-action-card card h-100 text-decoration-none position-relative">
                                            <a href="#" class="card-body d-flex align-items-center">
                                                <i class="fas fa-clipboard-check fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Receipt Note</span>
                                            </a>
                                            <ul class="hover-list list-unstyled bg-white shadow-sm rounded p-2">
                                                <li><a href="{{ route('receipt_notes.index') }}" class="text-decoration-none text-dark"><i class="fas fa-list me-2"></i>All Receipts</a></li>
                                                <li><a href="{{ route('receipt_notes.create') }}" class="text-decoration-none text-dark"><i class="fas fa-receipt me-2"></i>Create Receipt</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- Delivery Note -->
                                    <div class="col">
                                        <div class="quick-action-card card h-100 text-decoration-none position-relative">
                                            <a href="#" class="card-body d-flex align-items-center">
                                                <i class="fas fa-truck fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Delivery Note</span>
                                            </a>
                                            <ul class="hover-list list-unstyled bg-white shadow-sm rounded p-2">
                                                <li><a href="{{ route('delivery_notes.index') }}" class="text-decoration-none text-dark"><i class="fas fa-list me-2"></i>View Delivery Notes</a></li>
                                                <li><a href="{{ route('delivery_notes.create') }}" class="text-decoration-none text-dark"><i class="fas fa-truck me-2"></i>Create Delivery Note</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- Invoices -->
                                    <div class="col">
                                        <div class="quick-action-card card h-100 text-decoration-none position-relative">
                                            <a href="#" class="card-body d-flex align-items-center">
                                                <i class="fas fa-file-invoice-dollar fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Invoices</span>
                                            </a>
                                            <ul class="hover-list list-unstyled bg-white shadow-sm rounded p-2">
                                                <li><a href="{{ route('invoices.index') }}" class="text-decoration-none text-dark"><i class="fas fa-list me-2"></i>All Invoices</a></li>
                                                <li><a href="{{ route('invoices.create') }}" class="text-decoration-none text-dark"><i class="fas fa-file-invoice-dollar me-2"></i>Create Invoice</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- Quantra -->
                                    <div class="col">
                                        <a href="{{ route('quantra.index') }}" class="{{ Route::is('quantra.index') ?'active' : '' }} quick-action-card card h-100 text-decoration-none">
                                            <div class="card-body d-flex align-items-center">
                                                <i class="fas fa-cubes fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Quantra</span>
                                            </div>
                                        </a>
                                    </div>
                                    <!-- Enquiry -->
                                    <div class="col">
                                        <a href="{{ route('enquiry.index') }}" class="{{ Route::is('enquiry.index') ? 'active' : '' }} quick-action-card card h-100 text-decoration-none">
                                            <div class="card-body d-flex align-items-center">
                                                <i class="fas fa-question-circle fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Enquiry</span>
                                            </div>
                                        </a>
                                    </div>
                                    <!-- Staff -->
                                    <div class="col">
                                        <a href="{{ route('staff.index') }}" class="{{ Route::is('staff.index') ? 'active' : '' }} quick-action-card card h-100 text-decoration-none">
                                            <div class="card-body d-flex align-items-center">
                                                <i class="fas fa-user-tie fa-2x text-primary me-3"></i>
                                                <span class="text-dark fw-medium">Staff</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- New sidebar end -->

                    <!-- Financial Summary Start -->
                    <div class="container-fluid pt-4">
                        <div class="row g-4">
                            @if (Auth::check() && Auth::user()->role === 'superadmin')
                            <div class="col-sm-6 col-xl-3">
                                <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                                    <i class="fa fa-chart-line fa-3x text-primary"></i>
                                    <div class="ms-3">
                                        <p class="mb-2">Total Revenue</p>
                                        <h6 class="mb-0">₹{{ number_format($totalRevenue ?? 0, 2) }}</h6>
                                    </div>
                                </div>
                            </div>

                            @if (auth()->user()->isSuperAdmin())
                            <div class="col-sm-6 col-xl-3">
                                <a href="{{ route('reports.profit_loss') }}" class="text-decoration-none">
                                    <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                                        <i class="fa fa-dollar-sign fa-3x text-info"></i>
                                        <div class="ms-3 text-end">
                                            <p class="mb-2">Sales Analysis</p>
                                            <h6 class="mb-0">View P/L Report</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            @endif
                            <div class="col-sm-6 col-xl-3">
                                <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                                    <i class="fa fa-credit-card fa-3x text-primary"></i>
                                    <div class="ms-3">
                                        <p class="mb-2">Total Quantra Expenses</p>
                                        <h6 class="mb-0">{{ number_format($totalQuantraExpenses ?? 0, 2) }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                                    <i class="fa fa-chart-line fa-3x text-primary"></i>
                                    <div class="ms-3">
                                        <p class="mb-2">Net Profit</p>
                                        <h6 class="mb-0">{{ number_format($netProfit ?? 0, 2) }}</h6>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- Financial Summary End -->

                    <!-- Quick Actions / Shortcuts -->
                    <div class="container-fluid pt-5">
                        <div class="bg-white shadow-sm rounded-3 p-4">
                            <h5 class="fw-bold text-dark mb-4">Quick Actions</h5>
                            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
                                @php
                                $shortcuts = [
                                ['route' => 'invoices.create', 'icon' => 'fa-file-invoice', 'label' => 'Create Invoice', 'color' => 'subtle-primary'],
                                ['route' => 'delivery_notes.create', 'icon' => 'fa-truck', 'label' => 'Create Delivery Note', 'color' => 'subtle-success'],
                                ['route' => 'products.index', 'icon' => 'fa-laptop', 'label' => 'View Products', 'color' => 'subtle-warning'],
                                ['route' => 'customers.index', 'icon' => 'fa-keyboard', 'label' => 'View Customers', 'color' => 'subtle-info'],
                                ['route' => 'purchase_orders.create', 'icon' => 'fa-shopping-cart', 'label' => 'New Purchase Order', 'color' => 'subtle-danger', 'role' => ['superadmin', 'manager']],
                                ['route' => 'purchase_entries.index', 'icon' => 'fa-file-invoice-dollar', 'label' => 'Purchase Entries', 'color' => 'subtle-secondary', 'role' => ['superadmin', 'manager']],
                                ['route' => 'receivables', 'icon' => 'fa-hand-holding-usd', 'label' => 'Receivables', 'color' => 'subtle-primary', 'role' => ['superadmin', 'manager']],
                                ['route' => 'payables', 'icon' => 'fa-money-bill-wave', 'label' => 'Payables', 'color' => 'subtle-success', 'role' => ['superadmin', 'manager']],
                                ['route' => 'reports.category_wise', 'icon' => 'fa-chart-bar', 'label' => 'Category Report', 'color' => 'subtle-info', 'role' => ['superadmin']],
                                ['route' => 'reports.profit_loss', 'icon' => 'fa-chart-line', 'label' => 'Profit Analysis', 'color' => 'subtle-warning', 'role' => ['superadmin']],
                                ];
                                @endphp

                                @foreach ($shortcuts as $shortcut)
                                @if (empty($shortcut['role']) || (Auth::check() && in_array(Auth::user()->role, $shortcut['role'])))
                                <div class="col">
                                    <a href="{{ route($shortcut['route']) }}" class="text-decoration-none">
                                        <div class="card border-0 shadow-sm h-100 card-{{ $shortcut['color'] }} transition-all hover-scale">
                                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                                                <i class="fa {{ $shortcut['icon'] }} fa-2x mb-2"></i>
                                                <span class="fs-6 fw-medium">{{ $shortcut['label'] }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <!-- End of Quick Actions -->

                    <!-- Reports Section -->
                    @if (Auth::check() && Auth::user()->role === 'superadmin')
                    <div class="container-fluid pt-4">
                        <div class="bg-white shadow-sm rounded-3 p-4">
                            <h5 class="fw-bold text-dark mb-4">
                                <i class="fa fa-chart-bar me-2 text-primary"></i>Business Reports
                            </h5>
                            <div class="row row-cols-1 row-cols-md-2 g-4">
                                <div class="col">
                                    <a href="{{ route('reports.category_wise') }}" class="text-decoration-none">
                                        <div class="card border-0 shadow-sm h-100 card-subtle-info transition-all hover-scale">
                                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                                                <i class="fa fa-chart-bar fa-3x mb-3 text-info"></i>
                                                <h6 class="fw-bold mb-2">Category-Wise Business Report</h6>
                                                <p class="text-muted text-center mb-0">Analyze performance by product categories with detailed insights</p>
                                                <div class="mt-3">
                                                    <span class="badge bg-info text-white">Revenue Analysis</span>
                                                    <span class="badge bg-success text-white ms-1">Profit Tracking</span>
                                                    <span class="badge bg-warning text-white ms-1">Excel Export</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col">
                                    <a href="{{ route('reports.profit_loss') }}" class="text-decoration-none">
                                        <div class="card border-0 shadow-sm h-100 card-subtle-warning transition-all hover-scale">
                                            <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                                                <i class="fa fa-chart-line fa-3x mb-3 text-warning"></i>
                                                <h6 class="fw-bold mb-2">Sales & Profit Analysis</h6>
                                                <p class="text-muted text-center mb-0">Comprehensive sales performance and profit/loss analysis</p>
                                                <div class="mt-3">
                                                    <span class="badge bg-warning text-white">Sales Analysis</span>
                                                    <span class="badge bg-success text-white ms-1">Profit/Loss</span>
                                                    <span class="badge bg-primary text-white ms-1">Product Performance</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Main Content with Tables -->
                    <!-- STRUCTURE: Wrap tables in a container and row for proper layout -->
                    <!-- Dashboard Tables -->
                    <div class="container-fluid py-5">
                        <div class="row g-4">
                            <!-- Low Stock Products Table -->
                            <div class="col-md-6">
                                <div class="bg-white shadow-sm rounded-3 p-4 h-100">
                                    <h5 class="fw-bold text-dark mb-4">Low Stock Products</h5>
                                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                        <table class="table table-striped table-borderless">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-start">Name</th>
                                                    <th scope="col" class="text-start">Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($lowStockProducts as $product)
                                                <tr>
                                                    <td class="text-start">{{ $product->name }}</td>
                                                    <td
                                                        class="{{ $product->stock < 3 ? 'text-danger' : ($product->stock < 10 ? 'text-warning' : 'text-dark') }} fw-bold">
                                                        {{ $product->stock }}
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">No low stock products</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Sales Table -->
                            <div class="col-md-6">
                                <div class="bg-white shadow-sm rounded-3 p-4 h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <h5 class="fw-bold text-dark mb-0">Recent Sales</h5>
                                        @if(Route::has('invoices.index'))
                                        <a href="{{ route('invoices.index') }}"
                                            class="text-primary text-decoration-none fw-medium">Show All</a>
                                        @endif
                                    </div>
                                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                        <table class="table table-borderless table-hover align-middle">
                                            <thead>
                                                <tr class="text-dark">
                                                    <th scope="col" class="text-start">Date</th>
                                                    <th scope="col" class="text-start">Customer</th>
                                                    <th scope="col" class="text-start">Total</th>
                                                    <th scope="col" class="text-start">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($recentSales as $sale)
                                                <tr>
                                                    <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                                    <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                                    <td>₹{{ number_format($sale->total_price, 2) }}</td>
                                                    <td>
                                                        @if(Route::has('sales.show'))
                                                        <a class="btn btn-sm btn-outline-primary"
                                                            href="{{ route('sales.show', $sale->id) }}">Detail</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No recent sales</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End of Dashboard Tables -->
                    <!-- End of Main Content -->
                </div>
            </div>
        </div>
    </div>
</body>

@include('layout.footer')