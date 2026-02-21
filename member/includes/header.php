<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle ?? 'Member Area'); ?> - Cloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-light: #eef2ff;
            --primary-glow: rgba(99, 102, 241, 0.15);
            --dark: #0f172a;
            --dark-soft: #1e293b;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --danger: #ef4444;
            --success: #22c55e;
            --warning: #f59e0b;
            --radius: 16px;
            --radius-sm: 10px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
            --shadow: 0 4px 24px rgba(0,0,0,.06);
            --shadow-lg: 0 8px 40px rgba(0,0,0,.08);
            --transition: all .2s cubic-bezier(.4,0,.2,1);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--gray-50);
            color: var(--dark);
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Navbar ─── */
        .m-navbar {
            background: var(--dark);
            padding: .75rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .m-navbar .container { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem; }
        .m-navbar-brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.25rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .5rem;
            transition: var(--transition);
        }
        .m-navbar-brand:hover { color: var(--primary); }
        .m-navbar-brand svg { width: 28px; height: 28px; }
        .m-navbar-toggle {
            display: none;
            background: none;
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 8px;
            color: #fff;
            padding: .4rem .65rem;
            font-size: 1.25rem;
            cursor: pointer;
            transition: var(--transition);
        }
        .m-navbar-toggle:hover { border-color: rgba(255,255,255,.35); background: rgba(255,255,255,.06); }
        .m-nav { display: flex; align-items: center; gap: .25rem; list-style: none; margin: 0; padding: 0; }
        .m-nav a {
            color: var(--gray-300);
            text-decoration: none;
            padding: .5rem .875rem;
            border-radius: 8px;
            font-size: .875rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: .4rem;
            white-space: nowrap;
        }
        .m-nav a:hover { color: #fff; background: rgba(255,255,255,.08); }
        .m-nav a.active { color: #fff; background: var(--primary); }
        .m-nav .nav-divider { width: 1px; height: 20px; background: rgba(255,255,255,.12); margin: 0 .25rem; }
        .m-nav .nav-user {
            color: #fff;
            background: var(--dark-soft);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 10px;
            padding: .4rem .75rem .4rem .5rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .875rem;
            font-weight: 500;
            cursor: pointer;
            position: relative;
            transition: var(--transition);
        }
        .m-nav .nav-user:hover { border-color: var(--primary); background: rgba(99,102,241,.1); }
        .nav-avatar {
            width: 30px; height: 30px;
            background: linear-gradient(135deg, var(--primary), #a78bfa);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: .8rem;
        }
        .nav-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            background: #fff;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            min-width: 200px;
            padding: .5rem;
            display: none;
            z-index: 999;
        }
        .nav-dropdown.show { display: block; animation: fadeDown .15s ease; }
        .nav-dropdown a {
            color: var(--gray-600);
            padding: .5rem .75rem;
            border-radius: 6px;
            font-size: .85rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .nav-dropdown a:hover { background: var(--gray-100); color: var(--dark); }
        .nav-dropdown .dd-label { padding: .35rem .75rem; font-size: .75rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: .05em; }
        .nav-dropdown .dd-divider { height: 1px; background: var(--gray-200); margin: .35rem .5rem; }
        .nav-dropdown .dd-danger { color: var(--danger); }
        .nav-dropdown .dd-danger:hover { background: #fef2f2; color: var(--danger); }

        @keyframes fadeDown { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 768px) {
            .m-navbar-toggle { display: block; }
            .m-nav { display: none; flex-direction: column; width: 100%; padding-top: .75rem; align-items: stretch; }
            .m-nav.open { display: flex; }
            .m-nav a { padding: .65rem .875rem; }
            .m-nav .nav-divider { display: none; }
            .m-nav .nav-user { justify-content: flex-start; }
            .nav-dropdown { position: static; box-shadow: none; border: 1px solid var(--gray-200); margin-top: .35rem; }
        }

        /* ─── Form Card ─── */
        .auth-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        .auth-card-header {
            background: linear-gradient(135deg, var(--primary), #818cf8);
            padding: 2rem 2rem 1.75rem;
            text-align: center;
            color: #fff;
        }
        .auth-card-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .auth-card-header p {
            margin: .5rem 0 0;
            opacity: .85;
            font-size: .9rem;
        }
        .auth-icon {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,.2);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .75rem;
            font-size: 1.5rem;
            backdrop-filter: blur(4px);
        }
        .auth-card-body {
            padding: 2rem;
        }

        /* ─── Form Elements ─── */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .45rem;
        }
        .form-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .form-input-icon {
            position: absolute;
            left: 14px;
            color: var(--gray-400);
            font-size: 1.1rem;
            pointer-events: none;
            transition: var(--transition);
            z-index: 2;
        }
        .form-input {
            width: 100%;
            padding: .75rem .875rem .75rem 2.75rem;
            font-size: .95rem;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-sm);
            background: var(--gray-50);
            color: var(--dark);
            outline: none;
            transition: var(--transition);
            font-family: inherit;
        }
        .form-input::placeholder { color: var(--gray-400); }
        .form-input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px var(--primary-glow);
        }
        .form-input:focus ~ .form-input-icon,
        .form-input:focus + .form-input-icon { color: var(--primary); }
        .form-input.is-invalid {
            border-color: var(--danger);
            background: #fff;
        }
        .form-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.12); }
        .input-feedback {
            font-size: .8rem;
            color: var(--danger);
            margin-top: .35rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }
        .pw-toggle {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 4px;
            font-size: 1.1rem;
            transition: var(--transition);
            z-index: 2;
        }
        .pw-toggle:hover { color: var(--primary); }

        /* ─── Password Strength ─── */
        .pw-strength { margin-top: .5rem; }
        .pw-bar { height: 4px; border-radius: 2px; background: var(--gray-200); overflow: hidden; }
        .pw-bar-fill { height: 100%; width: 0; border-radius: 2px; transition: width .3s, background .3s; }
        .pw-text { font-size: .75rem; margin-top: .25rem; color: var(--gray-400); }

        /* ─── Button ─── */
        .btn-primary-custom {
            width: 100%;
            padding: .85rem;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            position: relative;
            overflow: hidden;
            font-family: inherit;
        }
        .btn-primary-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, .35);
        }
        .btn-primary-custom:active { transform: translateY(0); }
        .btn-dark-custom {
            width: 100%;
            padding: .85rem;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            background: var(--dark);
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            font-family: inherit;
        }
        .btn-dark-custom:hover {
            background: var(--dark-soft);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(15, 23, 42, .25);
        }
        .btn-outline-custom {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .6rem 1.25rem;
            font-size: .9rem;
            font-weight: 500;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-sm);
            background: #fff;
            color: var(--gray-600);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-family: inherit;
        }
        .btn-outline-custom:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
        .btn-outline-danger {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .6rem 1.25rem;
            font-size: .9rem;
            font-weight: 500;
            border: 1.5px solid #fecaca;
            border-radius: var(--radius-sm);
            background: #fff;
            color: var(--danger);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-family: inherit;
        }
        .btn-outline-danger:hover { background: #fef2f2; border-color: var(--danger); }

        /* ─── Alerts ─── */
        .alert-box {
            padding: .875rem 1rem;
            border-radius: var(--radius-sm);
            font-size: .875rem;
            margin-bottom: 1.25rem;
            display: flex;
            gap: .6rem;
            align-items: flex-start;
            line-height: 1.5;
            animation: fadeDown .2s ease;
        }
        .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .alert-icon { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }
        .alert-close {
            margin-left: auto;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: .5;
            line-height: 1;
            padding: 0;
            color: inherit;
        }
        .alert-close:hover { opacity: 1; }

        /* ─── Auth Footer ─── */
        .auth-footer {
            text-align: center;
            padding: 1.25rem 2rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
            font-size: .9rem;
            color: var(--gray-500);
        }
        .auth-footer a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }
        .auth-footer a:hover { color: var(--primary-hover); text-decoration: underline; }

        /* ─── Dashboard ─── */
        .dash-wrapper { flex: 1; padding: 2rem 1rem; }
        .dash-grid { display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; max-width: 960px; margin: 0 auto; }
        .dash-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        .dash-card-header {
            padding: 1rem 1.25rem;
            font-weight: 600;
            font-size: .9rem;
            color: var(--gray-600);
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .dash-card-body { padding: 1.25rem; }
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), #818cf8);
            border-radius: var(--radius);
            padding: 2rem;
            color: #fff;
            margin-bottom: 1.5rem;
            max-width: 960px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        .welcome-avatar {
            width: 64px; height: 64px;
            background: rgba(255,255,255,.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 700;
            flex-shrink: 0;
            backdrop-filter: blur(4px);
        }
        .welcome-banner h2 { margin: 0; font-size: 1.4rem; font-weight: 700; }
        .welcome-banner p { margin: .25rem 0 0; opacity: .85; font-size: .95rem; }
        .info-row { display: flex; justify-content: space-between; padding: .6rem 0; border-bottom: 1px solid var(--gray-100); font-size: .9rem; }
        .info-row:last-child { border: none; }
        .info-label { color: var(--gray-500); display: flex; align-items: center; gap: .4rem; }
        .info-value { font-weight: 600; color: var(--dark); }
        .badge-role {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .25rem .65rem;
            border-radius: 6px;
            font-size: .78rem;
            font-weight: 600;
            background: var(--primary-light);
            color: var(--primary);
        }
        .action-list { display: flex; flex-direction: column; gap: .5rem; }

        @media (max-width: 768px) {
            .dash-grid { grid-template-columns: 1fr; }
            .welcome-banner { flex-direction: column; text-align: center; }
        }

        /* ─── Footer ─── */
        .m-footer {
            padding: 1.25rem 0;
            text-align: center;
            font-size: .8rem;
            color: var(--gray-400);
            border-top: 1px solid var(--gray-200);
            margin-top: auto;
        }

        /* ─── Misc ─── */
        .container { max-width: 1140px; margin: 0 auto; padding: 0 1rem; }
    </style>
</head>
<body>
