<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Inventory - @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f6f9;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }
        .layout-wrap {
            background-color: #d5d5d5;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .section-menu-left {
            padding: 20px;
        }
        .center {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .menu-list {
            list-style: none;
            padding: 0;
            width: 100%;
        }
        .menu-item {
            margin-bottom: 10px;
        }
        .menu-item-button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.2s;
        }
    
        .menu-item-button i {
            font-size: 1.2rem;
        }
        .container {
            margin-left: 250px;
            transition: margin-left 0.3s ease;
            padding: 20px;
        }
        .navbar-mobile {
            display: none;
            background: #d5d5d5;
            padding: 15px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1100;
        }
        .navbar-mobile .navbar-toggler {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        footer {
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .layout-wrap {
                transform: translateX(-100%);
                width: 200px;
            }
            .layout-wrap.active {
                transform: translateX(0);
            }
            .container, footer {
                margin-left: 0;
                width: 100%;
            }
            .navbar-mobile {
                display: block;
            }
            .container {
                padding-top: 60px;
            }
            .overlay.active {
                display: block;
            }
            .row {
                margin-left: 0;
                margin-right: 0;
            }
            .col-3, .col-9 {
                padding-left: 0;
                padding-right: 0;
            }
        }
        @media (max-width: 576px) {
            .layout-wrap {
                width: 180px;
            }
            .section-menu-left {
                padding: 15px;
            }
            .menu-item-button {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
            .menu-item-button i {
                font-size: 1rem;
                margin-left: 20px;
            }
            .container {
                padding: 15px;
            }
            .navbar-mobile .navbar-toggler {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Mobile Navbar -->
    <div class="navbar-mobile">
        <button class="navbar-toggler" onclick="toggleSidebar()">
            <i class="fas fa-bars text-dark"></i>
        </button>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div class="overlay" onclick="toggleSidebar()"></div>

    <div class="row">
        <div class="layout-wrap col-3 p-0">
            <div class="section-menu-left">
                <div class="center">
                    <div class="center-item">
                        <ul class="menu-list">
                            <li class="menu-item">
                                <a href="{{ route('dashboard') }}" class="menu-item-button {{ Route::is('dashboard') ? 'active' : '' }}">
                                    <span class="text my-1">Dashboard</span>
                                    <i class="fa fa-dashboard text-dark"></i>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="{{ route('products.index') }}" class="menu-item-button {{ Route::is('products.*') ? 'active' : '' }}">
                                    <span class="text my-1">Products</span>
                                    <i class="fas fa-box text-dark"></i>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="{{ route('customers.index') }}" class="menu-item-button {{ Route::is('customers.*') ? 'active' : '' }}">
                                    <span class="text my-1">Customers</span>
                                    <i class="fas fa-user text-dark"></i>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="{{ route('sales.index') }}" class="menu-item-button {{ Route::is('sales.*') ? 'active' : '' }}">
                                    <span class="text my-1">Sales</span>
                                    <i class="fas fa-chart-line text-dark"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-4 mb-5">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 8px;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="card shadow-sm" style="border: none; border-radius: 10px; background-color: #ffffff;">
                <div class="card-body p-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p class="mb-0" style="font-size: 0.9rem;">© {{ date('Y') }} CRM Inventory. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.layout-wrap');
            const overlay = document.querySelector('.overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Close sidebar when clicking nav links on mobile
        document.querySelectorAll('.menu-item-button').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleSidebar();
                }
            });
        });

        // Close sidebar when resizing to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                document.querySelector('.layout-wrap').classList.remove('active');
                document.querySelector('.overlay').classList.remove('active');
            }
        });
    </script>
</body>
</html>