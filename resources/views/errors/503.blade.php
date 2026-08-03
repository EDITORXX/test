<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Security Lock Active</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #05070d;
            --panel: #0b1220;
            --text: #eef7ff;
            --muted: #9fb1c5;
            --border: rgba(45, 212, 191, 0.28);
            --accent: #2dd4bf;
            --accent-soft: rgba(45, 212, 191, 0.12);
            --danger: #fb7185;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background:
                linear-gradient(rgba(45, 212, 191, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(45, 212, 191, 0.035) 1px, transparent 1px),
                radial-gradient(circle at 18% 8%, rgba(45, 212, 191, 0.16), transparent 26rem),
                radial-gradient(circle at 90% 20%, rgba(251, 113, 133, 0.12), transparent 24rem),
                var(--bg);
            background-size: 42px 42px, 42px 42px, auto, auto, auto;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .lock-card {
            width: min(100%, 680px);
            background: linear-gradient(145deg, rgba(11, 18, 32, 0.96), rgba(7, 10, 18, 0.98));
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.45), 0 0 42px rgba(45, 212, 191, 0.12);
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
            border: 1px solid rgba(45, 212, 191, 0.44);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            flex: 0 0 auto;
            box-shadow: 0 0 28px rgba(45, 212, 191, 0.25);
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

        .timer {
            margin-top: 24px;
            padding: 14px 16px;
            border: 1px solid rgba(251, 113, 133, 0.35);
            border-radius: 8px;
            background: rgba(251, 113, 133, 0.08);
            color: #fecdd3;
            font-size: 15px;
            font-weight: 700;
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

        <p class="lead">This system was automatically locked after suspicious activity was detected on the server.</p>
        <p>Due to security concerns, access has been restricted while the owner verifies system integrity.</p>
        <p>If you are an authorized user, please contact the administrator.</p>

        <div class="timer">Automatic security lock: access restricted for 2 hours.</div>
    </main>
</body>
</html>
