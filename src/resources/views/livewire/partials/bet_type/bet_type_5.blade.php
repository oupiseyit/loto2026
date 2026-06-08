{{--
    Type 5 — Multiple Bet (X)
    All unique permutations of the 3 input digits.
    Badge shows the count: 112 → X 3 | 123 → X 6
    Alpine vars used: startNumber, active, betMode
--}}
<div x-show="betMode === 5" class="relative">
    <input type="text" inputmode="numeric"
           x-ref="startNumberInput"
           x-model="startNumber"
           @focus="active='startNumber'"
           @input="startNumber = startNumber.replace(/[^0-9]/g, '').slice(0,3)"
           maxlength="3"
           placeholder="{{ __('number') }}"
           class="w-full text-center font-bold rounded border-2 outline-none {{ $compact ? 'py-1 text-sm' : 'py-3 text-3xl' }}"
           :style="active==='startNumber' ? 'border-color:#9B2335;color:#9B2335;' : 'border-color:#C8BBA8;color:#9B2335;'" />
    <span x-show="startNumber.length === 3"
          class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold px-1.5 py-0.5 rounded"
          style="background-color:#2A5F47;color:#fff;"
          x-text="'X' + (() => {
            const freq = {};
            for (const c of startNumber) { freq[c] = (freq[c] || 0) + 1; }
            const denom = Object.values(freq).reduce((a, f) => a * [1,1,2,6][f], 1);
            return String(6 / denom);
          })()"></span>
</div>
