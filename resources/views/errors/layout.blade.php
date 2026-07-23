<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Something went wrong') — Trendy Closet</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ asset('images/logo-64.png') }}" sizes="any">

    {{-- Self-contained on purpose: an error page must render even when the asset
         pipeline or database is unhappy, so it carries its own small stylesheet
         rather than depending on the compiled bundle or any view composer. The
         look matches the back office — Inter, a slate palette, a flat white card
         on a soft canvas. --}}
    <style>
        :root {
            --ink: #0f172a;      /* slate-900 */
            --body: #334155;     /* slate-700 */
            --muted: #64748b;    /* slate-500 */
            --faint: #94a3b8;    /* slate-400 */
            --line: #e2e8f0;     /* slate-200 */
            --line-soft: #eef2f6;
            --canvas: #f1f5f9;   /* slate-100 */
            --card: #ffffff;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
            color: var(--body); padding: 1.5rem;
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
            /* A dark gradient: a soft slate glow at the top fading to near-black,
               with a faint dotted texture over it — the white card sits above. */
            background-color: #05070c;
            background-image:
                radial-gradient(1100px 540px at 50% -12%, rgba(71,85,105,.55), transparent 62%),
                radial-gradient(circle, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 100% 100%, 22px 22px;
            background-repeat: no-repeat, repeat;
        }
        a { text-decoration: none; color: inherit; }

        .card {
            width: 100%; max-width: 520px; background: var(--card);
            border: 1px solid rgba(255,255,255,.08); border-radius: 20px;
            box-shadow: 0 40px 90px -20px rgba(0,0,0,.7), 0 0 0 1px rgba(15,23,42,.4);
            padding: clamp(2.25rem, 5vw, 3.25rem); text-align: center;
        }

        .mark {
            display: inline-flex; align-items: center; gap: .6rem; margin-bottom: 2.5rem;
            font-size: 14px; font-weight: 600; letter-spacing: -.01em; color: var(--ink);
        }
        .mark span {
            display: inline-flex; height: 30px; width: 30px; align-items: center; justify-content: center;
            border-radius: 9px; background: #000; color: #fff; font-weight: 700; font-size: 15px;
        }

        .num {
            font-size: clamp(84px, 17vw, 132px); font-weight: 800; line-height: 1;
            letter-spacing: -.05em; color: var(--ink);
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 55%, #cbd5e1 240%);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }

        .badge {
            display: inline-block; margin-top: 1.25rem;
            border: 1px solid var(--line); background: var(--line-soft); border-radius: 999px;
            padding: 5px 14px; font-size: 11.5px; font-weight: 600; letter-spacing: .04em;
            text-transform: uppercase; color: var(--muted);
        }

        h1 {
            margin-top: 1.4rem; font-size: clamp(21px, 3.5vw, 26px); font-weight: 700;
            letter-spacing: -.02em; color: var(--ink); line-height: 1.2;
        }
        p.msg {
            margin: .85rem auto 0; max-width: 42ch; font-size: 14.5px; font-weight: 400;
            line-height: 1.65; color: var(--muted);
        }

        .actions {
            margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--line-soft);
            display: flex; gap: .625rem; justify-content: center; flex-wrap: wrap;
        }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            padding: 12px 22px; border-radius: 10px; font-size: 13.5px; font-weight: 600;
            transition: background .18s, color .18s, border-color .18s; cursor: pointer;
        }
        .btn-dark { background: var(--ink); color: #fff; }
        .btn-dark:hover { background: #1e293b; }
        .btn-outline { border: 1px solid var(--line); color: var(--body); background: #fff; }
        .btn-outline:hover { background: #f8fafc; border-color: var(--faint); }

        .foot { margin-top: 1.75rem; font-size: 12px; font-weight: 400; color: var(--faint); }

        @media (prefers-reduced-motion: no-preference) {
            .card { animation: pop .4s cubic-bezier(.34,1.4,.5,1) both; }
            @keyframes pop { from { opacity: 0; transform: translateY(10px) scale(.98); } }
        }
    </style>
</head>
<body>
    <div class="card">
        <a href="{{ url('/') }}" class="mark"><span>T</span> Trendy Closet</a>

        <div class="num">@yield('code')</div>
        <div class="badge">Error @yield('code')</div>

        <h1>@yield('heading')</h1>
        <p class="msg">@yield('message')</p>

        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-dark">Back to the shop</a>
            @yield('secondary')
        </div>

        <p class="foot">Trendy Closet by Leila Konsol</p>
    </div>
</body>
</html>
