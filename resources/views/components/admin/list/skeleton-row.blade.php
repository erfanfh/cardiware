@props([
    'columns' => 7,
])

<tr class="animate-pulse bg-white">
    @for($i = 0; $i < $columns; $i++)
        <td class="px-4 py-4">
            <div class="h-4 rounded bg-slate-100"></div>
        </td>
    @endfor
</tr>
