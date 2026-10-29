<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Domain\Notification\Models\Popup;
use App\Http\Controllers\Controller;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PopupController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('popup.list');

        $filters = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'title' => ['nullable', 'string', 'max:180'],
            'target_type' => ['nullable', Rule::in(['all', 'home', 'post', 'page', 'product'])],
            'audience' => ['nullable', Rule::in(['all', 'guest', 'authenticated', 'vip'])],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
            'sort' => ['nullable', Rule::in(['id', 'title', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Popup::query()->with('image.primaryVariant')
            ->when($filters['id'] ?? null, fn ($query, $id) => $query->whereKey($id))
            ->when($filters['title'] ?? null, fn ($query, $title) => $query->where('title', 'like', '%'.$title.'%'))
            ->when($filters['target_type'] ?? null, fn ($query, $type) => $query->where('target_type', $type))
            ->when($filters['audience'] ?? null, fn ($query, $audience) => $query->where('audience', $audience));

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';
        $popups = $query->orderBy($sort, $direction)->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $allowedBulkActions = [];
        if (Gate::allows('popup.edit')) {
            $allowedBulkActions = ['activate', 'deactivate'];
        }
        if (Gate::allows('popup.delete')) {
            $allowedBulkActions[] = 'delete';
        }

        return view('admin.notification.popups.index', [
            'popups' => $popups,
            'hasActiveFilters' => collect(['id', 'title', 'target_type', 'audience', 'is_active'])->contains(fn ($key) => $request->filled($key)),
            'bulkActionOptions' => DefaultBulkActions::set()->only($allowedBulkActions)->toOptions(),
            'hasBulkActions' => count($allowedBulkActions) > 0,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('popup.create');

        return view('admin.notification.popups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('popup.create');
        Popup::create($this->validatedData($request));

        return to_route('admin.popups.index')->with('success', 'پاپ‌آپ ایجاد شد.');
    }

    public function edit(Popup $popup): View
    {
        Gate::authorize('popup.edit');

        return view('admin.notification.popups.edit', compact('popup'));
    }

    public function update(Request $request, Popup $popup): RedirectResponse
    {
        Gate::authorize('popup.edit');
        $popup->update($this->validatedData($request));

        return to_route('admin.popups.index')->with('success', 'پاپ‌آپ ذخیره شد.');
    }

    public function destroy(Popup $popup): RedirectResponse
    {
        Gate::authorize('popup.delete');
        $popup->delete();

        return to_route('admin.popups.index')->with('success', 'پاپ‌آپ حذف شد.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'body' => ['nullable', 'string', 'max:5000'],
            'display_mode' => ['sometimes', 'required', Rule::in(['standard', 'image_only'])],
            'buttons' => ['nullable', 'array', 'max:20'],
            'buttons.*' => ['required', 'array:text,url'],
            'buttons.*.text' => ['required', 'string', 'max:100'],
            'buttons.*.url' => ['required', 'string', 'max:2048', 'url:http,https'],
            'image.id' => ['required_if:display_mode,image_only', 'nullable', 'integer', Rule::exists('media_files', 'id')
                ->where('type', 'image')->where('visibility', 'public')->where('status', 'ready')],
            'target_type' => ['required', Rule::in(['all', 'home', 'post', 'page', 'product'])],
            'specific_target' => ['required', 'boolean'],
            'target_post_id' => [Rule::excludeIf($request->input('target_type') !== 'post' || !$request->boolean('specific_target')), 'nullable', 'integer', Rule::exists('posts', 'id')->whereNull('deleted_at')],
            'target_page_id' => [Rule::excludeIf($request->input('target_type') !== 'page' || !$request->boolean('specific_target')), 'nullable', 'integer', Rule::exists('pages', 'id')->whereNull('deleted_at')],
            'target_product_id' => [Rule::excludeIf($request->input('target_type') !== 'product' || !$request->boolean('specific_target')), 'nullable', 'integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'audience' => ['required', Rule::in(['all', 'guest', 'authenticated', 'vip'])],
            'delay_seconds' => ['required', 'integer', 'between:0,86400'],
            'close_minutes' => ['required', 'integer', 'between:1,10080'],
            'hide_days' => ['required', 'integer', 'between:1,365'],
            'is_active' => ['required', 'boolean'],
        ], [
            'image.id.required_if' => 'برای حالت فقط تصویر، یک تصویر انتخاب کنید.',
            'buttons.*.text.required' => 'متن دکمه را وارد کنید.',
            'buttons.*.url.required' => 'لینک دکمه را وارد کنید.',
            'buttons.*.url.url' => 'لینک دکمه باید یک آدرس کامل با http:// یا https:// باشد.',
        ]);

        $targetType = $data['target_type'];
        $targetId = $data['target_'.$targetType.'_id'] ?? null;
        if (in_array($targetType, ['post', 'page', 'product'], true) && $request->boolean('specific_target') && !$targetId) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'target_'.$targetType.'_id' => 'محتوای موردنظر را انتخاب کنید.',
            ]);
        }

        return [
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'display_mode' => $data['display_mode'] ?? 'standard',
            'buttons' => array_values($data['buttons'] ?? []),
            'image_id' => $data['image']['id'] ?? null,
            'target_type' => $targetType,
            'target_id' => $request->boolean('specific_target') ? $targetId : null,
            'audience' => $data['audience'],
            'delay_seconds' => $data['delay_seconds'],
            'close_minutes' => $data['close_minutes'],
            'hide_days' => $data['hide_days'],
            'is_active' => $data['is_active'],
        ];
    }
}
