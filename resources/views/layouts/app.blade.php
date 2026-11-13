<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Subsidi Pupuk Petani')</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --app-primary: #16a34a;
            --app-primary-dark: #15803d;
            --app-primary-soft: #ecfdf5;
            --app-bg: #f7f9fc;
            --app-border: #e8edf3;
            --app-text: #1f2937;
            --app-muted: #6b7280;
            --app-radius: 14px;
        }

        html, body { min-height: 100%; margin: 0; }
        body {
            overflow-x: hidden;
            background: var(--app-bg);
            color: #374151;
            font-size: .9rem;
        }

        #wrapper { display: flex; width: 100%; min-height: 100vh; }
        #content-wrapper {
            width: calc(100% - 240px);
            margin-left: 240px;
            min-height: 100vh;
            background: var(--app-bg);
        }
        #content { width: 100%; min-height: 100vh; }
        .app-page-shell { padding: 0 24px 32px; }

        .btn { border-radius: 9px; font-weight: 600; }
        .btn-success { background-color: var(--app-primary); border-color: var(--app-primary); }
        .btn-success:hover, .btn-success:focus {
            background-color: var(--app-primary-dark);
            border-color: var(--app-primary-dark);
        }
        .text-success { color: var(--app-primary) !important; }
        .form-control { border-radius: 9px; border-color: #dfe5ec; }
        .form-control:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 .15rem rgba(34, 197, 94, .12);
        }

        .app-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }
        .app-page-title {
            margin: 0;
            color: var(--app-text);
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.25;
        }
        .app-page-subtitle {
            margin: 5px 0 0;
            color: #7c8798;
            font-size: .82rem;
        }
        .app-back-link {
            display: inline-flex;
            align-items: center;
            color: #6b7280;
            font-size: .76rem;
            font-weight: 600;
            text-decoration: none !important;
            margin-bottom: 10px;
        }
        .app-back-link:hover { color: var(--app-primary-dark); }

        .app-card {
            border: 0;
            border-radius: var(--app-radius);
            box-shadow: 0 4px 18px rgba(15, 23, 42, .055);
            background: #fff;
            overflow: hidden;
        }
        .app-card-header {
            padding: 16px 18px;
            background: #fff;
            border-bottom: 1px solid #edf1f5;
        }
        .app-card-title { color: var(--app-text); font-weight: 750; margin: 0; }
        .app-card-subtitle { color: #8490a1; font-size: .75rem; margin-top: 3px; }

        .app-toolbar {
            padding: 15px;
            background: #fff;
            border: 0;
            border-radius: var(--app-radius);
            box-shadow: 0 4px 18px rgba(15, 23, 42, .055);
            margin-bottom: 18px;
        }
        .app-search { position: relative; }
        .app-search > i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa4b2;
            z-index: 2;
        }
        .app-search .form-control { min-height: 42px; padding-left: 39px; }

        .app-table { margin: 0; font-size: .78rem; }
        .app-table thead th {
            border-top: 0;
            border-bottom: 1px solid #e8edf3;
            background: #f8fafc;
            color: #697386;
            font-size: .66rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .35px;
            vertical-align: middle;
            white-space: nowrap;
        }
        .app-table tbody td {
            padding-top: 12px;
            padding-bottom: 12px;
            border-color: #f0f3f6;
            vertical-align: middle;
        }
        .app-table tbody tr:hover { background: #fbfffc; }

        .app-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: .66rem;
            font-weight: 750;
            white-space: nowrap;
        }
        .app-badge-success { background: #dcfce7; color: #166534; }
        .app-badge-warning { background: #fef3c7; color: #92400e; }
        .app-badge-info { background: #dbeafe; color: #1e40af; }
        .app-badge-danger { background: #fee2e2; color: #991b1b; }
        .app-badge-muted { background: #f1f5f9; color: #64748b; }

        .app-action-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 8px;
        }

        .app-filter-icon-btn {
            position: relative;
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 10px;
            background: #fff;
            color: #475569;
            border: 1px solid #dfe5ec;
            box-shadow: 0 2px 8px rgba(15,23,42,.04);
        }
        .app-filter-icon-btn:hover,
        .app-filter-icon-btn:focus {
            color: var(--app-primary-dark);
            border-color: #86efac;
            background: #f0fdf4;
        }
        .app-filter-icon-btn .filter-active-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #16a34a;
            border: 2px solid #fff;
        }
        .app-filter-summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            margin-bottom: 16px;
        }

        .app-alert {
            border: 0;
            border-radius: 11px;
            box-shadow: 0 3px 12px rgba(15,23,42,.05);
        }

        .app-pagination-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 18px;
            border-top: 1px solid #edf1f5;
            background: #fff;
        }
        .app-pagination-info { color: #7c8798; font-size: .74rem; }
        .app-pagination-links .pagination { margin: 0; }
        .app-pagination-links .page-link {
            color: #475569;
            border-color: #e2e8f0;
            min-width: 34px;
            text-align: center;
        }
        .app-pagination-links .page-item.active .page-link {
            background: var(--app-primary);
            border-color: var(--app-primary);
        }

        .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
        .modal-header { border-bottom-color: #edf1f5; }
        .modal-footer { border-top-color: #edf1f5; }

        @media (max-width: 768px) {
            #content-wrapper { width: calc(100% - 220px); margin-left: 220px; }
            .app-page-shell { padding-left: 14px; padding-right: 14px; }
            .app-page-header { flex-direction: column; }
            .app-pagination-footer { justify-content: center; text-align: center; flex-wrap: wrap; }
        }
    </style>

    @stack('styles')
</head>
<body id="page-top">
<div id="wrapper">
    @include('layouts.sidebar')

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            @include('layouts.navbar')

            <div class="container-fluid app-page-shell">
                @if(session('success'))
                    <div class="alert alert-success app-alert alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger app-alert alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

@stack('scripts')
</body>
</html>
