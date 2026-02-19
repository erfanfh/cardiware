<?php

use Livewire\Component;

new class extends Component
{
    public int $postId;

    public function sendId()
    {
        $this->dispatch('quick-edit-post', $this->postId);
    }
};
?>

<button type="button" wire:click="sendId"
   class="inline-flex items-center cursor-pointer whitespace-nowrap gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
    <svg width="14" height="14" viewBox="0 0 24 24">
        <path d="M5 7h14M5 12h14M5 17h14"
              stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    </svg>
    ویرایش سریع
</button>
