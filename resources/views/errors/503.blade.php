<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Security Lock Active</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --panel: #ffffff;
            --text: #102033;
            --muted: #5d6b7a;
            --border: #d9e2ec;
            --accent: #0f766e;
            --accent-soft: #e6fffb;
            --danger: #b42318;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.12), transparent 32rem),
                var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .lock-card {
            width: min(100%, 680px);
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(16, 32, 51, 0.12);
            padding: clamp(24px, 5vw, 44px);
        }

        .status-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
        }

        .status-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--accent-soft);
            border: 1px solid rgba(15, 118, 110, 0.22);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            flex: 0 0 auto;
        }

        h1 {
            margin: 0;
            font-size: clamp(28px, 5vw, 40px);
            line-height: 1.08;
            letter-spacing: 0;
        }

        .lead {
            margin: 0 0 18px;
            font-size: clamp(17px, 2.6vw, 20px);
            line-height: 1.55;
            color: var(--text);
        }

        p {
            margin: 0 0 12px;
            font-size: 16px;
            line-height: 1.65;
            color: var(--muted);
        }

        .reference {
            margin-top: 26px;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #f8fafc;
            color: var(--danger);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            overflow-wrap: anywhere;
        }

        @media (max-width: 520px) {
            body {
                align-items: flex-start;
                padding: 18px;
            }

            .lock-card {
                margin-top: 32px;
            }

            .status-row {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <main class="lock-card" role="main" aria-labelledby="security-lock-title">
        <div class="status-row">
            <div class="status-icon" aria-hidden="true">
                <svg width="25" height="25" viewBox="0 0 24 24" fill="none">
                    <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 14v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <h1 id="security-lock-title">Security Lock Active</h1>
        </div>

        <p class="lead">This system has been temporarily locked because suspicious activity was detected on the server.</p>
        <p>Access is restricted while the owner verifies system integrity.</p>
        <p>If you are an authorized user, please contact the administrator.</p>

        <div class="reference">Reference: SECURITY_LOCK_ACTIVE</div>
    </main>
</body>
</html>
