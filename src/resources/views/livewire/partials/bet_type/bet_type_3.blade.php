{{--
    Type 3 — Tail Bet (ចំនួន)
    Block of 100 consecutive numbers (e.g. 000–099, 100–199).
    end_number is auto-calculated as floor(start/100)*100 + 99 (readonly).
    Alpine vars used: startNumber, active, betMode
--}}
<div x-show="betMode === 3" class="flex gap-1">
    <input type="text" inputmode="numeric"
           x-ref="startNumberInput"
           x-model="startNumber"
           @focus="active='startNumber'"
           @input="startNumber = startNumber.replace(/[^0-9]/g, '').slice(0,3)"
           maxlength="3"
           placeholder="{{ __('start_number') }}"
           class="w-1/2 text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           :style="active==='startNumber' ? 'border-color:#DC143C;color:#DC143C;' : 'border-color:#D4A017;color:#DC143C;'" />

    <input type="text" readonly
           :value="startNumber === '' ? '' : (Math.floor(parseInt(startNumber) / 100) * 100 + 99).toString().padStart(3, '0')"
           placeholder="{{ __('end_number') }}"
           class="w-1/2 text-center font-bold rounded border-2 outline-none bg-gray-50 {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           style="border-color:#D4A017;color:#9ca3af;cursor:default;" />
</div>
