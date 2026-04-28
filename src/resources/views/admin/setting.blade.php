<x-admin-layout>
    <x-slot:title>Settings — HT ភ្នាក់ Admin</x-slot:title>
    <x-slot:pageTitle>Settings</x-slot:pageTitle>
    <x-slot:breadcrumb>Settings</x-slot:breadcrumb>

    @php $user = auth()->user(); $isAdmin = true; @endphp

    <div class="row justify-content-center">
        <div class="col-lg-7">

            {{-- Printer & Commission Settings --}}
            <div class="card card-outline" style="border-top:3px solid #DC143C;">
                <div class="card-header" style="background-color:#DC143C;">
                    <h3 class="card-title text-white font-weight-bold">
                        <i class="fas fa-print mr-1"></i> Printer &amp; Commission Settings
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('setting.update') }}"
                          x-data="{ commissionMode: '{{ old('commission_mode', $setting->commission_mode ?? 'default') }}' }">
                        @csrf

                        @include('setting.printer-section')

                        <div class="mt-4">
                            <button type="submit"
                                    class="btn btn-block font-weight-bold"
                                    style="background-color:#D4A017;color:#fff;">
                                <i class="fas fa-save mr-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Currency Manager (admin only) --}}
            @livewire('currency-manager')

            {{-- About --}}
            <div class="card card-outline" style="border-top:3px solid #6c757d;">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold text-secondary">
                        <i class="fas fa-info-circle mr-1"></i> About
                    </h3>
                </div>
                <div class="card-body">
                    @include('setting.about-section')
                </div>
            </div>

        </div>
    </div>

</x-admin-layout>
