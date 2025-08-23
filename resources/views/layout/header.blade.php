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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet"> {{-- CDN
    --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet"> {{-- CDN
    --}}

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('assets/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />

    <!-- ADD SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>

<body>
    <div class="container-fluid position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner"
            class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Sidebar Start -->
        <div class="sidebar pe-7 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="{{ route('dashboard') }}" class="navbar-brand mx-4 mb-3">
                    <h5 class="text-primary">MAULI SOLUTIONS</h5>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="{{ asset('assets/img/user-profile.png') }}" alt=""
                            style="width: 40px; height: 40px;">
                        <div
                            class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1">
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                        <span>{{ auth()->user()->role }}</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="{{ route('dashboard') }}"
                        class="nav-item nav-link act-dash {{ Route::is('dashboard') ? 'active' : '' }}"><i
                            class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle act-product" data-bs-toggle="dropdown"><i
                                class="fa fa-laptop me-2"></i>Products</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="{{ route('products.index') }}" class="dropdown-item">Products</a>
                            <a href="{{ route('products.create') }}" class="dropdown-item">Add Product</a>
                        </div>
                    </div>
                    
                    <a href="{{ route('parties.index') }}"
                        class="nav-item nav-link act-parties {{ Route::is('parties.index') ? 'active' : '' }}"><i
                            class="fa fa-users me-2"></i>Parties</a>
                    <a href="{{ route('customers.index') }}"
                        class="nav-item nav-link act-customers {{ Route::is('customers.index') ? 'active' : '' }}"><i
                            class="fa fa-keyboard me-2"></i>Customers</a>
                    <!-- <a href="{{ route('sales.index') }}"
                        class="nav-item nav-link act-sales {{ Route::is('sales.index') ? 'active' : '' }}"><i
                            class="fa fa-table me-2"></i>Sales</a> -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle act-po" data-bs-toggle="dropdown"><i
                                class="fa fa-shopping-cart me-2"></i>Purchase Orders</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="{{ route('purchase_orders.index') }}" class="dropdown-item">All Purchase Orders</a>
                            <a href="{{ route('purchase_orders.create') }}" class="dropdown-item">Create Purchase
                                Order</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle act-payments" data-bs-toggle="dropdown"><i
                                class="fa fa-credit-card me-2"></i>Payments</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="{{ route('payables') }}" class="dropdown-item">Payables</a>
                            <a href="{{ route('receivables') }}" class="dropdown-item">Receivables</a>
                        </div>
                    </div>
                    <a href="{{ route('purchase_entries.index') }}"
                        class="nav-item nav-link act-purchaseentries {{ Route::is('purchase_entries.index') ? 'active' : '' }}"><i
                            class="fa fa-file-invoice me-2"></i>Purchase Entries</a>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle act-receiptnotes" data-bs-toggle="dropdown"><i
                                class="fa fa-receipt me-2"></i>Receipt Note</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="{{ route('receipt_notes.index') }}" class="dropdown-item">All Receipt Notes</a>
                            <a href="{{ route('receipt_notes.create') }}" class="dropdown-item">Create Receipt Note</a>
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle act-deliverynotes" data-bs-toggle="dropdown"><i
                                class="fa fa-truck me-2"></i>Delivery Note</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="{{ route('delivery_notes.index') }}" class="dropdown-item">All Delivery Notes</a>
                            <a href="{{ route('delivery_notes.create') }}" class="dropdown-item">Create Delivery Note</a>
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle act-invoice" data-bs-toggle="dropdown"><i
                                class="far fa-file-alt me-2"></i>Invoices</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="{{ route('invoices.index') }}" class="dropdown-item">All Invoices</a>
                            @if (Auth::check() && Auth::user()->role === 'superadmin')
                            <a href="{{ route('invoices.pending') }}" class="dropdown-item">Pending Invoices</a>
                            @endif
                            <a href="{{ route('invoices.create') }}" class="dropdown-item">Create Invoice</a>
                        </div>
                    </div>
                    <a href="{{ route('quantra.index') }}"
                        class="nav-item nav-link act-quantra {{ Route::is('quantra.index') ?'active' : '' }}"><i class="fa fa-table me-2"></i>Quantra
                    </a>
                    <a href="{{ route('enquiry.index') }}"
                        class="nav-item nav-link act-enquiry {{ Route::is('enquiry.index') ? 'active' : '' }}"><i class="fa fa-question-circle"></i>Enquiry</a>
                    
                    {{-- Reports Section --}}
                    @if (Auth::check() && Auth::user()->role === 'superadmin')
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle act-reports" data-bs-toggle="dropdown"><i
                                class="fa fa-chart-bar me-2"></i>Reports</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="{{ route('reports.profit_loss') }}" class="dropdown-item">Sales & Profit Analysis</a>
                            <a href="{{ route('reports.category_wise') }}" class="dropdown-item">Category-Wise Report</a>
                        </div>
                    </div>
                    @endif
                    
                    @if (Auth::check() && Auth::user()->role === 'manager')
                    <a href="{{ route('staff.index') }}"
                        class="nav-item nav-link act-staff {{ Route::is('staff.index') ? 'active' : '' }}"><i
                            class="fa fa-users me-2"></i>Staff</a>
                    @endif
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            {{-- This is the TOP NAVBAR, not the sidebar --}}
<nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
    <a href="{{ route('dashboard') }}" class="navbar-brand d-flex d-lg-none me-4">
        <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
    </a>
    <a href="#" class="sidebar-toggler flex-shrink-0">
        <i class="fa fa-bars"></i>
    </a>
    <!-- <form class="d-none d-md-flex ms-4">
        <input class="form-control border-0" type="search" placeholder="Search">
    </form> -->
    <div class="navbar-nav align-items-center ms-auto">
        {{-- Messages Dropdown --}}
        <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
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
                            @if (!$loop->last) <hr class="dropdown-divider my-1"> @endif
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
                    <i class="fa fa-bell me-lg-2"></i>
                    <span class="d-none d-lg-inline-flex">Notifications</span>
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
                           @if (!$loop->last && !($loop->iteration >= 5)) <hr class="dropdown-divider my-1"> @endif
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
                <img class="rounded-circle me-lg-2" src="{{ asset('assets/img/user-profile.png') }}" alt="User Profile" style="width: 40px; height: 40px;">
                <span class="d-none d-lg-inline-flex">{{ Auth::user()->name ?? 'Guest' }}</span>
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
<style>
.truncate-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block; /* Ensure it behaves like a block for width constraints */
}
.dropdown-menu { /* Ensure scrollability for long lists */
    max-height: 400px; /* Or your preferred max height */
    overflow-y: auto;
}
.dropdown-header.sticky-top, .dropdown-item.sticky-bottom {
    position: sticky;
    z-index: 1021; /* Higher than dropdown items */
}
.dropdown-header.sticky-top { top: 0; }
.dropdown-item.sticky-bottom { bottom: 0; }
</style>
            <!-- Navbar End -->