<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Failed</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@600&display=swap');

            *, *::before, *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #0f1117;
                background-image:
                    radial-gradient(ellipse at 20% 50%, rgba(239, 68, 68, 0.06) 0%, transparent 60%),
                    radial-gradient(ellipse at 80% 20%, rgba(239, 68, 68, 0.04) 0%, transparent 50%);
                font-family: 'DM Sans', sans-serif;
            }

            .card {
                background: #ffffff;
                border-radius: 20px;
                padding: 56px 48px 48px;
                width: 100%;
                max-width: 420px;
                text-align: center;
                box-shadow:
                    0 0 0 1px rgba(255,255,255,0.06),
                    0 32px 64px rgba(0, 0, 0, 0.5),
                    0 8px 16px rgba(0, 0, 0, 0.3);
                animation: rise 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
            }

            @keyframes rise {
                from { opacity: 0; transform: translateY(24px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            .icon-wrap {
                width: 72px;
                height: 72px;
                border-radius: 50%;
                background: #fef2f2;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 28px;
            }

            .icon-wrap svg {
                width: 36px;
                height: 36px;
                stroke: #ef4444;
                stroke-width: 2.5;
                fill: none;
                stroke-linecap: round;
                stroke-linejoin: round;
            }

            .x-line {
                stroke-dasharray: 30;
                stroke-dashoffset: 30;
            }

            .x-line:nth-child(1) { animation: draw 0.3s 0.3s ease forwards; }
            .x-line:nth-child(2) { animation: draw 0.3s 0.5s ease forwards; }

            @keyframes draw {
                to { stroke-dashoffset: 0; }
            }

            h1 {
                font-family: 'Playfair Display', serif;
                font-size: 1.75rem;
                font-weight: 600;
                color: #111827;
                margin-bottom: 12px;
                letter-spacing: -0.02em;
            }

            p {
                font-size: 0.9375rem;
                color: #6b7280;
                line-height: 1.6;
                margin-bottom: 36px;
            }

            .btn {
                display: inline-block;
                width: 100%;
                padding: 14px 24px;
                background: #111827;
                color: #ffffff;
                font-family: 'DM Sans', sans-serif;
                font-size: 0.9375rem;
                font-weight: 500;
                text-decoration: none;
                border-radius: 10px;
                transition: background 0.2s, transform 0.15s;
                letter-spacing: 0.01em;
            }

            .btn:hover {
                background: #1f2937;
                transform: translateY(-1px);
            }

            .btn:active {
                transform: translateY(0);
            }
        </style>
    </head>
    <body>

        <div class="card">
            <div class="icon-wrap">
                <svg viewBox="0 0 24 24">
                    <line class="x-line" x1="5" y1="5" x2="19" y2="19"/>
                    <line class="x-line" x1="19" y1="5" x2="5" y2="19"/>
                </svg>
            </div>

            <h1>Payment Failed</h1>
            <p>We were unable to process your payment.<br>Please try again or contact support.</p>

            <a href="{{ config('app.frontend_url') }}" class="btn">Go Back</a>
        </div>

    </body>
</html>