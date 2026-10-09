<?php

namespace App\Http\Controllers\Admin\Career;

use App\Domain\Career\Models\CareerPosition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Career\SaveCareerPositionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CareerPositionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('career-position.list');

        $filters = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,active,closed'],
            'department' => ['nullable', 'string', 'max:100'],
        ]);

        $positions = CareerPosition::query()->withCount('applications')
            ->when($filters['id'] ?? null, fn ($query, $id) => $query->whereKey($id))
            ->when($filters['title'] ?? null, fn ($query, $title) => $query->where('title', 'like', '%'.$title.'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['department'] ?? null, fn ($query, $department) => $query->where('department', 'like', '%'.$department.'%'))
            ->latest('published_at')->latest('id')->paginate(20)->withQueryString();

        return view('admin.career.positions.index', compact('positions', 'filters'));
    }

    public function create(): View
    {
        Gate::authorize('career-position.create');

        return view('admin.career.positions.create');
    }

    public function checkSlug(Request $request): JsonResponse
    {
        abort_unless(Gate::any(['career-position.create', 'career-position.edit']), 403);

        $data = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'regex:/^[\pL\pN_-]+$/u'],
            'ignore' => ['nullable', 'integer', 'min:1'],
        ]);

        $slug = $data['slug'];
        $ignoreId = $data['ignore'] ?? null;
        $exists = fn (string $value): bool => CareerPosition::withTrashed()
            ->where('slug', $value)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if (! $exists($slug)) {
            return response()->json(['available' => true, 'message' => '', 'suggested_slug' => null]);
        }

        $suffix = 2;
        do {
            $ending = '-'.$suffix++;
            $suggestion = mb_substr($slug, 0, 255 - mb_strlen($ending)).$ending;
        } while ($exists($suggestion));

        return response()->json([
            'available' => false,
            'message' => 'نامک تکراری است',
            'suggested_slug' => $suggestion,
        ]);
    }

    public function store(SaveCareerPositionRequest $request): RedirectResponse
    {
        Gate::authorize('career-position.create');

        $position = CareerPosition::create($this->payload($request));

        return redirect()->route('admin.career-positions.edit', $position)->with('success', 'موقعیت شغلی ایجاد شد.');
    }

    public function edit(CareerPosition $careerPosition): View
    {
        Gate::authorize('career-position.edit');

        return view('admin.career.positions.edit', ['position' => $careerPosition]);
    }

    public function update(SaveCareerPositionRequest $request, CareerPosition $careerPosition): RedirectResponse
    {
        Gate::authorize('career-position.edit');

        $careerPosition->update($this->payload($request, $careerPosition));

        return redirect()->route('admin.career-positions.edit', $careerPosition)->with('success', 'موقعیت شغلی ذخیره شد.');
    }

    public function destroy(CareerPosition $careerPosition): RedirectResponse
    {
        Gate::authorize('career-position.delete');

        $careerPosition->delete();

        return redirect()->route('admin.career-positions.index')->with('success', 'موقعیت شغلی حذف شد.');
    }

    private function payload(SaveCareerPositionRequest $request, ?CareerPosition $position = null): array
    {
        $data = $request->validated();
        $requirements = preg_split('/\R/u', $data['requirements_text'] ?? '') ?: [];
        $data['requirements'] = array_values(array_filter(array_map('trim', $requirements), fn ($item) => $item !== ''));
        unset($data['requirements_text']);

        if (blank($data['slug'] ?? null)) {
            $base = $this->slugFromTitle($data['title']) ?: 'position';
            $slug = $base;
            $suffix = 2;
            while (CareerPosition::withTrashed()->where('slug', $slug)->when($position, fn ($query) => $query->where('id', '!=', $position->id))->exists()) {
                $slug = $base.'-'.$suffix++;
            }
            $data['slug'] = $slug;
        }

        return $data;
    }

    private function slugFromTitle(string $title): string
    {
        $slug = Str::lower(trim($title));
        $slug = strtr($slug, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            'ي' => 'ی', 'ك' => 'ک',
        ]);
        $slug = preg_replace('/[\s_]+/u', '-', $slug) ?? $slug;
        $slug = preg_replace('/[^a-z0-9\x{0600}-\x{06FF}-]+/u', '', $slug) ?? $slug;
        $slug = preg_replace('/-+/u', '-', $slug) ?? $slug;

        return trim($slug, '-');
    }
}
