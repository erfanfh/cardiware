<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    {{-- انتقال جایگزین در صورت اجرا نشدن JavaScript --}}
    <meta
        http-equiv="refresh"
        content="4;url={{ $resultUrl }}"
    >

    <meta name="robots" content="noindex, nofollow">

    <title>بررسی نتیجه پرداخت</title>

    <style>
        @font-face {
            font-family: "IRANSansX";
            src: url("{{ \Illuminate\Support\Facades\Vite::asset('resources/fonts/IRANSansX/IRANSansX-Regular.woff2') }}") format("woff2"),
            url("{{ \Illuminate\Support\Facades\Vite::asset('resources/fonts/IRANSansX/IRANSansX-Regular.woff') }}") format("woff");
            font-style: normal;
            font-weight: 400;
            font-display: swap;
        }

        @font-face {
            font-family: "IRANSansX";
            src: url("{{ \Illuminate\Support\Facades\Vite::asset('resources/fonts/IRANSansX/IRANSansX-Bold.woff2') }}") format("woff2"),
            url("{{ \Illuminate\Support\Facades\Vite::asset('resources/fonts/IRANSansX/IRANSansX-Bold.woff') }}") format("woff");
            font-style: normal;
            font-weight: 700 900;
            font-display: swap;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        :root {
            color-scheme: dark;

            --background: #09090b;
            --card-background: rgba(24, 24, 27, 0.82);
            --card-border: rgba(255, 255, 255, 0.09);

            --text-primary: #fafafa;
            --text-secondary: #a1a1aa;
            --text-muted: #71717a;

            --brand-400: #fb923c;
            --brand-500: #f97316;
            --brand-600: #ea580c;
            --brand-700: #c2410c;
        }

        html {
            min-height: 100%;
            background: var(--background);
        }

        body {
            position: relative;
            min-width: 320px;
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            padding: max(20px, env(safe-area-inset-top)) max(20px, env(safe-area-inset-right)) max(20px, env(safe-area-inset-bottom)) max(20px, env(safe-area-inset-left));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-family: "IRANSansX",
            Tahoma,
            Arial,
            sans-serif;
            font-weight: 400;
            color: var(--text-primary);
            background: radial-gradient(
                circle at 12% 12%,
                rgba(249, 115, 22, 0.19),
                transparent 34%
            ),
            radial-gradient(
                circle at 88% 88%,
                rgba(194, 65, 12, 0.13),
                transparent 38%
            ),
            linear-gradient(
                145deg,
                #09090b 0%,
                #111827 50%,
                #0c0a09 100%
            );
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            z-index: 0;
            width: 350px;
            height: 350px;
            border-radius: 999px;
            filter: blur(90px);
            pointer-events: none;
            opacity: 0.2;
        }

        body::before {
            top: -160px;
            right: -120px;
            background: var(--brand-500);
        }

        body::after {
            bottom: -170px;
            left: -120px;
            background: var(--brand-700);
        }

        .payment-status {
            position: relative;
            z-index: 1;
            width: min(100%, 460px);
            padding: 42px 34px 30px;
            overflow: hidden;
            text-align: center;
            border: 1px solid var(--card-border);
            border-radius: 28px;
            background: var(--card-background);
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.5),
            0 10px 30px rgba(0, 0, 0, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .payment-status::before {
            content: "";
            position: absolute;
            top: 0;
            right: 15%;
            left: 15%;
            height: 1px;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(251, 146, 60, 0.7),
                transparent
            );
        }

        .payment-status__icon {
            position: relative;
            width: 88px;
            height: 88px;
            margin: 0 auto 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(249, 115, 22, 0.16);
            border-radius: 50%;
            background: rgba(249, 115, 22, 0.09);
            box-shadow: 0 0 0 10px rgba(249, 115, 22, 0.035),
            0 20px 50px rgba(249, 115, 22, 0.14);
        }

        .payment-status__spinner {
            position: relative;
            width: 52px;
            height: 52px;
            border: 4px solid rgba(255, 255, 255, 0.09);
            border-top-color: var(--brand-500);
            border-right-color: var(--brand-400);
            border-radius: 50%;
            animation: spinner-rotate 0.9s linear infinite;
        }

        .payment-status__spinner::after {
            content: "";
            position: absolute;
            inset: 13px;
            border-radius: 50%;
            background: var(--brand-500);
            box-shadow: 0 0 22px rgba(249, 115, 22, 0.7);
            animation: spinner-pulse 1.2s ease-in-out infinite;
        }

        .payment-status__title {
            margin: 0;
            font-size: 23px;
            font-weight: 800;
            line-height: 1.7;
            letter-spacing: -0.4px;
            color: var(--text-primary);
        }

        .payment-status__description {
            max-width: 360px;
            margin: 9px auto 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 2;
            color: var(--text-secondary);
        }

        .payment-status__progress {
            position: relative;
            width: 100%;
            height: 5px;
            margin-top: 28px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.07);
        }

        .payment-status__progress-bar {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 42%;
            border-radius: inherit;
            background: linear-gradient(
                90deg,
                var(--brand-700),
                var(--brand-400),
                var(--brand-500)
            );
            box-shadow: 0 0 16px rgba(249, 115, 22, 0.5);
            animation: progress-move 1.35s ease-in-out infinite;
        }

        .payment-status__link {
            width: 100%;
            min-height: 48px;
            margin-top: 25px;
            padding: 11px 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid rgba(249, 115, 22, 0.28);
            border-radius: 14px;
            outline: none;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.8;
            text-decoration: none;
            color: #fed7aa;
            background: rgba(249, 115, 22, 0.08);
            transition: transform 180ms ease,
            color 180ms ease,
            border-color 180ms ease,
            background-color 180ms ease,
            box-shadow 180ms ease;
        }

        .payment-status__link:hover {
            color: #ffffff;
            border-color: rgba(249, 115, 22, 0.55);
            background: rgba(249, 115, 22, 0.16);
            box-shadow: 0 10px 28px rgba(249, 115, 22, 0.1);
            transform: translateY(-2px);
        }

        .payment-status__link:active {
            transform: translateY(0);
        }

        .payment-status__link:focus-visible {
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.17),
            0 10px 28px rgba(249, 115, 22, 0.1);
        }

        .payment-status__link-icon {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
        }

        .payment-status__hint {
            margin: 17px 0 0;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.9;
            color: var(--text-muted);
        }

        @keyframes spinner-rotate {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spinner-pulse {
            0%,
            100% {
                opacity: 0.6;
                transform: scale(0.78);
            }

            50% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes progress-move {
            from {
                right: -45%;
            }

            to {
                right: 105%;
            }
        }

        @media (max-width: 540px) {
            body {
                align-items: center;
            }

            .payment-status {
                padding: 34px 21px 25px;
                border-radius: 23px;
            }

            .payment-status__icon {
                width: 78px;
                height: 78px;
                margin-bottom: 22px;
            }

            .payment-status__spinner {
                width: 46px;
                height: 46px;
            }

            .payment-status__spinner::after {
                inset: 12px;
            }

            .payment-status__title {
                font-size: 20px;
                line-height: 1.8;
            }

            .payment-status__description {
                margin-top: 6px;
                font-size: 13px;
                line-height: 2;
            }

            .payment-status__progress {
                margin-top: 24px;
            }

            .payment-status__link {
                margin-top: 22px;
            }
        }

        @media (max-width: 360px) {
            body {
                padding: 14px;
            }

            .payment-status {
                padding-right: 17px;
                padding-left: 17px;
            }

            .payment-status__title {
                font-size: 18px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .payment-status__spinner,
            .payment-status__spinner::after,
            .payment-status__progress-bar {
                animation-duration: 2.5s;
            }

            .payment-status__link {
                transition: none;
            }
        }
    </style>
</head>

<body>
<main
    class="payment-status"
    role="status"
    aria-live="polite"
    aria-busy="true"
>
    <div
        class="payment-status__icon"
        aria-hidden="true"
    >
        <div class="payment-status__spinner"></div>
    </div>

    <h1 class="payment-status__title">
        در حال بررسی نتیجه پرداخت
    </h1>

    <p class="payment-status__description">
        لطفا چند لحظه صبر کنید تا وضعیت تراکنش شما از درگاه پرداخت دریافت و تایید شود.
    </p>

    <div
        class="payment-status__progress"
        aria-hidden="true"
    >
        <div class="payment-status__progress-bar"></div>
    </div>

    <a
        class="payment-status__link"
        href="{{ $resultUrl }}"
    >
        مشاهده نتیجه پرداخت

        <svg
            class="payment-status__link-icon"
            viewBox="0 0 24 24"
            fill="none"
            aria-hidden="true"
        >
            <path
                d="M15 18L9 12L15 6"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>
    </a>

    <p class="payment-status__hint">
        در صورت منتقل نشدن خودکار، دکمه بالا را انتخاب کنید.
    </p>
</main>

<script>
    window.setTimeout(() => {
        window.location.replace(@json($resultUrl));
    }, 1500);
</script>

<noscript>
    <style>
        .payment-status__progress {
            display: none;
        }
    </style>
</noscript>
</body>
</html>
