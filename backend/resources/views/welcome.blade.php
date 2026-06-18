<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FreshBasket API</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0fdf4;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #0f172a;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 48px;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            max-width: 480px;
            width: 90%;
        }
        .icon { font-size: 56px; margin-bottom: 20px; }
        h1 { font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .sub { color: #64748b; margin-bottom: 28px; font-size: 15px; }
        .pill {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 6px 16px;
            border-radius: 99px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 28px;
        }
        .endpoints { text-align: left; background: #f8fafc; border-radius: 12px; padding: 20px; }
        .endpoints h3 { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; margin-bottom: 12px; }
        .ep { font-family: monospace; font-size: 13px; color: #475569; padding: 4px 0; border-bottom: 1px solid #e2e8f0; }
        .ep:last-child { border: none; }
        .ep span { color: #059669; font-weight: 700; margin-right: 8px; }
        .footer { margin-top: 24px; font-size: 13px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🌿</div>
        <h1>FreshBasket API</h1>
        <p class="sub">Laravel 11 REST API — Backend is running</p>
        <div class="pill">✓ Server Online</div>

        <div class="endpoints">
            <h3>Key Endpoints</h3>
            <div class="ep"><span>GET</span>/api/v1/products</div>
            <div class="ep"><span>GET</span>/api/v1/categories</div>
            <div class="ep"><span>POST</span>/api/v1/auth/login</div>
            <div class="ep"><span>POST</span>/api/v1/checkout/guest</div>
            <div class="ep"><span>GET</span>/api/admin/dashboard/stats</div>
        </div>

        <p class="footer">
            Frontend → <strong>http://localhost:5173</strong>
        </p>
    </div>
</body>
</html>
