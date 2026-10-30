<?php

namespace App\Http\Controllers\Admin\Story;

use App\Domain\Story\Models\Story;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoryBulkActionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        Gate::authorize($request->input('bulk_action') === 'delete' ? 'story.delete' : 'story.edit');
        $data = $request->validate([
            'bulk_action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists('stories', 'id')],
        ]);

        $count = DB::transaction(function () use ($data): int {
            $stories = Story::query()->whereKey($data['ids'])->get();
            foreach ($stories as $story) {
                if ($data['bulk_action'] === 'delete') {
                    $story->delete();
                } else {
                    $story->update(['is_active' => $data['bulk_action'] === 'activate']);
                }
            }

            return $stories->count();
        });

        return to_route('admin.stories.index')->with('success', number_format($count).' استوری به‌روزرسانی شد.');
    }
}
