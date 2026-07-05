{{--
    Type 4 — Tail Bet (>)
    Last digit swept from input value up to 9; all preceding digits fixed.
    2-digit XY → end X9 | 3-digit XYZ → end XY9
    Alpine vars used: startNumber, active, betMode
--}}
<div x-show="betMode === 4" class="flex gap-1">
    <input type="text" inputmode="numeric"
           x-ref="startNumberInput"
           x-model="startNumber"
           @focus="active='startNumber'"
           @input="startNumber = startNumber.replace(/[^0-9]/g, '').slice(0,3)"
           maxlength="3"
           placeholder="{{ __('start_number') }}"
           class="w-1/2 text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           :style="active==='startNumber' ? 'border-color:#9B2335;color:#9B2335;' : 'border-color:#C8BBA8;color:#9B2335;'" />

    <input type="text" inputmode="numeric"
           x-model="endNumber"
           @focus="active='endNumber'"
           @input="endNumber = endNumber.replace(/[^0-9]/g, '').slice(0, startNumber.length || 3)"
           placeholder="{{ __('end_number') }}"
           class="w-1/2 text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           :style="active==='endNumber' ? 'border-color:#9B2335;color:#9B2335;' : 'border-color:#C8BBA8;color:#9B2335;'" />
</div>
