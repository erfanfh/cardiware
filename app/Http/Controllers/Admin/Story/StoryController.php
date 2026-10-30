<?php

namespace App\Http\Controllers\Admin\Story;

use App\Application\Story\Actions\SaveStoryAction;
use App\Domain\Story\Models\Story;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Story\SaveStoryRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoryController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('story.list');
        $filters = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'title' => ['nullable', 'string', 'max:180'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
            'sort' => ['nullable', Rule::in(['id', 'title', 'sort_order', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $query = Story::query()->with(['image.primaryVariant', 'thumbnail.primaryVariant'])
            ->when($filters['id'] ?? null, fn ($query, $id) => $query->whereKey($id))
            ->when($filters['title'] ?? null, fn ($query, $title) => $query->where('title', 'like', '%'.$title.'%'));
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }
        $sort = $filters['sort'] ?? 'sort_order';
        $query->orderBy($sort, $filters['direction'] ?? 'asc');
        if ($sort !== 'id') {
            $query->orderBy('id');
        }

        $allowed = Gate::allows('story.edit') ? ['activate', 'deactivate'] : [];
        if (Gate::allows('story.delete')) {
            $allowed[] = 'delete';
        }

        return view('admin.story.stories.index', [
            'stories' => $query->paginate(20)->withQueryString(),
            'hasActiveFilters' => collect(['id', 'title', 'is_active'])->contains(fn ($key) => $request->filled($key)),
            'bulkActionOptions' => DefaultBulkActions::set()->only($allowed)->toOptions(),
            'hasBulkActions' => $allowed !== [],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('story.create');

        return view('admin.story.stories.create', ['story' => null]);
    }

    public function store(SaveStoryRequest $request, SaveStoryAction $action): RedirectResponse
    {
        $story = $action->execute($request->validated());

        $redirect = Gate::allows('story.edit') ? to_route('admin.stories.edit', $story)
            : to_route(Gate::allows('story.list') ? 'admin.stories.index' : 'admin.stories.create');

        return $redirect->with('success', 'استوری ایجاد شد.');
    }

    public function edit(Story $story): View
    {
        Gate::authorize('story.edit');
        $story->load(['image.primaryVariant', 'thumbnail.primaryVariant']);

        return view('admin.story.stories.edit', compact('story'));
    }

    public function update(SaveStoryRequest $request, Story $story, SaveStoryAction $action): RedirectResponse
    {
        $action->execute($request->validated(), $story);

        return to_route('admin.stories.edit', $story)->with('success', 'استوری ذخیره شد.');
    }

    public function destroy(Story $story): RedirectResponse
    {
        Gate::authorize('story.delete');
        $story->delete();

        return to_route('admin.stories.index')->with('success', 'استوری حذف شد.');
    }
}
