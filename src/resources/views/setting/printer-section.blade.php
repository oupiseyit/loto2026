<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center gap-3 mb-4">
        <h2 class="text-base font-bold" style="color:#DC143C;">Printer</h2>
        <div class="flex-1 h-px" style="background-color:#DC143C33;"></div>
    </div>

    {{-- Bluetooth --}}
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <span class="text-sm font-medium text-gray-700">Bluetooth</span>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="bluetooth_enabled" value="1"
                   {{ old('bluetooth_enabled', $setting->bluetooth_enabled ?? false) ? 'checked' : '' }}
                   class="sr-only peer">
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                peer-checked:after:translate-x-full peer-checked:after:border-white
                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                after:bg-white after:border-gray-300 after:border after:rounded-full
                after:h-5 after:w-5 after:transition-all peer-checked:bg-[#DC143C]"></div>
        </label>
    </div>

    {{-- Printer size --}}
    <div class="flex items-center justify-between py-3 border-b border-gray-100">
        <span class="text-sm font-medium text-gray-700">Selected Device</span>
        <div class="flex gap-4">
            @foreach ([['58mm','58MM (printer)'],['80mm','80MM (printer)']] as [$val, $lbl])
                <label class="flex items-center gap-1.5 cursor-pointer text-sm">
                    <input type="radio" name="printer_size" value="{{ $val }}"
                           {{ old('printer_size', $setting->printer_size ?? '58mm') === $val ? 'checked' : '' }}
                           class="accent-[#DC143C]">
                    <span class="text-gray-700">{{ $lbl }}</span>
                </label>
            @endforeach
        </div>
    </div>
    @error('printer_size') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

    {{-- Logo mode --}}
    <div class="flex items-center justify-between py-3 {{ $isAdmin ? 'border-b border-gray-100' : '' }}">
        <span class="text-sm font-medium text-gray-700">Logo</span>
        <div class="flex gap-4">
            @foreach ([['logo','Print with Logo'],['text','Print with Text']] as [$val, $lbl])
                <label class="flex items-center gap-1.5 cursor-pointer text-sm">
                    <input type="radio" name="logo_mode" value="{{ $val }}"
                           {{ old('logo_mode', $setting->logo_mode ?? 'logo') === $val ? 'checked' : '' }}
                           class="accent-[#DC143C]">
                    <span class="text-gray-700">{{ $lbl }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Commission (admin only) --}}
    @if ($isAdmin)
        <div class="flex items-center justify-between py-3 border-b border-gray-100">
            <span class="text-sm font-medium text-gray-700">Commission</span>
            <div class="flex gap-4">
                @foreach ([['default','Default'],['custom','Custom']] as [$val, $lbl])
                    <label class="flex items-center gap-1.5 cursor-pointer text-sm">
                        <input type="radio" name="commission_mode" value="{{ $val }}"
                               x-model="commissionMode"
                               {{ old('commission_mode', $setting->commission_mode ?? 'default') === $val ? 'checked' : '' }}
                               class="accent-[#DC143C]">
                        <span class="text-gray-700">{{ $lbl }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div x-show="commissionMode === 'custom'" class="pt-2 pb-1 flex items-center gap-3">
            <label class="text-sm text-gray-600 w-32">Commission %</label>
            <div class="flex items-center gap-1">
                <input type="number" name="commission_value" min="0" max="100" step="0.01"
                       value="{{ old('commission_value', $setting->commission_value ?? '') }}"
                       placeholder="e.g. 5.00"
                       class="w-28 border-2 rounded-lg px-3 py-1.5 text-sm outline-none transition-colors focus:border-[#D4A017] @error('commission_value') border-red-400 @else border-gray-200 @enderror">
                <span class="text-sm text-gray-500">%</span>
            </div>
        </div>
        @error('commission_value') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
    @endif
</div>
