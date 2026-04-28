@php
    $user            = auth()->user();
    $isAdmin         = $user->role === 'admin';
    $isMasterOrAdmin = in_array($user->role, ['admin', 'master']);
@endphp

<div class="max-w-2xl mx-auto px-4 py-6 space-y-6">

    @include('livewire.account.sales-card')

    @includeWhen($isAdmin,              'livewire.account.master-accounts')
    @includeWhen($user->role === 'master', 'livewire.account.staff-accounts')

    @include('livewire.account.change-password')
    @include('livewire.account.language-switcher')

    @includeWhen($isMasterOrAdmin, 'livewire.account.modal-create')
    @includeWhen($isMasterOrAdmin, 'livewire.account.modal-edit')

</div>
