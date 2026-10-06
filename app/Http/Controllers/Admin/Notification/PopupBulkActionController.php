<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Domain\Notification\Models\Popup;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PopupBulkActionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bulk_action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists('popups', 'id')],
        ]);

        $action = $data['bulk_action'];
        Gate::authorize($action === 'delete' ? 'popup.delete' : 'popup.edit');

        $query = Popup::query()->whereIn('id', $data['ids']);
        $count = $action === 'delete'
            ? $query->delete()
            : $query->update(['is_active' => $action === 'activate', 'updated_at' => now()]);

        return to_route('admin.popups.index')
            ->with('success', number_format($count).' پاپ‌آپ به‌روزرسانی شد.');
    }
}
