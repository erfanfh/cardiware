<?php

namespace App\Livewire\Admin\Icon;

use App\Domain\Icon\Models\Icon;
use App\Domain\Icon\Models\IconSet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class IconPicker extends Component
{
    use WithPagination;

    public ?string $itemKey = null;
    public bool $opened = false;

    public string $search = '';
    public string $activeSet = '';
    public int $perPage = 72;

    public ?int $pendingIconId = null;
    public ?string $selectedIconName = null;

    public array $selectedInfo = [
        'id' => null,
        'name' => null,
        'full_name' => null,
        'set_key' => null,
        'set_label' => null,
        'svg' => null,
    ];

    #[On('mwip-open-singleton')]
    public function openSingleton(string $itemKey, ?string $selectedIconName = null): void
    {
        $this->itemKey = $itemKey;
        $this->selectedIconName = filled($selectedIconName) ? trim($selectedIconName) : null;
        $this->search = '';
        $this->resetPage();

        $selectedIcon = $this->selectedIconName
            ? Icon::query()->with('set')->where('full_name', $this->selectedIconName)->first()
            : null;

        $this->pendingIconId = $selectedIcon?->id;
        $this->selectedInfo = $this->mapIcon($selectedIcon);
        $this->opened = true;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActiveSet(): void
    {
        $this->resetPage();
    }

    public function selectSet(string $setKey = ''): void
    {
        $this->activeSet = trim($setKey);
        $this->resetPage();
    }

    public function previewSelection(int $iconId): void
    {
        $icon = Icon::query()
            ->with('set')
            ->find($iconId);

        if (!$icon) {
            return;
        }

        $this->pendingIconId = $icon->id;
        $this->selectedInfo = $this->mapIcon($icon);
    }

    public function confirmSelection(): void
    {
        if (!$this->pendingIconId) {
            return;
        }

        $icon = Icon::query()
            ->with('set')
            ->find($this->pendingIconId);

        if (!$icon) {
            return;
        }

        $this->selectedIconName = $icon->full_name;
        $this->selectedInfo = $this->mapIcon($icon);

        $this->dispatch(
            'mwip-selected',
            itemKey: $this->itemKey,
            iconId: $icon->id,
            iconName: $icon->full_name,
            iconTitle: $icon->full_name,
            iconSvg: $icon->svg,
            iconSet: $icon->set?->key,
        );

        $this->opened = false;
        $this->dispatch('mwip-close-modal');
        $this->skipRender();
    }

    public function clearSelection(): void
    {
        $this->pendingIconId = null;
        $this->selectedIconName = null;
        $this->selectedInfo = $this->emptySelectedInfo();

        $this->dispatch(
            'mwip-cleared',
            itemKey: $this->itemKey,
        );

        $this->opened = false;
        $this->dispatch('mwip-close-modal');
        $this->skipRender();
    }

    public function closeModal(): void
    {
        $this->opened = false;
        $this->dispatch('mwip-close-modal');
    }

    public function getLibraryProperty(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return Icon::query()
            ->with('set:id,key,label')
            ->fromSet($this->activeSet)
            ->search($search)
            ->when(
                $search !== '',
                function (Builder $query) use ($search) {
                    $query->orderByRaw(
                        'CASE
                            WHEN full_name = ? THEN 0
                            WHEN name = ? THEN 1
                            WHEN name LIKE ? THEN 2
                            ELSE 3
                        END',
                        [$search, $search, $search . '%'],
                    );
                },
                fn(Builder $query) => $query->orderBy('name'),
            )
            ->orderBy('full_name')
            ->paginate($this->perPage);
    }

    public function getSetsProperty(): Collection
    {
        return IconSet::query()
            ->where('icons_count', '>', 0)
            ->orderBy('label')
            ->get(['id', 'key', 'label', 'icons_count']);
    }

    private function mapIcon(?Icon $icon): array
    {
        if (!$icon) {
            return $this->emptySelectedInfo();
        }

        return [
            'id' => $icon->id,
            'name' => $icon->name,
            'full_name' => $icon->full_name,
            'set_key' => $icon->set?->key,
            'set_label' => $icon->set?->label,
            'svg' => $icon->svg,
        ];
    }

    private function emptySelectedInfo(): array
    {
        return [
            'id' => null,
            'name' => null,
            'full_name' => null,
            'set_key' => null,
            'set_label' => null,
            'svg' => null,
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.icon.icon-picker', [
            'icons' => $this->library,
            'sets' => $this->sets,
        ]);
    }
}
