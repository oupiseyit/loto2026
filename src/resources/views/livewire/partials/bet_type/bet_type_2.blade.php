{{--
    Type 2 — Head Bet (ជំហានៗ)
    Run of 10 consecutive numbers sharing the same units digit.
    start_number + end_number (editable; units digit locked to match start).
    Alpine vars used: startNumber, endNumber, active, betMode
--}}
<div x-show="betMode === 2" class="flex gap-1">
    <input type="text" inputmode="numeric"
           x-ref="startNumberInput"
           x-model="startNumber"
           @focus="active='startNumber'"
           @input="
               startNumber = startNumber.replace(/[^0-9]/g, '').slice(0,2);
           "
           maxlength="2"
           placeholder="{{ __('start_number') }}"
           class="w-1/2 text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           :style="active==='startNumber' ? 'border-color:#DC143C;color:#DC143C;' : 'border-color:#D4A017;color:#DC143C;'" />

    <input type="text" inputmode="numeric"
           x-model="endNumber"
           @focus="active='endNumber'"
           @input="
               endNumber = endNumber.replace(/[^0-9]/g, '').slice(0,2);
               if (startNumber !== '' && endNumber.length === 2) {
                   let units = parseInt(startNumber) % 10;
                   let corrected = Math.floor(parseInt(endNumber) / 10) * 10 + units;
                   endNumber = corrected.toString().padStart(2, '0');
               }
           "
           maxlength="2"
           placeholder="{{ __('end_number') }}"
           class="w-1/2 text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           :style="active==='endNumber' ? 'border-color:#DC143C;color:#DC143C;' : 'border-color:#D4A017;color:#DC143C;'" />
</div>
