<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Member Area') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-light: #eef2ff;
            --primary-glow: rgba(99,102,241,.15);
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
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
            --shadow: 0 4px 24px rgba(0,0,0,.06);
            --shadow-lg: 0 8px 40px rgba(0,0,0,.08);
            --transition: all .2s cubic-bezier(.4,0,.2,1);
        }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--gray-50);color:var(--dark);min-height:100vh;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased}

        /* ─── Navbar ─── */
        .m-nav{background:var(--dark);padding:.75rem 0;position:sticky;top:0;z-index:100;border-bottom:1px solid rgba(255,255,255,.06)}
        .m-nav .wrap{max-width:1100px;margin:0 auto;padding:0 1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem}
        .m-brand{color:#fff;font-weight:700;font-size:1.2rem;text-decoration:none;display:flex;align-items:center;gap:.5rem;transition:var(--transition)}
        .m-brand:hover{color:var(--primary)}
        .m-brand svg{width:26px;height:26px}
        .m-toggle{display:none;background:0;border:1px solid rgba(255,255,255,.15);border-radius:8px;color:#fff;padding:.35rem .6rem;font-size:1.2rem;cursor:pointer;transition:var(--transition)}
        .m-toggle:hover{border-color:rgba(255,255,255,.35);background:rgba(255,255,255,.06)}
        .m-links{display:flex;align-items:center;gap:.25rem;list-style:none}
        .m-links a,.m-links button{color:var(--gray-300);text-decoration:none;padding:.5rem .85rem;border-radius:8px;font-size:.875rem;font-weight:500;transition:var(--transition);display:flex;align-items:center;gap:.4rem;white-space:nowrap;background:0;border:0;cursor:pointer;font-family:inherit}
        .m-links a:hover,.m-links button:hover{color:#fff;background:rgba(255,255,255,.08)}
        .m-links a.active{color:#fff;background:var(--primary)}
        .m-links .sep{width:1px;height:20px;background:rgba(255,255,255,.12);margin:0 .2rem}

        .m-user{color:#fff;background:var(--dark-soft);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:.35rem .7rem .35rem .45rem;display:flex;align-items:center;gap:.5rem;font-size:.875rem;font-weight:500;cursor:pointer;position:relative;transition:var(--transition)}
        .m-user:hover{border-color:var(--primary);background:rgba(99,102,241,.1)}
        .m-avatar{width:28px;height:28px;background:linear-gradient(135deg,var(--primary),#a78bfa);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.75rem}
        .m-dd{position:absolute;top:calc(100% + 6px);right:0;background:#fff;border-radius:var(--radius-sm);box-shadow:var(--shadow-lg);border:1px solid var(--gray-200);min-width:200px;padding:.5rem;display:none;z-index:99}
        .m-dd.show{display:block;animation:fadeD .15s ease}
        .m-dd a,.m-dd button{color:var(--gray-600);padding:.5rem .75rem;border-radius:6px;font-size:.85rem;display:flex;align-items:center;gap:.5rem;width:100%;text-align:left}
        .m-dd a:hover,.m-dd button:hover{background:var(--gray-100);color:var(--dark)}
        .m-dd .dd-label{padding:.35rem .75rem;font-size:.7rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.05em}
        .m-dd .dd-line{height:1px;background:var(--gray-200);margin:.35rem .5rem}
        .m-dd .dd-red{color:var(--danger)}
        .m-dd .dd-red:hover{background:#fef2f2;color:var(--danger)}
        @keyframes fadeD{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}

        @media(max-width:768px){
            .m-toggle{display:block}
            .m-links{display:none;flex-direction:column;width:100%;padding-top:.75rem;align-items:stretch}
            .m-links.open{display:flex}
            .m-links .sep{display:none}
            .m-user{justify-content:flex-start}
            .m-dd{position:static;box-shadow:none;border:1px solid var(--gray-200);margin-top:.35rem}
        }

        /* ─── Auth Card ─── */
        .auth-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
        .auth-card{width:100%;max-width:440px;background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--gray-200);overflow:hidden}
        .auth-head{padding:2rem 2rem 1.75rem;text-align:center;color:#fff}
        .auth-head.gradient{background:linear-gradient(135deg,var(--primary),#818cf8)}
        .auth-head.dark{background:linear-gradient(135deg,var(--dark),var(--dark-soft))}
        .auth-head h2{margin:0;font-size:1.5rem;font-weight:700}
        .auth-head p{margin:.5rem 0 0;opacity:.85;font-size:.9rem}
        .auth-icon{width:56px;height:56px;background:rgba(255,255,255,.2);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:.75rem;font-size:1.5rem;backdrop-filter:blur(4px)}
        .auth-body{padding:2rem}
        .auth-foot{text-align:center;padding:1.25rem 2rem;background:var(--gray-50);border-top:1px solid var(--gray-200);font-size:.9rem;color:var(--gray-500)}
        .auth-foot a{color:var(--primary);font-weight:600;text-decoration:none;transition:var(--transition)}
        .auth-foot a:hover{color:var(--primary-hover);text-decoration:underline}

        /* ─── Form ─── */
        .fg{margin-bottom:1.25rem}
        .fg label{display:block;font-size:.78rem;font-weight:600;color:var(--gray-600);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.45rem}
        .fi-wrap{position:relative;display:flex;align-items:center}
        .fi-icon{position:absolute;left:14px;color:var(--gray-400);font-size:1.1rem;pointer-events:none;transition:var(--transition);z-index:2}
        .fi{width:100%;padding:.75rem .875rem .75rem 2.75rem;font-size:.95rem;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);background:var(--gray-50);color:var(--dark);outline:0;transition:var(--transition);font-family:inherit}
        .fi::placeholder{color:var(--gray-400)}
        .fi:focus{border-color:var(--primary);background:#fff;box-shadow:0 0 0 3px var(--primary-glow)}
        .fi:focus~.fi-icon{color:var(--primary)}
        .fi.is-invalid{border-color:var(--danger)}
        .fi.is-invalid:focus{box-shadow:0 0 0 3px rgba(239,68,68,.12)}
        .fi-err{font-size:.8rem;color:var(--danger);margin-top:.35rem;display:flex;align-items:center;gap:.3rem}
        .pw-btn{position:absolute;right:12px;background:0;border:0;color:var(--gray-400);cursor:pointer;padding:4px;font-size:1.1rem;transition:var(--transition);z-index:2}
        .pw-btn:hover{color:var(--primary)}

        /* Password strength */
        .pw-str{margin-top:.5rem}
        .pw-bar{height:4px;border-radius:2px;background:var(--gray-200);overflow:hidden}
        .pw-fill{height:100%;width:0;border-radius:2px;transition:width .3s,background .3s}
        .pw-txt{font-size:.75rem;margin-top:.25rem;color:var(--gray-400)}

        /* Remember me */
        .remember-check{display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;font-size:.875rem;color:var(--gray-600)}
        .remember-check input[type=checkbox]{width:16px;height:16px;accent-color:var(--primary);cursor:pointer}

        /* ─── Buttons ─── */
        .btn-primary{width:100%;padding:.85rem;font-size:1rem;font-weight:600;color:#fff;background:linear-gradient(135deg,var(--primary),var(--primary-hover));border:0;border-radius:var(--radius-sm);cursor:pointer;transition:var(--transition);display:flex;align-items:center;justify-content:center;gap:.5rem;font-family:inherit}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(99,102,241,.35)}
        .btn-primary:active{transform:translateY(0)}
        .btn-dark{width:100%;padding:.85rem;font-size:1rem;font-weight:600;color:#fff;background:var(--dark);border:0;border-radius:var(--radius-sm);cursor:pointer;transition:var(--transition);display:flex;align-items:center;justify-content:center;gap:.5rem;font-family:inherit}
        .btn-dark:hover{background:var(--dark-soft);transform:translateY(-1px);box-shadow:0 6px 20px rgba(15,23,42,.25)}
        .btn-ol{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.25rem;font-size:.9rem;font-weight:500;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);background:#fff;color:var(--gray-600);cursor:pointer;transition:var(--transition);text-decoration:none;font-family:inherit}
        .btn-ol:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light)}
        .btn-ol-red{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.25rem;font-size:.9rem;font-weight:500;border:1.5px solid #fecaca;border-radius:var(--radius-sm);background:#fff;color:var(--danger);cursor:pointer;transition:var(--transition);text-decoration:none;font-family:inherit}
        .btn-ol-red:hover{background:#fef2f2;border-color:var(--danger)}

        /* ─── Alerts ─── */
        .alert{padding:.875rem 1rem;border-radius:var(--radius-sm);font-size:.875rem;margin-bottom:1.25rem;display:flex;gap:.6rem;align-items:flex-start;line-height:1.5;animation:fadeD .2s ease}
        .alert-danger{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
        .alert-success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}
        .alert-warning{background:#fffbeb;color:#92400e;border:1px solid #fde68a}
        .alert-icon{font-size:1.1rem;flex-shrink:0;margin-top:1px}
        .alert-x{margin-left:auto;background:0;border:0;font-size:1.2rem;cursor:pointer;opacity:.5;line-height:1;padding:0;color:inherit}
        .alert-x:hover{opacity:1}

        /* ─── Dashboard ─── */
        .dash{flex:1;padding:2rem 1rem}
        .dash .wrap{max-width:960px;margin:0 auto}
        .dash-grid{display:grid;grid-template-columns:1fr 340px;gap:1.5rem}
        .d-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);overflow:hidden}
        .d-card-head{padding:1rem 1.25rem;font-weight:600;font-size:.9rem;color:var(--gray-600);border-bottom:1px solid var(--gray-200);background:var(--gray-50);display:flex;align-items:center;gap:.5rem}
        .d-card-body{padding:1.25rem}
        .welcome{background:linear-gradient(135deg,var(--primary),#818cf8);border-radius:var(--radius);padding:2rem;color:#fff;margin-bottom:1.5rem;display:flex;align-items:center;gap:1.25rem}
        .welcome-av{width:64px;height:64px;background:rgba(255,255,255,.2);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:700;flex-shrink:0;backdrop-filter:blur(4px)}
        .welcome h2{margin:0;font-size:1.4rem;font-weight:700}
        .welcome p{margin:.25rem 0 0;opacity:.85;font-size:.95rem}
        .info-row{display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--gray-100);font-size:.9rem}
        .info-row:last-child{border:none}
        .info-lbl{color:var(--gray-500);display:flex;align-items:center;gap:.4rem}
        .info-val{font-weight:600;color:var(--dark)}
        .badge-role{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:6px;font-size:.78rem;font-weight:600;background:var(--primary-light);color:var(--primary)}
        .action-list{display:flex;flex-direction:column;gap:.5rem}

        @media(max-width:768px){
            .dash-grid{grid-template-columns:1fr}
            .welcome{flex-direction:column;text-align:center}
        }

        /* ─── Footer ─── */
        .m-foot{padding:1.25rem 0;text-align:center;font-size:.8rem;color:var(--gray-400);border-top:1px solid var(--gray-200);margin-top:auto}
    </style>
</head>
<body>
    @include('layouts.navbar')

    @yield('content')

    <footer class="m-foot">
        &copy; {{ date('Y') }} {{ config('app.name') }}
    </footer>

    <script>
    // Close dropdown
    document.addEventListener('click',e=>{const d=document.getElementById('navDd');if(d&&!e.target.closest('.m-user'))d.classList.remove('show')});
    // Alert dismiss
    document.querySelectorAll('.alert-x').forEach(b=>b.addEventListener('click',()=>b.closest('.alert').style.display='none'));
    // Password toggle
    document.querySelectorAll('.pw-btn').forEach(b=>b.addEventListener('click',function(){const i=this.parentElement.querySelector('input'),ic=this.querySelector('i');if(i.type==='password'){i.type='text';ic.classList.replace('bi-eye','bi-eye-slash')}else{i.type='password';ic.classList.replace('bi-eye-slash','bi-eye')}}));
    // Password strength
    (function(){const p=document.getElementById('password');const bar=document.querySelector('.pw-fill');const txt=document.querySelector('.pw-txt');if(!p||!bar||!txt)return;p.addEventListener('input',function(){const v=this.value;let s=0;if(v.length>=8)s++;if(v.length>=12)s++;if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;const pct=[0,20,40,60,80,100][s];const c=['var(--gray-300)','var(--danger)','#f97316','var(--warning)','#84cc16','var(--success)'][s];const l=['','Sangat Lemah','Lemah','Cukup','Kuat','Sangat Kuat'][s];bar.style.width=pct+'%';bar.style.background=c;txt.textContent=v.length?l[s]:'';txt.style.color=c})})();
    </script>
</body>
</html>
