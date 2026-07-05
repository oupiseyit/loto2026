<x-guest-layout>
    <x-slot:title>Login — HT ភ្នាក់</x-slot:title>

    <div class="px-6 py-8">
        <h2 class="text-xl font-bold text-center mb-4" style="color: #DC143C;">Sign In</h2>

        

        @if (session('error'))
            <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-medium text-white" style="background-color:#DC143C;">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if ($status)
            <div class="mb-4 text-sm font-medium text-green-600 text-center">{{ $status }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                       autocomplete="username" placeholder="{{ __('username') }}"
                       class="w-full border-2 rounded-lg px-3 py-2.5 text-sm outline-none transition-colors
                              focus:border-[#D4A017] @error('username') border-red-400 @else border-gray-200 @enderror">
                @error('username')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       placeholder="{{ __('password') }}"
                       class="w-full border-2 rounded-lg px-3 py-2.5 text-sm outline-none transition-colors
                              focus:border-[#D4A017] @error('password') border-red-400 @else border-gray-200 @enderror">
                @error('password')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 accent-[#D4A017]">
                    Remember me
                </label>
            </div>

            <button type="submit"
                    class="w-full py-3 rounded-xl text-white font-bold text-sm mt-2"
                    style="background-color: #D4A017;">
                Sign In
            </button>
        </form>

        <div class="flex gap-2 mt-3" x-data>
            @foreach ([['admin','admin','admin123'],['master','master1','master123'],['staff','staff1','staff123']] as [$role,$user,$pass])
            <button type="button"
                    @click="document.getElementById('username').value='{{ $user }}'; document.getElementById('password').value='{{ $pass }}'; document.querySelector('form').submit();"
                    class="flex-1 py-1.5 text-xs font-bold rounded-lg border transition-colors"
                    style="background-color:#DC143C15; border-color:#DC143C60; color:#DC143C;">
                {{ $role }}
            </button>
            @endforeach
        </div>
    </div>
</x-guest-layout>
