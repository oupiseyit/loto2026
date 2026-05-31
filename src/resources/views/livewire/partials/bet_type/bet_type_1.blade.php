{{--
    Type 1 — Normal Bet (មូលគ្រាល)
    Single specific number, 2 or 3 digits.
    Alpine vars used: number, active
--}}
<div x-show="betMode === 1">
    <input type="text" inputmode="numeric"
           x-ref="numberInput"
           x-model="number"
           @focus="active='number'"
           @input="number = number.replace(/[^0-9]/g, '').slice(0,3)"
           placeholder="{{ __('number') }}"
           class="w-full text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           :style="active==='number' ? 'border-color:#DC143C;color:#DC143C;' : 'border-color:#D4A017;color:#DC143C;'" />
</div>
