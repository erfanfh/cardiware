@if($node['kind'] === 'union')
    <div class="space-y-3">
        <div class="flex flex-wrap gap-3">
            @foreach($node['variants'] as $variant => $child)
                <x-admin.form.button type="button" variant="secondary" size="sm" @click="{{ $model }} = clone({{ \Illuminate\Support\Js::from($child['default']) }})">
                    {{ $variant === 'object' ? 'متن دوزبانه' : 'متن ساده' }}
                </x-admin.form.button>
            @endforeach
        </div>
        @foreach($node['variants'] as $variant => $child)
            <template x-if="{{ $variant === 'object' ? 'typeof '.$model.' === \'object\' && '.$model.' !== null && !Array.isArray('.$model.')' : 'typeof '.$model.' === '.json_encode($variant) }}">
                <div>@include('admin.system.settings.partials.product-landings.node', ['node' => $child, 'model' => $model, 'depth' => $depth + 1])</div>
            </template>
        @endforeach
    </div>
@elseif($node['kind'] === 'object')
    <div class="space-y-4">
        @foreach($node['fields'] as $key => $child)
            @php($childModel = $model.'['.json_encode((string) $key).']')
            <div>
                @if($child['optional'])
                    <template x-if="!Object.prototype.hasOwnProperty.call({{ $model }}, @js((string) $key))">
                        <x-admin.form.button type="button" variant="secondary" size="sm"
                                             @click="{{ $childModel }} = clone({{ \Illuminate\Support\Js::from($child['default']) }})">افزودن {{ $child['label'] }}</x-admin.form.button>
                    </template>
                @endif
                <template x-if="Object.prototype.hasOwnProperty.call({{ $model }}, @js((string) $key))">
                    <div>
                        @if(in_array($child['kind'], ['object', 'list'], true))
                            <details class="rounded-2xl border border-slate-200 bg-white p-4">
                                <summary class="cursor-pointer text-sm font-bold text-slate-700">{{ $child['label'] }}</summary>
                                <div class="mt-4">
                                    @include('admin.system.settings.partials.product-landings.node', ['node' => $child, 'model' => $childModel, 'depth' => $depth + 1])
                                </div>
                            </details>
                        @else
                            @include('admin.system.settings.partials.product-landings.node', ['node' => $child, 'model' => $childModel, 'depth' => $depth + 1])
                        @endif
                        @if($child['optional'])
                            <button type="button" class="mt-2 text-xs text-rose-600" @click="delete {{ $childModel }}">حذف {{ $child['label'] }}</button>
                        @endif
                    </div>
                </template>
            </div>
        @endforeach
    </div>
@elseif($node['kind'] === 'list')
    @php($index = 'row'.$depth)
    <div class="space-y-4">
        <template x-for="(_, {{ $index }}) in {{ $model }}" :key="{{ $index }}">
            <details class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <summary class="cursor-pointer text-sm font-semibold text-slate-700">
                    <span x-text="{{ $index }} + 1"></span>
                    <span x-text="{{ $model }}[{{ $index }}]?.title?.fa || {{ $model }}[{{ $index }}]?.name?.fa || {{ $model }}[{{ $index }}]?.label?.fa || {{ $model }}[{{ $index }}]?.q?.fa || {{ $model }}[{{ $index }}]?.version || ''"></span>
                </summary>
                <div class="mt-4 space-y-4">
                    <div class="flex flex-wrap gap-3">
                        <x-admin.form.button type="button" variant="secondary" size="sm" @click="move({{ $model }}, {{ $index }}, -1)" :disabled="false" x-bind:disabled="{{ $index }} === 0">بالاتر</x-admin.form.button>
                        <x-admin.form.button type="button" variant="secondary" size="sm" @click="move({{ $model }}, {{ $index }}, 1)" x-bind:disabled="{{ $index }} === {{ $model }}.length - 1">پایین‌تر</x-admin.form.button>
                        <x-admin.form.button type="button" variant="secondary" size="sm" @click="if (confirm('این مورد حذف شود؟')) {{ $model }}.splice({{ $index }}, 1)">حذف مورد</x-admin.form.button>
                    </div>
                    @foreach($node['variants'] as $variant => $child)
                        <template x-if="{{ $variant === '_' ? 'true' : $model.'['.$index.'].type === '.json_encode($variant) }}">
                            <div>
                                @include('admin.system.settings.partials.product-landings.node', ['node' => $child, 'model' => $model.'['.$index.']', 'depth' => $depth + 1])
                            </div>
                        </template>
                    @endforeach
                </div>
            </details>
        </template>
        <p x-show="{{ $model }}.length === 0" class="text-sm text-slate-500">موردی ثبت نشده است.</p>
        <div class="flex flex-wrap gap-3">
            @foreach($node['variants'] as $variant => $child)
                <x-admin.form.button type="button" variant="secondary" size="sm"
                                     x-bind:disabled="{{ $model }}.length >= 200"
                                     @click="add({{ $model }}, {{ \Illuminate\Support\Js::from($child['default']) }})">افزودن {{ $child['label'] }}</x-admin.form.button>
            @endforeach
        </div>
    </div>
@else
    <x-admin.field :label="$node['label']">
        @if(isset($node['choices']))
            <x-admin.form.input x-model="{{ $model }}" readonly/>
        @elseif($node['kind'] === 'boolean')
            <input type="checkbox" x-model="{{ $model }}" class="rounded border-slate-300">
        @elseif($node['kind'] === 'number')
            <x-admin.form.input type="number" :min="$node['min']" :max="$node['max']" step="any" x-model.number="{{ $model }}"/>
        @elseif($node['multiline'])
            <x-admin.form.textarea x-model="{{ $model }}" :rows="3" dir="{{ ($node['key'] ?? '') === 'en' ? 'ltr' : 'auto' }}"/>
        @else
            <x-admin.form.input x-model="{{ $model }}" dir="auto"/>
        @endif
        @if($node['media'] ?? false)
            <div class="mt-3 space-y-3" x-id="['landing-media']"
                 @mfip-selected.window="if ($event.detail.itemKey === $id('landing-media')) {{ $model }} = $event.detail.mediaPath ?? ''">
                <img x-show="{{ $model }}" :src="mediaPreview({{ ($node['key'] ?? '') === 'logo' ? '('.$model.'?.includes(\'/\') ? '.$model.' : \'/images/landings/logos/emb/\' + '.$model.')' : $model }})" alt="" class="h-24 max-w-xs rounded-xl object-contain" loading="lazy">
                <x-admin.form.button type="button" variant="secondary" size="sm"
                                     @click="$dispatch('mfip-open-requested', {itemKey: $id('landing-media'), selectedMediaPath: {{ $model }}})">انتخاب از کتابخانه رسانه</x-admin.form.button>
            </div>
        @endif
    </x-admin.field>
@endif
