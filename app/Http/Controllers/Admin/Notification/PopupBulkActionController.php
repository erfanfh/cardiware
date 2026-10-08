<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Domain\Notification\Models\Popup;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
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

        $count = DB::transaction(function () use ($action, $data): int {
            $count = 0;
            foreach (Popup::query()->whereIn('id', $data['ids'])->get() as $popup) {
                if ($action === 'delete') {
                    $popup->delete();
                    $count++;
                } elseif ((bool) $popup->is_active !== ($action === 'activate')) {
                    $popup->update(['is_active' => $action === 'activate']);
                    $count++;
                }
            }

            return $count;
        });

        return to_route('admin.popups.index')
            ->with('success', number_format($count).' پاپ‌آپ به‌روزرسانی شد.');
    }
}
