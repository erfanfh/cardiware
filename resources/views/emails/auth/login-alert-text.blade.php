Hello {{ $login->name }},

A successful sign-in to your account at Cardiware occurred.
Time (Tehran): {{ $login->time() }}
Address IP: {{ $login->ip }}
Sign-in location: {{ $login->sourceLabel() }}
Browser / App: {{ $login->userAgent }}

If you did not sign in, change your account password and contact support Cardiware for assistance.

This email was sent automatically; please do not reply.
