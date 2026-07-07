@extends('front.account.layout')

@section('page-content')
    @php
        $canCreateTicket = $canCreateTicket
            ?? app(\App\Application\Support\Actions\CanCreateTicketAction::class)->handle($user);
    @endphp

    <div
        x-data="{
            canCreateTicket: @js($canCreateTicket),

            openModal(name) {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: name }));
            },

            openCreateTicketModal() {
                if (this.canCreateTicket) {
                    this.openModal('create-ticket');
                    return;
                }

                this.openModal('ticket-access-denied');
            }
        }"
        x-init="
            @if($errors->createTicket->any())
                $nextTick(() => openModal('create-ticket'))
            @elseif(session('ticket_created'))
                $nextTick(() => openModal('ticket-created'))
            @elseif(session('ticket_access_denied'))
                $nextTick(() => openModal('ticket-access-denied'))
            @endif
        "
    >
        <section
            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20"
        >
            @include('front.account.pages.tickets.partials.header')

            <livewire:front.account.my-tickets-section/>
        </section>

        @include('front.account.pages.tickets.modals.create')

        @include('front.account.pages.tickets.modals.access-denied')

        @include('front.account.pages.tickets.modals.created')
    </div>
@endsection
