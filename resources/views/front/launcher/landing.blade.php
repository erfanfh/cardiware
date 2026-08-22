<!doctype html>
<html lang="fa" dir="rtl" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>لانچر اختصاصی مودینگ‌وی</title>
    <meta name="description" content="تجربه‌ای جدید و آسان از نصب محصولات. لانچر اختصاصی مودینگ‌وی به زودی...">

    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: "main-font", Tahoma, sans-serif;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes softGlow {
            0%, 100% {
                opacity: .55;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.06);
            }
        }

        @keyframes ringPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(249, 115, 22, .55);
            }
            100% {
                box-shadow: 0 0 0 22px rgba(249, 115, 22, 0);
            }
        }

        .animate-fade-up {
            animation: fadeUp .7s ease both;
        }

        .delay-100 {
            animation-delay: .12s;
        }

        .delay-200 {
            animation-delay: .24s;
        }

        .delay-300 {
            animation-delay: .36s;
        }

        .animate-soft-glow {
            animation: softGlow 3.4s ease-in-out infinite;
        }

        .cta-ring::before,
        .cta-ring::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            pointer-events: none;
            animation: ringPulse 2.4s ease-out infinite;
        }

        .cta-ring::after {
            animation-delay: 1.2s;
        }

        @keyframes heartbeat {
            0%, 100% {
                transform: scale(1);
            }
            14% {
                transform: scale(1.13);
            }
            28% {
                transform: scale(1);
            }
            42% {
                transform: scale(1.13);
            }
            70% {
                transform: scale(1);
            }
        }

        .animate-heartbeat {
            animation: heartbeat 1.6s ease-in-out infinite;
            transform-origin: center;
        }

        @keyframes textShine {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -100% 0;
            }
        }

        .text-shine {
            background-image: linear-gradient(100deg,
            #ffffff 0%, #ffffff 38%,
            #fed7aa 46%, #f97316 50%, #fed7aa 54%,
            #ffffff 62%, #ffffff 100%);
            background-size: 250% 100%;
            background-position: 200% 0;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            animation: textShine 3.2s linear infinite;
        }

        .feature-card {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, .18);
            background: transparent;
            padding: 1rem .6rem;
            color: #ffffff;
        }

        .feature-card-icon {
            width: 22px;
            height: 22px;
            color: #ffffff;
            flex-shrink: 0;
        }

        .feature-card-label {
            font-size: 11px;
            font-weight: 700;
            line-height: 1.5;
            color: #ffffff;
        }

        @media (min-width: 640px) {
            .feature-card-icon {
                width: 26px;
                height: 26px;
            }

            .feature-card-label {
                font-size: 12.5px;
            }
        }
    </style>
</head>

<body class="min-h-screen w-full overflow-x-hidden bg-slate-950 text-slate-100">

<div class="relative min-h-screen w-full">

    <!-- Fallback ambient background, visible until/unless the WebGL animation loads -->
    <div class="pointer-events-none absolute inset-0 z-0 bg-slate-950">
        <div class="animate-soft-glow absolute -top-24 -right-24 h-80 w-80 rounded-full bg-brand-500/20 blur-3xl"></div>
        <div class="animate-soft-glow absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-indigo-500/20 blur-3xl"
             style="animation-delay: 1.4s"></div>
    </div>

    <!-- Interactive tubes cursor animation -->
    <canvas id="tubes-canvas" class="fixed inset-0 z-0 h-full w-full"></canvas>

    <!-- Hero content -->
    <main
        class="relative z-10 flex min-h-screen w-full flex-col items-center justify-center gap-4 px-4 py-10 text-center sm:gap-5 sm:px-6">

        <div class="animate-fade-up mb-1">
            <img
                src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAdEAAAHRCAYAAAA1w4ObAAAACXBIWXMAAC4jAAAuIwF4pT92AAAOk2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgMTAuMC1jMDAwIDI1LkcuZWY3MmU0ZSwgMjAyNS8wNi8yNy0xODo1NDowNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9ImFkb2JlOmRvY2lkOnBob3Rvc2hvcDpiYmQ3ZGQ5Ny1jNGE1LTZiNDMtYTBmZS00ZjhkYjNlZWIyZTAiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MWQ2ZmZiZmYtZDNiZS0zNzQ5LWJhMGUtZDZmYjZiNGQ5NDI4IiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9IkUwMzU4QTBFNjJCRDIyMzU5MTRCMDY1MDc4NTgzQzhDIiBkYzpmb3JtYXQ9ImltYWdlL3BuZyIgcGhvdG9zaG9wOkNvbG9yTW9kZT0iMyIgeG1wOkNyZWF0ZURhdGU9IjIwMTgtMDgtMjFUMDg6NDk6NDIrMDQ6MzAiIHhtcDpNb2RpZnlEYXRlPSIyMDI2LTA2LTExVDEzOjA5OjM0KzAzOjMwIiB4bXA6TWV0YWRhdGFEYXRlPSIyMDI2LTA2LTExVDEzOjA5OjM0KzAzOjMwIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE1LjUgKFdpbmRvd3MpIiB0aWZmOkltYWdlV2lkdGg9IjQ1MCIgdGlmZjpJbWFnZUxlbmd0aD0iNDcwIiB0aWZmOlBob3RvbWV0cmljSW50ZXJwcmV0YXRpb249IjIiIHRpZmY6T3JpZW50YXRpb249IjEiIHRpZmY6U2FtcGxlc1BlclBpeGVsPSIzIiB0aWZmOlhSZXNvbHV0aW9uPSIzMDAwMDAwLzEwMDAwIiB0aWZmOllSZXNvbHV0aW9uPSIzMDAwMDAwLzEwMDAwIiB0aWZmOlJlc29sdXRpb25Vbml0PSIyIiBleGlmOkV4aWZWZXJzaW9uPSIwMjIxIiBleGlmOkNvbG9yU3BhY2U9IjY1NTM1IiBleGlmOlBpeGVsWERpbWVuc2lvbj0iODQ4IiBleGlmOlBpeGVsWURpbWVuc2lvbj0iODQ4Ij4gPHhtcE1NOkhpc3Rvcnk+IDxyZGY6U2VxPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0ic2F2ZWQiIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6NjUyZjlkYzUtNzkzNS1jZDQxLWJlODYtZDdlOGJhYzQzMTY4IiBzdEV2dDp3aGVuPSIyMDE4LTA4LTIxVDA5OjA4OjMzKzA0OjMwIiBzdEV2dDpzb2Z0d2FyZUFnZW50PSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxNS41IChXaW5kb3dzKSIgc3RFdnQ6Y2hhbmdlZD0iLyIvPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0iY29udmVydGVkIiBzdEV2dDpwYXJhbWV0ZXJzPSJmcm9tIGltYWdlL2pwZWcgdG8gYXBwbGljYXRpb24vdm5kLmFkb2JlLnBob3Rvc2hvcCIvPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0iZGVyaXZlZCIgc3RFdnQ6cGFyYW1ldGVycz0iY29udmVydGVkIGZyb20gaW1hZ2UvanBlZyB0byBhcHBsaWNhdGlvbi92bmQuYWRvYmUucGhvdG9zaG9wIi8+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJzYXZlZCIgc3RFdnQ6aW5zdGFuY2VJRD0ieG1wLmlpZDozMmZlMTQ0YS1mMGNhLTQyNDUtYjNlMy1iY2VhNTg5YzNmMGMiIHN0RXZ0OndoZW49IjIwMTgtMDgtMjFUMDk6MDg6MzMrMDQ6MzAiIHN0RXZ0OnNvZnR3YXJlQWdlbnQ9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE1LjUgKFdpbmRvd3MpIiBzdEV2dDpjaGFuZ2VkPSIvIi8+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJzYXZlZCIgc3RFdnQ6aW5zdGFuY2VJRD0ieG1wLmlpZDo3OTZhM2YyMS1jNDFmLWUyNGYtOGZjMy01NDVmNGIxNWZhYzUiIHN0RXZ0OndoZW49IjIwMTgtMDgtMjFUMTk6MDM6NDArMDQ6MzAiIHN0RXZ0OnNvZnR3YXJlQWdlbnQ9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE1LjUgKFdpbmRvd3MpIiBzdEV2dDpjaGFuZ2VkPSIvIi8+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJjb252ZXJ0ZWQiIHN0RXZ0OnBhcmFtZXRlcnM9ImZyb20gYXBwbGljYXRpb24vdm5kLmFkb2JlLnBob3Rvc2hvcCB0byBpbWFnZS9wbmciLz4gPHJkZjpsaSBzdEV2dDphY3Rpb249ImRlcml2ZWQiIHN0RXZ0OnBhcmFtZXRlcnM9ImNvbnZlcnRlZCBmcm9tIGFwcGxpY2F0aW9uL3ZuZC5hZG9iZS5waG90b3Nob3AgdG8gaW1hZ2UvcG5nIi8+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJzYXZlZCIgc3RFdnQ6aW5zdGFuY2VJRD0ieG1wLmlpZDpiNzRmZWZhMi0zMGJjLTJmNGItYjU0Ni0xN2JjZGNjYjIxNWIiIHN0RXZ0OndoZW49IjIwMTgtMDgtMjFUMTk6MDM6NDArMDQ6MzAiIHN0RXZ0OnNvZnR3YXJlQWdlbnQ9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE1LjUgKFdpbmRvd3MpIiBzdEV2dDpjaGFuZ2VkPSIvIi8+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJzYXZlZCIgc3RFdnQ6aW5zdGFuY2VJRD0ieG1wLmlpZDoxZDZmZmJmZi1kM2JlLTM3NDktYmEwZS1kNmZiNmI0ZDk0MjgiIHN0RXZ0OndoZW49IjIwMjYtMDYtMTFUMTM6MDk6MzQrMDM6MzAiIHN0RXZ0OnNvZnR3YXJlQWdlbnQ9IkFkb2JlIFBob3Rvc2hvcCAyNy4zIChXaW5kb3dzKSIgc3RFdnQ6Y2hhbmdlZD0iLyIvPiA8L3JkZjpTZXE+IDwveG1wTU06SGlzdG9yeT4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6Nzk2YTNmMjEtYzQxZi1lMjRmLThmYzMtNTQ1ZjRiMTVmYWM1IiBzdFJlZjpkb2N1bWVudElEPSJhZG9iZTpkb2NpZDpwaG90b3Nob3A6MGZiM2I2YmMtYTRmYy0xMWU4LThjMGItOTdjMjY4ZjE2MWM1IiBzdFJlZjpvcmlnaW5hbERvY3VtZW50SUQ9IkUwMzU4QTBFNjJCRDIyMzU5MTRCMDY1MDc4NTgzQzhDIi8+IDxwaG90b3Nob3A6RG9jdW1lbnRBbmNlc3RvcnM+IDxyZGY6QmFnPiA8cmRmOmxpPmFkb2JlOmRvY2lkOnBob3Rvc2hvcDphZmE4YzAyNS1hNGUxLTExZTgtOGMwYi05N2MyNjhmMTYxYzU8L3JkZjpsaT4gPHJkZjpsaT54bXAuZGlkOjc3YzVhODJiLWMzZjktNDQ0Yi1iN2NjLTA0ZjMzZGM5NGZkMzwvcmRmOmxpPiA8L3JkZjpCYWc+IDwvcGhvdG9zaG9wOkRvY3VtZW50QW5jZXN0b3JzPiA8cGhvdG9zaG9wOlRleHRMYXllcnM+IDxyZGY6QmFnPiA8cmRmOmxpIHBob3Rvc2hvcDpMYXllck5hbWU9IkxBVU5DSEVSIiBwaG90b3Nob3A6TGF5ZXJUZXh0PSJMQVVOQ0hFUiIvPiA8L3JkZjpCYWc+IDwvcGhvdG9zaG9wOlRleHRMYXllcnM+IDx0aWZmOkJpdHNQZXJTYW1wbGU+IDxyZGY6U2VxPiA8cmRmOmxpPjg8L3JkZjpsaT4gPHJkZjpsaT44PC9yZGY6bGk+IDxyZGY6bGk+ODwvcmRmOmxpPiA8L3JkZjpTZXE+IDwvdGlmZjpCaXRzUGVyU2FtcGxlPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PuBlT24AAE/6SURBVHic7d0HuO1E1Tfw/4SqCGILllFR7GKlg/SOiIACKop0IQICI4qgCCqiwNDUWAApIkoREAHpRbGDvSDWF8ZCVAQFK2S+Z13n8l1uPXuSvSfZ+f+e97xe5WT24u5z9kqmrKW89yAiIqLRZRHXEBEREZMoERFRPCZRIiKiSEyiREREkZhEiYiIIjGJEhERRWISJSIiisQkSkREFIlJlIiIKBKTKBERUSQmUSIiokhMokRERJGYRImIiCIxiRIREUViEiUiIorEJEpERBSJSZSIiCgSkygREVEkJlEiIqJITKJERESRmESJiIgiMYkSERFFYhIlIiKKxCRKREQUiUmUiIgoEpMoERFRJCZRIiKiSEyiREREkZhEiYiIIjGJEhERRWISJSIiirR47IVEbfJWPxLAsgAeBWC58J85gMeHr2UA1ACWArBY+JI/i0cAWALAHwHcCuA2AL9Txsl/p0SqIntC+IyR920USq7Jy7oaU2hErVHe+/ZGIxqBt/olANYB8GIAKwN4DIDlATwWwNINhv4PgF8B+BmAXwD4MYDzlXHyv9OYVEUmNz2rAXgRgBcCeEm4uXkwYoasDjdDPwLwUwC35GX9mzGFThSNSZQmylv9OABvALAXgOcCWHJCLy0fyCcBOIPJtF1Vkb0CwAEANgIg7+84/F1mF8JMwzcBfDUv6x+M6bWIZoxJlCbCW/0MAAWAnQE8KWEo8mR6CoDTlXH/TBhH71VFtiWAgwBsmiiECwB8PC/rGxK9PhGTKI2Xt3otAG8B8PoJPnXO9Mm0DE+m96UOpk+qInsdgL0BbIhuuBrAaXlZS1IlmigmURoLb/UaYYpPpm677NchmX5KGSdThjQfVZHJZp83AXgrgNXRTZcBODwv6x+mDoSGg0mUWuWtXgXAwT1InnO7A4BRxl2YOpCuqYpMZhM+CuDl6D7ZkPRBAB/Iy/rfqYOh6cckSq3wVq8pSQjAa9FvZwN4uzLuTxi4qshk+v1IAO9C/8h0/XvysuZNEY0Vkyg15q2WzSU2nO+bBjLF+84hP5VWRbYBgOMByMxCn50P4L15WUtSJWodkyg14q0+Oax9TqMzAByijPsLBqIqMjmjeVyYkp8Wsgv74LysP5E6EJo+TKIUxVstFYU+D+CVmG6/D0+l52DKVUW2EoDPhYIJ0+hU2RiVl/V/UwdC04NJlEbmrX4egHOmYKpvFHJ84mBlnMMUqopsYwBnAXgKptu3AeyXl/V3UgdC04FJlEbirZYyfV8cY2WaLvstgA2Ucf+HKVIVmQnrn0MhH3r75GX9qdSBUP8xidKMeaul2tBnpmgDUQypybumMu7PmAJVkZ0OYHcM06khmY5aIJ/oIUyiNCPe6h0BnJc6jo64Rar19LnSUVVkKwC4CMDaGDapxbttXtZTOU1P48ckSovkrZbybtenjqNjrgOwmTKud08xVZE9OpTK62rloUmTQvbr5GV9f+pAqH/YlJsWylv9UgDXpI6jgzYOu5P7eISFCfThpGXb5amDoH5iEqUF8lY/C8CXQwNsmtcO3uqPoSeqIpPm5dLxhAl0XutXRcblChoZkyjNl7f6CeHu/ImpY+m4wlv9PnRcVWRLAfgSgPVSx9JhO1ZF1pubIuoGJlGah7d6sTDl95zUsfTEe7zV+6HbLg5T0LRwRVVkR6QOgvqDG4voYbzVKqyB8gN3dLsq46RgQadURXYJgFenjqNnpLKRtMgjWig+idLcZLMME2icM73V26JDqiL7NBNolI9VRSaN5IkWik+i9BBvtRw+3zN1HFNgXWXczamDqIrsGACHpo6j59bPy/orqYOg7uKTKM3irf4wE2hrrg5Hg5KpiuxAJtBWXFUVmRyBIZovPomSJNC3h/ZX1B4Xqhr9ctIvXBUZq0u16w55L/Oylj6zRA/DJDpw3uo3y1pe6jim1O0A1lDG3TOpF6yKbKNQTYnaJU2918zL+t7UgVC3MIkOmLd663B2kMZbUm51Zdx/xv1CVZG9EIC0+JKiCtS+78lNEfuR0py4JjpQ3up1AXwhdRxDKSnnrV5inC9SFZn0Ab2SCXSsXgbgiqrIlkwdCHUHk+gAhU0v8gTKD4PJ2GScdXarInuMfLgD0ON6DXrYe8n1ZnoIk+jAeKtXBHAtAOnkQZOzvbe69bXnqshUeAJ9cdtj0wJtWxXZOamDoG5gEh0Qb7VM+cmZt8eljmWg3uytPr7lMeXDnAXlJ2/nqsg+kjoISo9JdCC81Y8NBeWfmjqWgTPe6sNbrEb0hjbGoij7VUV2VOogKC3uzh0Ab3UWnkDXSR0LPWQfZdwnYy8O3UaKdkOiSAfnZX1i6iAoDT6JDoNsOmEC7ZZPeKulKMLIqiL7IBNop5xQFdluqYOgNPgkOuW81bJmtnPqOGi+5Jfvlco4aXw+I1WR7Q/glPGGRZF2zMv6gtRB0GQxiU4xb/UnALwldRy0UP8JBeu/vahvrIpsGwBfnExYFGnTvKxl9zsNBJPolPJWW1mrSR0Hzcj9AFZVxklpufmqimxlAN8HIA3TqbukmtG6eVl/K3UgNBlcE51C3urDmEB7ZRk56xnO8M6jKjI5knQpE2gvSGWqy0IJRhoAJtEp462WDSdHp46DRvZ0qSLlrV5+Pv/sMgDPSBATxXk8gC9XRfa01IHQ+DGJThFv9esByNEH6ieZsn3YJqO79lVyDGbNdCFRJDmPfW1VZKwMNuWYRKeEt3orAOemjoMaWxMnPe3SMI27K4C9UwdE0Z4N4PqqyGS6nqYUNxZNT0eW6wEsnjoWak4tluG///zPlXffcdea3mP5bFZ5XOqxGwBskZf12Nvh0eTxSbTnvNUvDgXImUCnhH+wxhKPWHKLZfPll/cPAvUDHrzZ7bUNAZyfOggaDz6J9pi3WqaLvgbgCaljoXYppYDFMtz/p3tx/z333+IfeODO+kHcudgS6k8A/hkKNSwNQGoiywaWF4R1uOVSx04L9Jm8rHdJHQS1i0m03x1ZbgKwUupYaEyUmpVMvfdvVwffKed+F6gqMplVkp+JjUKJx+3CLlHqlpPysj4odRDUHibRHvJWy0aFr7OH5AAo+T8FLL7YTjjgtzOeEqyKLAfwWgB7AZAm7NQdR+VlfWTqIKgdTKI9461ePHRkWSt1LDRZSqmtcfCd0s5uJFWR7QTg7VIVaTyRUYR35GV9XOogqDluLOqfLzGBDpP3/gv++KeM/N7nZX1eaNwtdZR/PZ7oaETHVkW2Z+ogqDk+ifaIt/rzAOSpgobr7wDWVsb9OObiqsiWlelEAFyX64bX5GV9UeogKB6TaE94q08FwDtXEnfJk6Uy7o7YAaoik81HZwJ4VruhUQR2fukxTuf2gLf6WCZQmsMKAK7zVsvmoSh5WcvRKJka/ka7oVGEi6oiWy11EBSHT6Id561+J4APpY6DOuk7cqRFGXdf7ABhelcq6qzSbmg0or/INH1e1renDoRGwyfRDvNW78MESgshTy+z6uzGysta1lg3BfDL9sKiCNLu7saqyKRgBvUIk2hHeavfBODjqeOgztvQW31hkwHysv5rKE33u/bCoghPAnBNVWSsQNYjTKId5K3eBsDZqeOg3niNt1papkXLy9oBkE5Ad7cXFkV4buhFKtPs1ANMoh3jrX4FgC+mjoN6Z29v9TFNBsjL+ocA5Aaubi8siiDr05dWBdv39AGTaId4q18WOrIQxTjUW31YkwHCrt1XtxcSRdoAwMWpg6BFYxLtVkcWqUbEBr7UxNHe6n2bDJCX9WUAdmsvJIr06qrIzkgdBC0ck2gHeKsfA+Cq0IWDqKnSWy3F56PlZS2FGEx7IVGkXasiOyF1ELRgTKKJeauXCwXln5E6FpoqF3irN2syQF7W8uH93vZCokgHVUX2vtRB0Pyx2EJC3upHhidQ2UxE1DYpwrCpMu6bTQapiuxEAAe2FxZFente1gvtK0uTxyfRtC5hAqUxehSAy8N6e7TQRPqz7YVFkY6vimzX1EHQw/FJNBFvtTRY3iF1HDQIf5BjE8o4+c9oVZFdHs6SUlrb5WUtN+DUAXwSTcBb/SkmUJpwJZyrvdWPbTiObFa6uaWYKN4FVZFJqUbqACbRCfNWSy3cvVLHQYOzcpjafUTsAHlZ/xPAqwD8rN3QaESLyxnSqsik0TolxiQ6+Y4s8kWUwpphHT5aXtb3hDq7d7YXFkWQ8+TXVUX2nNSBDB2T6ISEA/DsyEKpbeat/lyTAfKylqbgmwD4U3thUeTGsRuqInt66kCGjEl0ArzVb5QD8KnjIApe563+WJMBQt/LLcMxGkrnyQCuqIosukE7NcPduWPmrZbdjLKrkahrjlbGvbvJAFWRbSTTiu2FRJFuDU29/5M6kKHhk+gYeavXDfVwibrocG91o9J+eVlfz53mnen8IoVbaMKYRMfEW/3y0NKMf8fUZcd7q/doMkBe1tIUfJ/2QqJIG1RF9oXUQQwNP+DHwFu9kqxTAJDC8kRdd5q3evsmA+RlLU3BD28vJIq0fVVkZ6UOYki4Jtoyb/XjAXwPgE4dC9GINlTG3dhkgKrIjgVwSHshUaSP5mW9f+oghoBPoi0KFWFkjYgJlProUm+1rK1Fy8v6HQDkqZTS2o+dXyaDT6ItCZVgrgGwTupYiBqQM6DrKePkCEu0qsguCGUCKa0D87I+OXUQ04xPou25mAmUpsAKAK71Vj+uySB5We8QZmUorZOqItsldRDTjEm0Bd7qzwPYPHUcRC15KoAbQ8P4JrYB8K2WYqJ4Z1VFtm3qIKYVk2hD3uqPA9gpdRxEYyhYL2ukS8cOkJf1/QBeDaDR1DC11vlls9RBTCMm0Qa81bITkefjaFqtLx++LdXZ/XN7YVFk55cvVUW2aupApg2TaCRv9RHcyk8DsLW3+pwmA+RlLR1fNgDwt/bCoghLyjp1VWQvSB3INGESjeCtfiuAo1LHQTQhO3urT2wyQF7WPwm9SP/VXlgUYdlQsP4ZqQOZFjziMiJv9esBnJs6DqIE3quMa3T2sCqyLUM1L0pLGquvlZf1vakD6Tsm0RF4q18J4LLUcRAldJAy7qQmA1RF9gYAn20vJIr0XQBr5GX9QOpA+ozTuTPkrV4bAIs709Cd6K1udO4wL2uZyTmwvZAokjTJ+HJVZLLpiCIxic6At/ql8sMGYKnUsRB1wFne6q2bDBCq6LAsXXqyc/q81EH0GadzF8Fb/UwA3wbQqIIL0RRaVxl3c5MBqiI7BQALpaf3mbysWdkoAp9EF8Jb/RSp3MIESjRfl4VZmmh5WR8AoNERGmrFm6oia7TWPVRMogsQaodeEUqgEdG8Hg3gcm/105sMkpf1m6QQQHthUaS3VUX23tRB9A2T6Hx4qxcLv9QvTh0LUcc9GcB1TQvWh/KAjaaGqRVHVkX2ttRB9AmT6PxdLmeoUgdB1BMrAbjaW/3I2AHyspbNGXKG9IfthkaRnV/2SB1EXzCJzsVbLdvv2ZGFaPTjEld4q6OPS+RlfR8AOYv963ZDowinVUW2feog+oBJdN6OLFKRiIjiCtaf32SAvKwdgC0A/LW9sCjSF6oi2zB1EF3HJBp4q49jRxaixrbzVp/RZIC8rH8Rzi/+t72wKJLU2V09dRBdxiT6vwR6GIC3p46DaErs6q0+vskAeVl/N6yRPtheWBRh6dBC7fmpA+mqwRdb8FbL06dM4xJRuw5Vxn24yQBVke3IijqdcAeAtfOy/l3qQLpm0EmUHVk6T55C/hK+fg/gNnnbADwNgA5tneTPLMfYXfsq4z7RZICwU/S09kKiSLcDWDMva65Xz2GwSdRbvQ2AL6aOg+bxt7A55UoAv5LkqYyr5veN3mpZjpBpplcA2Dh8PXbyIdMivFEZ16hrS1Vk7wDQ6KmWWnGLNFjPy/r+1IF0xSCTqLdaPnRvAMDuBd3xTQAXSw1PZdwfYgbwVksC3QjApgBkGnD59sOkSFsq4+TGKFpVZMcCOKS9kCjS9bKDOi9rbvwaYhL1Vr8EwLc4BdgZ8tRZKuNuanNQb/WKcmg8VMKhblhLGSc3S9GqIvsUgL3aC4kiXZKX9Xapg+iCQSVRb/VzAHxVNv+ljoUg00EHKOM+Pc4X8VbvCeBovuedcI/MFCjjvtdkkKrIzgszDZTWGXlZ746BG8wRF2/1k0M5P36Ypvet0EZrrAlUKONOCyUcLxz3a9EiLR86vzy74Tivk3q9LcVE8Xarisxi4AbxJOqtfkToCbpy6lgIJyrjDk7xwt5q6RYi/Su5VpqWVCVaTRn3x9gBqiJbAsDXAazabmgU4d15WctszyBNfRL1Vi8ZNhGtnTqWgZPjKq9Xxl2QMghv9XNDt5DHp4yDIFWJXrGgndczURXZYwB8A4C8p5TWwXlZn4gBGsJ07iVMoJ3wytQJVCjjfh5Kyt2dOpaBe3bo/BLdQi2cV9wyPNlSWicMtfPLVCdRb/WF4ZeM0p8TvAodoYz7AYCtAfwrdSwD95KwRhq9Uz4v698A2ArA39sNjSI7v2yLgZnaJOqtlq3wr0kdB+Hgpgftx0EZ943w4UtprQngy95qFTtAXtY/Cp1fWGc3vYurIpOiJ4MxlUnUWy2VTXiWLL3jlXGdXSdRxsla+atSx0HYMCy7RMvLWjYZSRUy6kYiXR0DMXUbi7zV7wLwwdRxEM5TxslRhM7zVu/QtA8mteICZVyj859Vkb1Rql61FxJF+kuos/tLTLmpSqLeann6lGlcSutaZZyU3usNb/WbAZyZOg7Cmcq43ZoMUBXZ/uEoE6X1eznKlJe1/OfUmprpXG+13IEygaYn1Wh6Vw5MGXcWgLemjoNm9SL9aJMB8rL+CIAj2guJIj1ZbqirIpvqAjdT8STKjiydIXecayrj7kRPeavZLaQbPqSMk6WZaFWRSe3kt7UXEkX6jqx7T2vnl2xKOrIwgab3gOyQ7HMCFco46RTyodRxEA71Vh/WZIC8rA8EcE57IVGk1QBciinV6yTqrV4l9J2k9Al0U2WcHDXovfAEVKaOg3C0t1oSYbS8rKXU45faC4kibVQV2RcwhXqbRL3Vzw+/HMukjmXgagDbKuNuxBRRxsn66OdSx0E40Vv9loZjyO7rqfr57KntqyI7HVOml2ui3uonhnl2nToWwmuUcRdhSnmrrwuNvimtNyjjom9qqiJ7ZGhC8cJ2w6IIJ4ep9qnQuydRb/VjQwFxJtD0dp3mBBpsFW7YKK1zwwbCKHlZ/wPABgB+225YFOFtVZG9F1OiV0nUWy1dG64GsFLqWAj7hWMhU00Z928ArwYghesprQu91VLeL0pe1n8GsDmAP7QbFkU4siqyqdg53Zvp3NDS7Fpp5pw6FsKhyrhBHQPxVq8UmolHdx2hVshNzcbKuK/FDlAVmfQg/QoA6TNMae2al3Wvb8b79CQqm4iYQNN739ASqFDG/SqsjbLzS1rS8eUGb/XLYgfIy/oWNh/ojDOrIut1zeNeJFFv9XkANksdB8Eq46ZmLWNUyrgfhhZqcqSH0llCjrZ5q1eOHSAva9mt26hOL7XmC1WR9XbzXueTqLdatkTzhz29Tyjj3o6BU8bJbt1eFNafclJK7nJv9YrRA5S1NInfu92wKMLiUjCnr51fOr0m6q3+OIB9UsdB+KwyTmoTU+CtliLpn04dB+H/AKyijJOuIVGqIjtYZlnaDYsi/A3AqnlZ/wI90tknUW+1tDNjAk3vEibQeSnjzpANVqnjIDwdwFe91dEbvvKyPoHtEzthOQA3VUUm72lvZB3uCdqo+DS14goAr00dRFeFDVbHp46DINXLrvBWPzp2gLysD5cli3bDoghPkmn6qsiegJ7o3HSut7oA8LHUcdCsghbrK+OkrB8thLda+pBKP1JKS46tbNjkZ7YqMlkn5Y1jet8C8Iq8rDu/ia9TT6LeaikWzQSa3ndCQXkm0BlQxu0K4LLUcRDWk6eYJgPkZb1DOI9Oaa0B4Cr0QGeSqLd6ewBnp46DIJ1YtlbG8TzkaOTp5abUQRC2CEfimtg2PAlR+s4v56PjOjGd663eNJTzo7Skrug6yjhprk0j8lYvF6bBX5Q6FsLZyrjoKfaqyGSjklRFem67YVGET+dlvQc6KnkS9VavHe7g5awQpXNXOCrwu9SB9FnYJfp9NkjohFOVcdHnQKsik00u3wOwQrthUYSP5GV9ADoo6XSut/qlYS2JCTR9ApUNGUygDYXzihvLZ3DqWAh7eauPi704L2spVC+VdKRwPaW1f1c7vyRLot7qZwP4MgDpzEJpDzi/Shn3s9SBTAtl3O1hXU3ab1Fab/dWHxF7cV7WPw3vpRS+p/SdXw5AxySZzvVWPyks3D914i9Oc5M10K+nDmIaeaul7daVqeOgWd6pjDs29uKqyLZquvOXWrNLXtafwVCfRL3VTwjnuZhA05LjK5szgY6PMk626LPuczd82Fu9X+zFeVlL4RFW7uqGs6sikx6/w0ui3urlwxTusyb5ujRfOyjjuCN6zJRxcnif5Su74SOh5nGUvKw/C+DAdkOiSBdVRSZ7D4aTRL3V8lqXyg7QSb0mLdAblHEXpQ5iKJRxnwRwUOo4aJZPe6ujKxLlZX0ygE5ucBmYLJQHXLULgUyKrCewqXZ6eynjPpc6iKFRxp0EIHqDC7XqAm/1FrEX52X9PtZM7kyD9muqInve1CdRb7V8aEf/0FJrjDLutNRBDJUy7v0ATkwdB81yibd6/diL87I+BIDUTKa0Zi0RVkX2tKndneutlg/tzlabGJAjwoc4daPR/O6p4yD8U+rtKuNuiR2gKjJZonpVu2FRBDmKtHZe1vdimp5EvdUfYQLthFOYQLtDGSe/ExemjoPwCAA3eKtXbjDGdtLPtMWYKM4LpHFAVWRLYFqSqLdaPrSjt5RTq02135Y6CJrHjn3pUjHlHiXvg7f6OTEX52X9IAA5bvHD9kOjEa0apnYneupkLC/mrX47gHePY2waya0AdkodBM1LGefDNKAUrKe0niwfvt7qp8RcnJf1X8OeD2ngQGnJsZemXXzSrol6q/cFULY6KMVwoaA8a7h2mLf6kQCk4MVLUsdCkHKNayjj7om5uCqyZwL4NgBpQkBpnZmXdfSZ4GRPot5qaT3EBJqe1Gzdkgm0+5Rx8l5JecCfp46FIFO6N3qrHx1zcV7Wv5Zm9gDuaz80GtGuVZGdgD4lUW+1LLBzy3c3vEYZ9+PUQdDMKOOki86WANhFJz2ZEbjcW71MzMV5WUvrtFcC+G/7odGIDqqK7N29SKLe6g2lDFMbY1Fj+yjjWPS8Z5RxvwGwSeiqQ2mtIxvyYi/Oy1pqg7Nmcje8vyqy/TudRL3VcucmZ6UovfeFEnPUQ8q428LGCDm/SGlt4q3+YuzFeVlLEt6z3ZAo0ilVke3eyY1F3modNkWwI0t65yjj3pQ6CGrOW71BOP6yZOpYqNnvVVVkUtkougUbteo1eVlf1JkkGtYMvgmgyUFlasdXlXHrpQ6C2uOt3gZA9JMQtepUZdzesRdXRSZJVJIppbdhXtY3dmU6V1qaMYGm90sAW6cOgtqljJMlEs4sdMNe3mrp3hIlL+t3APh4uyFRpCva7vwSlUS91Z9nR5ZO+IvsBFTGcTPKFFLGncOqX51xgLf6A7EX52VdADi33ZAostTjpVWRPR+pkmi4I2MVnG7YThknB8RpSinjPsYWap1xuLdaniqj5GW9s7TuajckivAkAFdWRZZj0knUW32Y3JG18cLU2OuVcSx8PQChecAxqeOgWT7srW5Si3rLUNWI0pLWaTdVRRZVWCMqiXqrZbv20U1fkFpxuDJOptRpIJRxcgN7Suo4aJaTvNV7NyhYLzWTf9J+WDSi54Un0qjCGiPtzuVOwU75pDJun9RBUBre6rO54ahTs0FRN7NVka0I4BsAnth+WDSi66VcY17WNcbxJOqtXo0JtDOuYQIdNmXcLvx97IzPeaujGnLnZS0dX6TSG+vsprcRgAtiL15oEg099uQoC6Un65/bpg6COmEHaUCcOgia5UJvtZRrHFle1reF5gP/aj8sGtH2VZF9Gm0mUW/1CgC+xLY+nSDVa7YKHT9o4JRx/w03VN9JHQvNqir1RW/1WjEX52UtFd+2bz8sirBbVWS2lTVRb7UKv6CrxERCrbpEGScdcogexlu9bPg9fW7qWGhWveM1lXE/jLm4KrIdJ91Mmhbo8LysP4iGT6JSLYUJtBt1O5lAab6UcX8HIHV2pY8lpT/Ef723+gUxF+dlfT6At7YfFkU4uiqyt0UlUW/10t5qaarNMnLplSwoT4uijPujTPUD+EPqWGjW0tdHYy/Oy1o+e9/TbkgU6aSqyHaNeRKVPnr7xr4qteIOaaGkjONdKc2IMu7nIZGyhVp6T25ycV7WUlqQ54G74YyqyN4y1lZoREREQ9a4KTcREdFQMYkSERFFYhIlIiKKxCRKREQUiUmUiIgoEpMoERFRpMVjLySiYfBWPzZUMJOvZwB4OoAVQ3EBqR273FyXSCWluwH8BcDvwtnnXwL4EYAfKOP+nOhfhah1Cz0n6q2OPUS6oTLuRvSYt1oq+u/W0nBHKuOOwhh18b3yVktJuhtirlXGqRFf6x40I8X9X6aMuwsti4xta2XczUjAW70UgI1D5bJNATyr5Ze4PfRwvFyaK4SC+r37+Z50XA1ea9x+oIx76agXeauXAPC0UKDiSQCeEP78hHBjtmz4kj8/Wj4Wwk3bIxcw5N/CDdxfww3cnQB+G8pifl8ZN5bymHwSnQ9vddZy6UPpuDHWJEqzfsmaXi/v0T4diW3iv5ve6rUBSIWW1wBYZowv9ZzwJX/Xf/ZWfwzAB5RxD4zxNakjvNXXAXgmgKcCWGxSv2fe6nsByI3p1QAuayupck10/tYKd0Nteam3WqbAqNv29FavjIHdMHqr3+itlqnWrwHYZcwJdG6PD6VGH5zga1L6JtgrtpxAZ5pkXwngZAC/8lbf5K3e2Vvd6IaVSXT+Xj2GMdlQu/vkl/p4DIS3Wn4mJXl+BkDKm4drlXFdnaqk6bWedMoCcJu3WlrRRWESnb9XjWFMJtF+2NxbvQWmmLf6md7qawBcDCCqdVfLrk0dAA3aStLL1Vt9ibc6H/ViJtG5eKtlreZ5Yxh6vbDLkbrvuKZTPF3lrd4DgDSO3gTdwSRKXZmBvNVb/cJRLmISncxU7uy/63E84VL7ZGpTks3U8FYv6a0+A8BpE17zXJTblXGyi5KoCzSAr4zSXJ1JdHJJVHBKtz/e562W7fW9562WDRWyI3JGTYYnTKaVibpEZgyv8lbLOehFYhKdg7f68WFn7jjX2x4xxvGpPbI28i5Mx8+0nNV9BbqJU7nU1SfST83kG5lEH27rMf+dSALdbIzjU7sO6vPRpPAkLQUNXoZuqmOLcRBNwPbe6kXuHZjKzRMTnMqV6hjLRkzpfnHEayiNpQF8EMDO6BlvtRzX+RyA1cf8UlLh6Y8A/gng3+H3YZnwJP+YRVz7bWWcHIAnmon/hhKSvw1lJf8RPoMfDFOw8vXclitsHbWo2RIm0SBMs47ylCjn2o4AcOKIL/Uq+YBTxvFweT+8wVt9sjLu2+iXo8LB8jbJB9ZVYR3zFgA/UcZJ4pyvsBv9hWG3u1RD2iRMk83GqdzJ+AGApmUxF+aXmIzNZlIqMfzcySbOvQCs0/A11/ZWv0gZJ+ep54tJ9OFVNBZUk3F+fgrgkogk+riwPnXTiNdROid4q9ftS0EAb/WGAA5rcUi5gTgFwIULS5pzU8bJ08JXw9epITZJqq8PlZG4qWgyDux7LfNRhJ+7s+TLWy03kqcDWAHxXhuKkswX10Tjp3K/poybXdx4VNyl2y/rhHqyneetlqlUaZ4wUgH/hRSK30YZt4Yy7rOjJNAFUcbJ0+u7Q9m3JAX2aTiUcZeH39/fNxhGmjEsEJPo/y84P+oZzq+H/4zZGMEk2j/HyllLdN/hIUE19ZHQ1eZLGANlXC1f4xibaE7KuF8BeB3iLbRDDZPo/6wG4ImRSVRaOo1qRW/1SyKuo3Skj+YB6DBvtbSVOriFzRt7KOMOUMbJxg2i3lPGyZLCZZGXL+OtllZt88UkGjeV+ydl3C8aJFHBp9G0bgubw0bx7pkewE5E1kGlF2gseTJ8ozJOpoOJps1nGly7wDVVJtH/2WbE7//K7D8o4/64sEXnhWASTetb4QzlKKTyz5HoIG/1Ci1UJJINKOe3FBJRF3/nYy2wVObgk6h0tAjb8Ecx94YIafI6KvYYTevRoa/gqPb1VstZtK7Zq+FT6EXKOFkHJZpWruEyx3wNPolG1sqde7v4lZGvzafRdJYJ9WTlqFKve456q2Un7u4Nz38WLYZE1EVNdqz/dUH/gEl09CT6t9BKau4nU6nYMiom0XSWCec+Y56+tvZWy7nirlgjbHyKdbQyTioPEU0zHXmdFMaR44zzNegkGipbrDviZV+de2u+Mu5fkcUT2GM0ndlrHGcv7C5zEQUYshGe9LraeUhuCssWYyHqqtgmDN9VxnE6dwG2ivg7eGhT0VykHNqo2GM0neXl/4VjHJ+MuF6OKL15ht877vOQmze49gxl3LiTPFEXSJWsGAv9bB96Eo25g1/QJqIvR8bAKd005tyEcwIAmU0Y1QdSt7bzVi+3qMPgi/D5FsMh6iRv9ZYANh3H0ZjB1s71VsuH6BYjXnZXKOY8D2Xcz73Vv4lYm5rVY1QZF7OmSvEeSn7KuD95qz8VUUzhyaG4wdFIWygkdsPEXQ23/ffBDd7GLoVNjZO81eMqQH+mMu5MdJi3+llh2SbGZco4KX+5QIsPvOD8o0a85spFFCGXihj7jzjm7O4xbI82WXOX8LNyfAXAEiOO8w5v9SeVcX9GGi9ocO3X+1JUnxoZZ3W0G9Fh3mpZ6jgHgDSnj9lQJGU0F2rI07kxa5GLWvfklG5/PGwaVhl3R2RFE5lOfc8ivmeca45Neid+s8U4iDpz5Mtb/Qpv9SXh+GFMAhXHKuPmPokxj0E+iYZzdaNWKapnUFThxrC2Js2cR8EeowlIQXll3H/m+J+OAbBbxPTovqHn6II6+jzY0SS60Gkqoo556QKm5pcPX08BsAqANQEssNbtDEmt3ffO5BsHmUTDX7T8hY/iFmXcXxb2DbKu6a2Wri6yiD0K9hhNQ/rHPpRElXG/9FafPcKu29mWCA3aF1R2736Mj1TcirXAs29EHXTihF7nxwC2W9ixljkNdTp31KfQUaZqr0AcTul2wwcinxzfGDYwzM+MfhkjjdJIfm5Vi3EQTYNbZL/Moh6Y5sQkOnMzPQfKJNqzs6JzkqdRAGdFjCXlAKXZ9Pz8Y8w1gGOx1RnR/3cGgPVltz5GMLgkGoq+j7pb7W4A357JN4Z1sZi1JvYY7Y6jIp8eF/Q0Oue6a9sW2F1iBu5rMQ6ivpIb582UcbvH9NAdXBKNLLBw7YibfkZtsTUbn0Y7IOzU/XSLT6PjTFZN9jWMesSLaJr8IGwkfKEy7prYQYaYRMe5Hjobp3T7732RVYzkaXTughv3YnyarLc2aZ1G1HffAHD9XDv0RzaoJOqtljWw9SMuvTpie3TMehN7jHaEMu73kR1eFgtVjCaVRJusaz6xxTiI+mYfAFJpbv9w7DHKoJJoOHoy6vTXD8IH6owp4/4tU8CIw6fRyVnUL84xkQlwd2+1HFuaRBJtUsjhaS3GQdRHcqb/FGlCIWf1YwYY2jnRmKncl3irJ1kaTZLoyRN8vSFb6M5WZdxfvdWSSD8Ucexk33BcZtxJ9A8N+iRKycAvYbqdNebzsDM6kJ/Yhsq4Tpfn64C9wvLNqPWzh5NEvdVLhNZnXTerx6gyTnYEj+LeyOMOTY5IzKQkXoxxJp1RnRJ+saTY/Chkiuj40Gt2nP8+vw1F6GOsiul35jgTiLe6D0mUZv47e7My7nyMYEjTuRs0+FCfpEn3GF2ig2N3pih66K5zZMSlOYA3hT+Pq4MGGj5lyQ3bkD4DqN82VMap2V9hKlbK+8nRwD0BnBu5GXBOH/FWj/RgMaRfoJip3FRi1kVHOiA8h9jizDMx57rgKFJ1RFnYIeyfRFx3cNiwIC3HxuVHDa6VRL9Gi7EQTYzsPVHG/VGKxCvjTlfG7Qzg2SMUxlnQ78Q7R7mASbSbZvUYnVAJt6aFmhdmhQbrfJ2hjHtAWp5FXPq80HJvnP8+MyoCshDywUM0FZRxDsArGzabL7zVMz5DPYgk6q1+ac92Is7uMTqK30W+1nMwPitFXhf77zKqGf/8K+Pk7O91Ea+x/5ifRG9vuOa6i7d62RbjIUoqFMaRIgq3RQ4h07lvmOk3DyKJRlYp6tuU7kjHcOYgNxjj8sLI6/4PkzHqGrmJWK+V9e1lx1X6LzTVjknus0lsB7UYElFyYUPf3Oe1RzF7P8MiMYl216weoyN8f8yanXjuXGcaWxGmo1+aYJ1vbJRxP4goTp+F4y7jfBqV5sNNHOKtHnX3MVGnKeOk0twim2ovwDrealkfXaSpT6LeajlD9zL0z+weo+NOPLLxZQu0b8MGR6gkWXXV4RFVgvYc845jqdUs67axZP3n9CZVW4g6SvoDj/VzceqTaM82FDWZ0pUn0TrydV6H9r028rp/NFjLGLtQver4iBuisa3JhzPFlzYcZotwg0A0TT7f4NrNZ/JNQyi2EDuV+3UA0ZX959qhuVODJDqj9Spl3N+91dJQdvWI19lKWniFXpqNhenh2H/n68Ju2C47DsDeHas9K3V+t284xvu91b9XxsV0sCHqHGXc77zVP5dlq4jLN5HZmbDvYJhJ1Fu9XCiyEOMkZdwFLcQgH7Q7zqBO64J6jL5UGff9GX7/9ZFJNAs9NNs67vDOUPoOEyj2P3HKuPu81e8BcCo6QqryeKtlSv9FDYc6PVTMGvVpm6irboxMonn4ffrhkKdz5XF8yYjr6oY7Hh8ih4EB3DqhKd3YFmziDd7qV7V0nCh2t6dvYVpykgUYfoxuaWs69jhv9RckmbY0HlFKTco+brKob5j2JBo7lXtrRO3acTTpHjWJ3tzweMhnvNXRTzJhh+clDWY4rg0NsftyFu0QdIgyTorJ39TScNuHNlH7eqtjbkSJuuIrDa4dbhL1Vi/eoOB8k7JR83NZg2uli8yKM/nGMHd/ToPXkkPGN3irNx71Qm/1i8MPa5N+qKehR5RxV7a0bt4mORsnCb4NUhKyBPAbb/UR3urY4hmzSJ1euUnzVh/qrb7VWz2jjRtELWwG/HXk5esv6iZyXGui8kGMMdtNGXfmQv75ugAeEzl22x+MMp37xwYbUeRp9KQZfu8nQ4m62OLvsinoGm+1bC75oDLu1zNY85Xp27cBWArxZPH/IvSPFGD4flduSJVx3/VWf6jlnbZPDmvmR4V116+GcoM/DwXwK2XcrJ3hoaC97EVYPtxQPSt8vTzU6Z2zuPfLxnDDSvM6yVs9ziYIsx04wv6NSZOf2WdGXCd7O9Za2AzPNG8sip3KvQ/AN9oMRJ4QvdWyXrn7uJOoMu7OkADfEvlas4YBsEdoLv218AP0s9CNRIUPwueEc6zrtfRzdGQPduXOQxn3I2/1GeHvqyuk68z6I54znqkXha9izv/RW/1gqMo0Ss3nPp7f7iPpcjIJy6O7ZKnrzQ2mdBeYRDtx99yx86E3KOP+i/Y1mdJdd8SqQtIM+n40p8IH8eFhmlj+Hb4U/nxEKK6+eEs/4CP18OuY97T0992KcDOyA4A7J/iyUl1r1KYJTKLU+3XRqUyi3uqVATwj8vJxrXFd06B+6kg9RkMng74cnJd+nbvPng7sI2XcH8LZ0c4Iu8K36GBbuTk9i8XvaRKUcdKoIXbT4uoL6zE6lUm0Ya3cq8Z1trDhzslRC9J/NDzhdd3+yrhfoP+O72ALt5+G2YKuJlI1walGoqsjr8vC79EC/+E0ik2id4Q7lnG5fFI9RsMRjB0nPKU3KivNdDEFlHH3h2ndzq3ZSjFtAL9CN8mGI6JJkN30sbYYTBL1VkuT6dU6Wi1H1hNjLT3TWo5zTTNu2dEnkY937ZxlC87oYgeacGO4esNiHOMyzlZ8RHNXdItdNpLP0WEk0VHWDiedRMNxkdsmOKUrr/mTcNxnUj06F0V+iA9TxhWLqknZN2FdV468dE4oHrJ1aBI+aheaceLmIpoIZdxfG1QvemrYazOIJBo7ldtaqb8xTumO2mN0FmXcbeHD6mKkJYl8A2XcMZhSyjjZQCZ9DDtHblqUcR8NTREa14VuycqsiEQTdEHbU7pTlUS91dIXceRqO2Mq9TeOoy6PDU+VUXdhyjgp5bYrAKngMUnSZV4KALxYGSeHnqfdO1qsGtQ6OUusjNsxLHtckDhWOSI13zt8ojG4qMGU7qunPokC2KxB1ZxJVU6RHbP3TnJKd07KuLNCoYRDJ7DpSG5KTgDwbGXcu5Rxf8MAKOOkMH3n24kp424JyVQquXw4wSY0ubk6D8BfJvy6NFDKuKrBbOA63uqnTHsSbdKA+5oJHoS/KlUSDTHcr4z7cDhLK3dXZ7W4+UgS57mh0fdTlHEmnFsdmiO6VIBhYaTovzLu0FCmTzYgyc/GdwC0XXRE1sBvCy3kpGn7E5Rxr1PGdWW9nobhhMjrZldye/j/6P1U7e2gSKHmqUyrrRrWT6XovRRAljsvWbOafdhYpv7uC08R8uQiCVI+BL8bagTfFo7XUM95q5cOR1BWCTdcK4Yv6bMoSyfLzKdi1d1zfN0Rfjak2bucWf2BMq7JLAxR5zCJElHTm6/FlXGx1biIeo1JlIiIKNK0rYkSERFNDJMoERFRJCZRIiKiSEyiREREkRbaULkqsln/PC9rOdtIU8BbvUTqGAZOSu/x94mi8Xc4KRV+h/87oyQazoGVVZHJIex7xh8fLcAjpZVVXtZ7NRnEWy0tsT7fXlgU4UFvtfRQbdLRhwbIW/0aAO8CsELqWAZqCQCfA/DFOQvZLzSJ5mV9b1Vk54WLKK0NqyJbPi/rHRqM8fxQQIHSutRbLYX4mzRppwHxVp8DYOfUcRB+HIqHjHZOtCqyzUIvwpE7iFDrPpGX9b4xF3qrHx9KDrIRcnpSR3gTZZyU1yOaL2/1sqFo+iapYyEco4w7LLrYQlVk24U3k9I7Ji/red7MmQgFlGUq4lnth0UjkmLY64dWdUQP461+nMxaAFg7dSyEs5Rx0gGrWcWiqsh2CcXKKb2D87I+sUEilTq3XFtJ7y6ZGVDGTbo9HXWYt1rqE18P4IWpYyFcpYybby/RkY+45GV9NoCDWgmLmjqhKrK9Yy5Uxv0OwCvCBzilJTcyN3qreUNDs3irZZboG0ygnfBtANKHud3auVWRHRVaPVF6O+VlfX7Mhd7ql4cWcNLsm9K6NayRchf8gHmrnwDga9KDN3UsBOlAtJYy7g+tF1vIy/q9AD4WHRq16byqyDaIuVAZ993Qg5Wty9JbJezalXNoNFxy9IkJNL1/AthsUQm0UcWivKz3A/CZ2OupVVdWRbZazIXKOLnr3bz9kCjCugAuTx0EpeGtvgDAGqnjIEghhY2VcbePvexfXtay0ejiJmNQK5aSs7xVkT0v5mJl3HUA5CA3++Klt6W3+rOpg6DJ8lZ/CMBrU8dBs+ykjJM16cn1E62K7AYAUVOK1KrfyLRgXtZ/jbnYW/16AOe2HxZFOFUZF7VxjPrFWy3nvsvUcdAsb1HGfQoJCtBvFjZGUFrPAPDVqsikTODIlHFS0mr39sOiCHt5q6OOMFF/eKu3ZALtjHePmkBbS6J5Wcsc8isB/LSN8agR2RZ/eVVkMsU7MmXcGTzG1BkHequ5C35KeatXBXBh6jholo8p445GylZoeVnfFRLpIncz0djJ1LpsUoiijDuJR5g64yhv9QGpg6B2hXPBl4TmEpTWRco42SgbpZU10TlVRfZcAHJ0gj8c6X02L+s3xl7srf4AgMPbDYki7aqMY7WwKRCOMf0AwItSx0K4WRknu+K705Q7L+ufA9gIwP1tj00j27kqspNjL1bGvRsA1+W64Uxv9UIrp1BvSFcsJtD0fgJg66aDtJ5ERV7W3wqlkth8OL0DqiKLnppVxh0M4LR2Q6JIF4aNKNRT3mrZRPSq1HEQpPTpNsq4e5sO1Pp07pzY+aVT9svLOrrKlLdamnnv1G5IFEGqS62njPt66kBoNN7qdwKQ86CU1r8BrKqMk96g6HQSFVWRSfsY2fFJ6e2cl3X0OVBv9WVh8xildZ80EFDGyboa9YC3+nUA5AgZpU+g0n5QZktbMZbp3DnlZX0mgEPG/To0I5+tiqzJVNK2AKS6EaX1KABXeKufkzoQWjRvtXRMYonU9P4TpnBbS6ATeRKdrSqy98th1om8GC2MrFNvkJe11Mwdmbd6SQA3AViz/dAookLV2sq4P6YOhObPW70SgO8AeEzqWAjbKuNkUxd6mURFVWSy0/PAib0gLcg/pNB1XtZRawLe6mWkMhKAl7UfGo3oNgCrK+P+njoQejhvtcwYfA+A9AeltHZWxo2lpOnYp3PnlJe1VMKRxt6Ulpzh/XJVZHKXPDJlnBxf2ip8gFNa0nTg2vCBTR3hrc5CWzMm0PT2G1cCnXgSFXlZvzmck6K0dCgPGDXNFKYQpYXaHe2HRiNaXarfeKsXSx0IPUT2grApR3qHKuPG2vt64klU5GUtG1RuTPHa9DBSXerqqsiiPnyVcZJANwTwp/ZDoxFtzDqs3eCt/iSAN6WOg3CUMu7D436RJEk02IadXzpBimB/OfZiZdyvwx333e2GRRG29VazNGBC3urjAbCFXXonKuOOnMQLJUuieVn/Payrzah7OI3VplWRSTGFKMq4n4bzo9zckt4ubKGWRui4Y1LHQTg9VFqbiInuzp2fqsieCkDO7TwpaSAkTs/Les/Yi73V63OavjOOUcYdljqIofBWvwPA2KcOaZHOV8ZNtLJayuncWfKyvjOsq/01dSyEPaoiOy72YmWcnB9lRaNueJe3mh14JsBb/TYm0E64EoBUhpqo5E+is1VFJjsMrwWwbOpYCO/My/rY2Iu91TsCOK/dkCjSW5VxUvScxsBbzbKm3fAVAJso4/472CQqqiLbVHaLpo6DZtkzL+vTYy/2Vu8G4NPthkSRdlLGnZ86iGnjrX4tgAtSx0H4XqglLUVkMOgkKqoikxZqX0gdB82yQ17W0ccmvNUFgLGe0aIZ20wZd03qIKaFt1qWLaQhA6X1MwBrtdHSrLdronPLy1pap+2VOg6a5XNVkcn5wyhhGvHQdkOiSBd5q1dJHcQ08FbLkS7e6HejdvQWKRNoJ59EZ6uKjLvdutM6aO28rL8bO4C3+mgA3Cma3t2hzu6vUgfSV97q1ULd6KVSxzJwfwawjjIu+RHJziZRURWZHJZ9b+o4aNbO6TXzsr694SF0nqFLT6pMraaMq1IH0jfe6hcBkGborFOc1v1hCvdH6IDOTefOKS9rSaInpY6DZrVxuqoqsqfEDqCMezsA7hJN72myec9bvVzqQPrEW/3cUNmLCTStf4VduJ1IoJ1/Ep2tKjLp/MJalOn9EMC6eVn/LXYAb7U0J35ju2FR5JGAjZRxD6YOpOu81U8KT6Arpo5l4B6Qc+jKuE6d4Oj0k+hseVnvAuDS1HEQXgzgiqrIVOwAyji5Gbq43bAownrcHLNo3urHhSb0TKDp7di1BNqbJBrImSyWlEtvnRZuaHZoUvSeWvNqb/VpqYPoKm/1Y0IBmGenjoWwizKukzffvUmieVlLJYpXAfh+6lgIW1dFJtOyUcIU4rZhlyOltYe3+kOpg+gab/UyMusC4KWpYyEcoIyL/rwZt94kUZGX9X2hzu4vUsdCeGNVZNGbvpRx/wk9MKOPzlBr3umtPih1EF3hrVZhtmXN1LEQDlPGfQQd1qskKvKyvkd2ZwFwqWMhvK0qsvfFXhzqXMp72ZmddgN2grc6uoPPlJEEulHqIAjvU8Ydg47rxe7c+amKbGUANwB4fOpYCAfmZX1y7MXe6qeG93KldsOiCDso46JLPfadt/pzKTqB0DxOUcZJd5zO620SFVWRrRG26i+ZOhbCznlZnxt7sbf6GaGv7BPaDYsirKeMG9x6tbdaGi7snjoOwpnKOGlg0Qu9TqKCnV86Zeu8rC+Pvdhb/XwANwN4bLthUcSBdqkIM5hNfN5qWXfbL3UchAuVcbJ7vzd6tyY6t7yspTOF9K+k9C6oikzOH0ZRxklHhs0BJC0oTVhaOpR4q5+JAfBWf5AJtBOu6ONUeu+fRGerimwPADzzlt7fQ53dn8YO4K1eN5zP4zR9Wj8HsEbqLhnj5K1+FwBJopTW1wCs38cKWlOTREVVZFLgXAqdU1p/ArBKXtZ3xg7grd5M6vW2GxZFuCUk0hpTxlu9v2xgSR0H4Xth+UA6RvVO76dz55SXtZVt0anjoFmbg66piiyPHSCU93pNu2FRhFXD1G50qccu8lbvzQTaCT8DsGVfE+jUJVGRl7W0Tvto6jgI0vXisqrIpPJLFGWcNGjfud2wKMKWAM7BlPBW7wTgk6njIPwewFbKuLvQY1OXREVe1jJN89nUcRBWa1pnVxknx2b2ai8kivQGb/Wn0HNhvf3zqeMg3Bu6CP0WPTeVSVTkZS3ttr6YOg7CRlWRXdBkAGWcbBg7uL2QKNJe3urj0FPe6hfI7EjqOAj/ALCpMk42rvXe1CbRQI6+XJc6CMJrqyJrNH2mjDsRwBHthUSR3u6tPhI9461+YpgVYTPy9D1Bt1bGfQdTYqp2585PVWRLhko47MaQ3nF5Wb+jyQDe6mMBHNJeSBTpEGVcL3bCe6sXB/BtAC9LHQthW2XcVM0QTvuTqEzrSreQDcKZN0rrkKrIDm0ygDJOkvDH2guJIh3nrd4X/TnEzwSa3m7TlkAHkURFXtayiL0VgDtSx0I4piqytzQZQBkn1WU+3V5IFKn0Vne61qy3+gxZf0sdB+FgZdyZmEJTP507n84vXwewbOpYCDvkZd2oW4i3+nwZp72QKNJOyjh5L7pYzk8qElFaH1DGvQdTalBJVFRFtjqAbwzlKbzjNsnLutHGL2/15WGWgdKSzSLRzQfa5q0+EIBsRqO0PqqMkyOHU2twiSQva9lgsDWA3tVonEKXVEUmZ0mb2J47sDvhIm91JxpZe6vlXDETaHqfnvYEOsgn0dmqIpNpwM5NQQ3QnwGsm5f1bbEDeKuXCQXr12w3NBrRPwG8SRn3hRQv7q1+REiejdbcqRUXK+PkBnfqDTaJiqrIpPErN6ik5wC8PC9rKVwfxVu9dCiW/sJ2Q6MIpwJ4hzLunkm9oLd65VCl7MWTek1aoBuUcZ2YlZiEwU3nzikva9m5J51fKC0N4MaqyJaPHUAZJ42kNwoFrSktmU79obd6InWPvdV7AvgmE2gnfAvAqzAgg34Sna0qsvcDeHfqOAg3A9gsL2uZFowSGknfAOBp7YZGkaTk46HKuF+3PbC3+nmhitXr2x6bovwUwIbKuAoDwiQaVEUmnV/emjoOwhV5Wb+yhQ9X6VEoU7zUjUbtkuyuUsY1ninwVsvPx+6hu4ysg1J6f5Qewso46cwyKEyic6iKTNo9sfVWeufmZd3offBWrwHgJgBLtRcWNeTDLIFMvV4C4MfKuEXOOnirZdnp6QDeDGA7Ttt2zt2hqfbtGCAm0blURXax1HdMHQfh5Lys5axfNG+1nB/90tDX/jvstnCj8/vwQfw7APeEpu4rAngSgKeGqXnZMPbI1AHTPP4mSzDKOFkLHSQm0blURSYfuNcDWD91LIQj87I+qskA3moeZSIan42VcfJ5OVi8Q59LXtY1gM0B3Jo6FsKRVZEd1GQAZZxsbNmnvZCIKNhu6AlUMInOR17W/wYgmxeiCwBQa06oimyXJgMo46SX6eHthUQ0eHsq42Rde/A4nbsQVZE9A8DXwtoMpbVNXtayvhnNW22lm0R7IRENtiMLyyoGTKKLUBXZ80ND30eljmXg5Ad1vbysb26hNdau7YVFNCjvVca9L3UQXcIkOgNVkclxiRt57jA52bm5QV7WP2gyiLdapqFe3V5YRINwkjKu0R6FacQ10RnIy/pb4djLA6ljGTgpC3hFVWRyZrAJdn4hGs2ZTKDzxyfREVRF9hoAjRpJUyt+CWCNvKzlbGEUb/VSoUH7y9sNjWjqXKKMkyIXNB98Eh1BXtbS4kmKXVNazwJwTVVk0SXflHGyA3tTAIOsskI0Q1Jh6rWpg+gyJtER5WV9urR5Sh0HzXqCvLwqsiViB1DG3R3qr0orNiJ6ONlQuY0y7sHUgXQZk2iEvKyPA3BM6jgIGwI4r8kAobuInAn+R3thEfXez+UGUxl3X+pAuo5JNFJe1ofJbrXUcRC2q4rstCYDKON+GKZ2ieh/MzPrh5kaWgQm0QbyspbdameljoOwR1VkH24ygDJONhlt015IRL1UhYLyd6UOpC+YRBvKy1oO7jeqpEOteEdVZIc0GUAZ96XQbotoiP4Z1kAb93wdEibRdmwbWjpRWsdWRbZ3kwGUcWcD2K+9kIh6Y+shtzSLxSTaXucXSaTfTR0L4ZNVkb2+yQDKuI9JB5n2QiLqvB3YkSUOiy20qCqyHMA3AUjhekprs7ysr2kygLf6FAD7txcSUSftpYxrtDlvyJhEW1YV2dMAyJTIE1PHMnDyg716Xta3NBrE6s8AeGN7YRF1yoHKuJNTB9FnnM5tWV7Wd4TjEtwenpYCcGVVZC9oNIhxbwJwaXthEXXGEUygzTGJjkFe1j8GsBUP8Cf3ONk5XRWZbjiOlD1r1IKNqIMdWd6fOohpwOncMaqKTJ5Ir04dB+GnAFbNy1q28EfxVj8yrHe/qN3QiCbuLGUce+q2hE+iYxQ2tuyQOg6CTOleWxXZkrEDKONkVkFuin7VbmhEE3URE2i7mETHLC9raZ3W6OwitWJt+QBpMkCo4iLT9H9sLyyiiZEeujulDmLaMIlOQF7WpwI4NHUchFdWRfb5JgMo424PBev/1V5YRGN3q/zcKuMeSB3ItGESnZC8rKW26wdSx0HYqSoyKaYQTRknRTW2aC8korG6DcAmoYcutYxJdILysn4PADnAT2kVVZEd1WQAZZyUeXxNeyERjcX/hZZm96QOZFpxd24CVZHxAH83HJiXdaNzct7q3QFIo3airpHEuY4yTnan05gwiSZSFdllYW2N0to5L+tzmwzgrTYAjm8vJKLG/hMSaKOKXbRonM5NR6YCb0wdBOGzVZE16iOqjLNc76YOqcMmIibQCeCTaEJVkS0H4AYAL08dy8DJXfvmeVk3uqnxVsuGpaK9sIiibKeMuyR1EEPBJJpYVWSPCdvP2fklrX+FgvU/ajKIt/o8ADu2FxbRSN4ceuLShHA6N7G8rP8KYAMALnUsA7c0gOurIlupySDKODnM/uX2wiIaqSMLE+iEMYl2p/OLbDL6S+pYBu7xAC6riuxJLRSs/1pLMRHNxFHsyJIGp3M7pCqyNQF8FcDiqWMZOJleXysv6//GDuCtfhQA2djx3HZDI5rHR5VxbB6fCJ9EOyQv62+G2qyU1ipNu+8o4+4DsD6AO9sLi2geZzKBpsUk2s3OL69PHQdhg6rIZJNQ04L1m8skQ3thET3kQmXcbqmDGDom0Q7Ky1qKpO+ZOg7CjlWRSfOAaMq4nwGQc6isW0ptul4ZxzaLHcAk2lF5WUspuYNTx0HYsyqyE5sMoIz7FqtTUYtkrX2z1EHQ/zCJdlhe1vLhfXTqOAgHVkX2riYDKOOklyOn6ampn4RqRA+mDoT+h7tzeyC07mIlnPT2zcv6E00G8FbvA+Dj7YVEA/L7UA/3t6kDof+PSbQnqiKTIul8kklvx7ysL2gygLf6EADHthcSDYDs9l4lNIWnDmES7ZGqyC7nEZjk5Bdms7ysr200iNUfAvDO9sKiKXY/gI2Ucd9OHQjNi2ui/bJdKFhP6SgAX6yKbI1Ggxh3KIBPtRcWTSkp+LEtE2h38Um0Z6oieySArwN4SepYBk4aHq+Rl3Wj6TVv9RcAbN9eWDRlJIF+MXUQtGB8Eu2ZvKz/EQrWc20kreWlH2xVZE9vMogyTvrKXt9eWDRFdmUC7T4m0R7Ky1qegrZkSbnkpFD91VWRNa2Puy0ATtfRnA5Wxp2VOghaNCbRnsrL+tcAtg679iid5wD4RlVk0rklijLu72HD2C/aDY163JGlUYEPmhyuifZc2ODyjbDhhdKSco3vDK3tRuatfjKA7wJYof3QqCdOUsYdlDoImjkm0SlQFZlM7V7GmYVOkCbr8hRxSl7W9456sbf6hQBuAvC48YRHHXaGMm731EHQaJhEp0RVZK8D8LnUcdBDfgNAmiSfnpf1aFPuJz/9Ff6BB2Wz0RJji4665hJlnBxho55hEp0iVZHJXawUrqfu+D95wgBwTl7Wv5rxVaesuDF8fYV/sF5yVnkHmmY3KOM2Sh0ExWESnTJVkbGkXHcPzV8K4GYA0jP2N+G40gI98KEnFosvvdTHfF0D/D2dVt8J1Yi4QbCnmESnUFVk0vnlsNRx0EIT6h8A3AZANiHdDeBf4Z8tDe8f/+ADeIrK8ORHPW7ZFz1qhccAdQ1f83d1yvxUznwr4/6UOhCKxyQ6paoiK6XrSOo4KJ4kzfoBYLkVlsMyT3g0/IN16pCoPXITtZoy7nepA6FmuJtzSuVlLa3TzkkdB8VTmUK2OPD3v/zt/v/+418Xq8UWSx0StbeDez0m0OnAJDrF8rJ+Uzj6Qj2l1Kzjv4cscVgl9XU/kzoeauxvADZXxv0ydSDUDibR6bcNgK+mDoKinf3ET/j/NfE+8P92AXBl6oCoke2VcbKZiKYEk+iUy8tZu1G2APC91LHQyG7NP16/eT43RVKhivpnO2XcdamDoHYxiQ5AOEohtVnZ+aVf50vnacCujJOdva8KOzupP/ZUxl2SOghqH5PoQORl/cfQ+eWu1LHQIj0g0355WVfz+4fKuL+E2QVuTOkHo4xjEZQpxSQ6vM4vGwOQriHUXa/Py1oK0S+QMu7O8F6OXJ+XJuoIZdwJqYOg8WESHZi8rH8CYDMA/04dC82Xycv6wpl8ozLu57LTk+9lZ52ojHt/6iBovJhEBygv62+GDSo8vd8tx+VlPdJTizLuW+G9ZNWUbjlLGXdw6iBo/FixaMCqItsBwPmp46BZpNvLnrEXe6t3BHBeuyFRJHZkGRA+iQ5YXtYXAHhL6jgIZzZJoEIZJzdDLPOYnrSwk5tTGggm0YHLy/pTAN6VOo4BuyAv693aGEgZ9wkA725jLIpyq0ytK+NkdzUNBJMoSSL9ENunJSEFMHZuc0Bl3NGhGThNlmzy2kQZd3/qQGiyuCZKD6mK7CMA9ksdx0BIsYR18rK+ZxyDe6tPA7DHOMamechxo7WVcS51IDR5TKL0MFWRnQVAarTS+PwuJFCpSjQ23mpZJ+X63HhJ4Yt1lXE/Sx0IpcEkSvOoikw6v7wydRxTSppvr5WX9fcn8WLe6huk8fMkXmuA/hOaarOW8YBxTZTmR2qzfiV1EFPq1ZNKoMFmYcMLtW9bJlBiEqUFdX7Zlp1fWvemvKyvnuQLzlGw/rZJvu4A7KKM+3LqICg9JlGar7ys/wpgawBSuJ6aOzQv63NSvLAy7g9hev5PKV5/Ch2sjGODdJqFSZQWKC/r3wNYnwXrGzslL+sPpwxAGSfNBzYEIG3xKN4HlHEnpg6CuoMbi2iRqiJbF8A1AJZKHUsPnZuXdatnQZvwVst7eS2AJVPH0kOnKOPeljoI6hY+idIi5WX9VQCvTR1HD30JwBvRIco4eS957GV05zKB0vzwSZRmrCqyXQGckTqOnrguL+tN0FHeaknuXNebmWuUcbLLmWgefBKlGcvL+kzZIJM6jh64oevnbJVxssmJrboWTdoGvjp1ENRdTKI0krBBZqSelwPztXAWtPONssMGmWNSx9HxerhyFvSfqQOh7uJ0LkWpiuxzAF6XOo6OkXO1a+dlLVWJesNbfSqARq3YptDdAF6qjJO6uEQLxCRK0aoik8IBm6aOo0MJdL28rO9DD3mrLwLARtL/8/dQUP7HqQOh7uN0LjWxPQBWbQFuljXQvibQYMdw9GXoZhUZYQKlmeKTKDVWFdl7ARyJYTojL+vdMQW81cuEmskvxzBJV53tlXHfTR0I9QefRKmxvKyPCvVZpS3UkBwyLQlUhIbSUozhPAzPjQBezARKo+KTKLWmKrJnAPgogK0w3aQW7a6TLiY/Sd5qORP8QQBPwvQ7G8DeyrjO76im7mESpdZVRSZTuzLFO42krdgb8rK+HVPOW/10ACdO+Yajo5RxQ12KoBYwidJYVEW2DYBPAngipsexeVm/EwPjrd4NwPEAHovpsrsyjhW4qBGuidJY5GV9qawxhendaXj6XGeICVSERCPv5WcxHaSZwqpMoNQGPonS2FVFtgGA9wN4BfqlBiAVmo7My/o/qYPpAm+1FNj4EACZ6u1j8vyoMk5u8IhawSRKE1MV2QHhKMxj0H2yS/OAvKyljB/NwVst79+7AewFYFl0nxzb+bAy7orUgdD0YRKliaqK7CnhqVTW2bpIkmaZl/W5qQPpycajfQHsA+DR6B7ZPX2CMu6q1IHQ9GISpSSqItsw1Gt9XUfW5uUD99S8rC9MHUhPk+lbAOwhy+GJw3kgnHM9Qxl3XeJYaACYRCmpqshkw8reAHZJNDUoZQtPzsuaTysNeaslge4f3s9JJ9M/SeNs2RGujPvZhF+bBoxJlDqhKrKnhWo5awFYHYD89xXG9HK/B3ATgJPysv72mF5j6Ml0z1CPNx9TwYZ/APgNgK+H/q3XK+PuGsPrEC0Ukyh1UlVkjwfwMgDPD8crXghgibBjdhQyVfwggB+Gr5/KkZW8rO8ZU+g0B2/18uE9fC6AFQGsDGAlAEsB+Gd4b+Rr9u5nqRrkw/t8TygI/+fw53vDf/8bgDuVcdLvkygpJlHqrKrIHheOUqwivR0BLBk+cEexWFgnuzXsuP1NXtbyQUwT4q1+RHgfdShu/zwASwO4f44kOrvk3r9DAq1DT09JoFX48/2hTdn9yjj570TJMYkSERFF6sKuSCIiol5iEiUiIorEJEpERBSJSZSIiCgSkygREVEkJlEiIqJITKJERESRmESJiIgiMYkSERFFYhIlIiKKxCRKREQUiUmUiIgoEpMoERFRJCZRIiKiSEyiREREkZhEiYiIIjGJEhERRWISJSIiisQkSkREFIlJlIiIKBKTKBERUSQmUSIiokhMokRERJGYRImIiCIxiRIREUViEiUiIorEJEpERBSJSZSIiCgSkygREVEkJlEiIiLE+X/TDlwCYJcbjgAAAABJRU5ErkJggg=="
                alt="لانچر مودینگ‌وی"
                class="animate-heartbeat h-14 w-14 select-none object-contain drop-shadow-[0_0_18px_rgba(249,115,22,.55)] sm:h-16 sm:w-16 md:h-20 md:w-20">
        </div>

        <div class="animate-fade-up delay-100">
            <h1 class="text-shine m-0 select-none text-xl font-black leading-tight [text-shadow:0_0_30px_rgba(0,0,0,.5)] sm:text-2xl md:text-3xl lg:text-4xl">
                لانچر اختصاصی مودینگ‌وی
            </h1>
        </div>

        <p class="animate-fade-up delay-200 m-0 select-none text-base font-extrabold text-brand-400 [text-shadow:0_0_20px_rgba(0,0,0,.85)] sm:text-lg md:text-xl">
            به زودی ...
        </p>

        <p class="animate-fade-up delay-200 m-0 max-w-xl select-none text-xs leading-7 text-slate-300 [text-shadow:0_0_20px_rgba(0,0,0,.9)] sm:text-sm md:text-base">
            تجربه‌ای جدید و آسان از نصب محصولات
        </p>

        <a href="https://moddingway.ir/"
           data-no-tubes-click
           class="cta-ring animate-fade-up delay-300 relative mt-4 inline-flex items-center justify-center gap-2 rounded-full bg-brand-500 px-7 py-3 text-sm font-extrabold text-white shadow-lg shadow-brand-500/30 transition hover:bg-brand-600 active:bg-brand-700 sm:px-8 sm:py-3.5 sm:text-base">
            بازگشت به سایت
            <span aria-hidden="true">←</span>
        </a>

        <p class="animate-fade-up delay-300 pointer-events-none m-0 mt-2 select-none text-[11px] text-slate-400/60 sm:text-xs">
            برای تغییر افکت کلیک کن
        </p>

        <div
            class="animate-fade-up delay-300 mt-6 grid w-full max-w-4xl grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">

            <div class="feature-card">
                <svg class="feature-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M3 12h18"/>
                    <path d="M12 3c2.5 2.5 3.5 6 3.5 9s-1 6.5-3.5 9c-2.5-2.5-3.5-6-3.5-9s1-6.5 3.5-9z"/>
                </svg>
                <span class="feature-card-label">کاملا فارسی</span>
            </div>

            <div class="feature-card">
                <svg class="feature-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 3l6.5 15.5 2-6.5L21 10 6 3z"/>
                    <path d="M14 14l6 6"/>
                </svg>
                <span class="feature-card-label">نصب با ۱ کلیک</span>
            </div>

            <div class="feature-card">
                <svg class="feature-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 0 1 15.5-6.3L21 8"/>
                    <path d="M21 3v5h-5"/>
                    <path d="M21 12a9 9 0 0 1-15.5 6.3L3 16"/>
                    <path d="M3 21v-5h5"/>
                </svg>
                <span class="feature-card-label">نصب اتوماتیک آپدیت‌ها</span>
            </div>

            <div class="feature-card">
                <svg class="feature-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M10.5 21a1.5 1.5 0 0 0 3 0"/>
                </svg>
                <span class="feature-card-label">سیستم نوتیفیکیشن</span>
            </div>

            <div class="feature-card">
                <svg class="feature-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M10 4h4a1 1 0 0 1 1 1v2.2a2 2 0 1 0 0 3.6V13a1 1 0 0 1-1 1h-2.2a2 2 0 1 1-3.6 0H6a1 1 0 0 1-1-1V9a2 2 0 1 0 0-3.6V5a1 1 0 0 1 1-1h2.2a2 2 0 1 1 3.6 0z"/>
                </svg>
                <span class="feature-card-label">سیستم مادمنیجر پچ‌ها</span>
            </div>

        </div>

    </main>
</div>

<script type="module">
    // Vanilla-JS port of the original TubesCursor React component: same
    // library, same colors, same behavior — just without the React wrapper.
    const canvas = document.getElementById('tubes-canvas');
    let cursorApp = null;

    const randomColors = (count) =>
        Array.from({length: count}, () =>
            '#' + Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0')
        );

    // A short delay lets the canvas receive its final layout size before the
    // library reads it, avoiding a "Computed radius is NaN" init race.
    const initTimer = setTimeout(() => {
        import('/js/tubes1.min.js')
            .then((module) => {
                const TubesCursor = module.default;
                if (!canvas) return;

                cursorApp = TubesCursor(canvas, {
                    tubes: {
                        colors: ['#5e72e4', '#8965e0', '#f5365c'],
                        lights: {
                            intensity: 200,
                            colors: ['#21d4fd', '#b721ff', '#f4d03f', '#11cdef']
                        }
                    }
                });
            })
            .catch((err) => console.error('Failed to load the tubes cursor animation:', err));
    }, 100);

    window.addEventListener('beforeunload', () => {
        clearTimeout(initTimer);
        if (cursorApp && typeof cursorApp.dispose === 'function') {
            cursorApp.dispose();
        }
    });

    document.addEventListener('click', (event) => {
        if (!cursorApp || event.target.closest('[data-no-tubes-click]')) return;

        cursorApp.tubes.setColors(randomColors(3));
        cursorApp.tubes.setLightsColors(randomColors(4));
    });
</script>

</body>
</html>
