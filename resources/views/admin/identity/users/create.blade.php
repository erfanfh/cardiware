@extends('admin.layout')

@section('title', 'ایجاد کاربر جدید')

@section('content')
    <x-admin.form action="{{ route('admin.users.store') }}" method="post">
        @include('admin.identity.users.partials.form-fields', [
            'user' => null,
        ])

        <x-admin.save-button label="ایجاد کاربر"/>
    </x-admin.form>
@endsection

@push('scripts')
    <script>
        /**
         * Toggle password visibility
         * @param {string} inputId - ID of the password input field
         * @param {string} buttonId - ID of the toggle button
         */
        function togglePasswordVisibility(inputId, buttonId) {
            const input = document.getElementById(inputId);

            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        /**
         * Generate secure random password
         */
        function generatePassword() {
            const length = 12;
            const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
            let password = '';

            password += charset.charAt(Math.floor(Math.random() * 26));
            password += charset.charAt(Math.floor(Math.random() * 26) + 26);
            password += charset.charAt(Math.floor(Math.random() * 10) + 52);
            password += charset.charAt(Math.floor(Math.random() * 8) + 62);

            for (let i = 4; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * charset.length);
                password += charset[randomIndex];
            }

            password = password.split('').sort(() => 0.5 - Math.random()).join('');

            document.getElementById('password').value = password;
            document.getElementById('password_confirmation').value = password;
        }
    </script>
@endpush
