<x-admin-layout>
    <x-slot:title>Bet Categories — HT ភ្នាក់ Admin</x-slot:title>
    <x-slot:pageTitle>Bet Categories</x-slot:pageTitle>
    <x-slot:breadcrumb>Bet Categories</x-slot:breadcrumb>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-outline" style="border-top:3px solid #DC143C;">
                <div class="card-header" style="background-color:#DC143C;">
                    <h3 class="card-title text-white font-weight-bold">
                        <i class="fas fa-tags mr-1"></i> Bet Categories
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('setting') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Settings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @livewire('bet-category-manager')
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
