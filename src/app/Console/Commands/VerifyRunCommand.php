<?php

namespace App\Console\Commands;

use App\Verify\VerifyRunner;
use Illuminate\Console\Command;

class VerifyRunCommand extends Command
{
    protected $signature = 'verify:run {--endpoint= : Filter to one endpoint} {--fixture= : Filter to one fixture}';
    protected $description = 'Run the verify matrix against the running app';

    public function handle(): int
    {
        $results  = (new VerifyRunner())->run($this->option('endpoint'), $this->option('fixture'));
        $maxLabel = collect($results)->map(fn($r) => strlen("{$r['endpoint']} / {$r['fixture']}"))->max();

        foreach ($results as $r) {
            $label  = "{$r['endpoint']} / {$r['fixture']}";
            $pad    = str_repeat('.', max(3, $maxLabel - strlen($label) + 3));
            $probe  = $r['type'] === 'probe' ? ' [probe]' : '';
            $color  = match ($r['verdict']) { 'PASS' => 'green', 'FAIL' => 'red', default => 'yellow' };
            $this->line(" <fg=$color>{$r['verdict']}</> {$label} {$pad}{$probe}");

            if ($r['verdict'] !== 'PASS') {
                foreach ($r['checks'] as $c) {
                    if ($c['result'] === 'fail') {
                        $this->line("       <fg=red>✗ {$c['name']}: {$c['detail']}</>");
                    }
                }
                if (!empty($r['body'])) {
                    $decoded = json_decode($r['body'], true);
                    $this->line('       body: ' . json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }
            }
        }

        $counts  = collect($results)->countBy('verdict');
        $fails   = $counts->get('FAIL', 0);
        $blocked = $counts->get('BLOCKED', 0);
        $pass    = $counts->get('PASS', 0);

        $this->newLine();
        $this->line(" <fg=green>$pass PASS</>, <fg=red>$fails FAIL</>, <fg=yellow>$blocked BLOCKED</>");

        return ($fails + $blocked) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
