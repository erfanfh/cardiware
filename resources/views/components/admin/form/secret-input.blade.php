@props([
    'name' => null,
    'type' => 'text',
    'value' => '',
    'toggle' => 'password',
])

<div class="relative">
    <input
        type="{{ $type }}"
        value="{{ $value }}"
        @if($name) name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'w-full h-10 rounded-2xl border border-slate-200 bg-white pr-3 pl-10 text-sm text-slate-800 placeholder:text-slate-400 outline-none transition focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300'
        ]) }}
    />

    <span class="absolute left-3 top-2.5 cursor-pointer" onclick="togglePasswordVisibility('{{ $toggle }}', 'togglePassword')">
        @include('icons.view')
    </span>
</div>

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
