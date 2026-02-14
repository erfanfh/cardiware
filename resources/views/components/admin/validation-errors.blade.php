@if ($errors->any())
    <div class="rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 p-4 mb-6">
        <div class="text-sm font-semibold text-orange-700 dark:text-orange-400 mb-2">
            لطفاً خطاهای زیر را بررسی کنید:
        </div>
        <ul class="list-disc pr-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li class="text-sm text-orange-600 dark:text-orange-300">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
