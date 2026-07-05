<?php

namespace App\Verify;

use App\Models\BetTimeSetting;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VerifyRunner
{
    private const PASSWORD = 'Vfy@99Verify!';

    private array $tokens = [];

    /** role/label => user id, resolved after seeding — used by the scope verifier. */
    private array $userIds = [];

    /** Active BetTimeSetting rows loaded before the transaction (seedSettings disables them inside). */
    private array $betSessions = [];

    /** role/label => User model, kept for web guard pre-auth. */
    private array $users = [];

    /** @return list<array> */
    public function run(?string $endpoint = null, ?string $fixture = null): array
    {
        $this->loadBetSessions();
        $results = [];

        DB::beginTransaction();
        try {
            $users = $this->seedUsers();
            $this->users   = $users;
            $this->userIds = array_map(fn ($u) => $u->id, $users);
            $this->tokens = [
                'admin'  => $users['admin']->createToken('vfy')->plainTextToken,
                'master' => $users['master']->createToken('vfy')->plainTextToken,
                'staff'  => $users['staff']->createToken('vfy')->plainTextToken,
            ];
            $this->seedTickets($users);
            $this->seedSettings();

            foreach ($this->fixtures() as $fix) {
                if ($endpoint && $fix['endpoint'] !== $endpoint) { continue; }
                if ($fixture  && $fix['fixture']  !== $fixture)  { continue; }
                $results[] = $this->runFixture($fix);
            }
        } finally {
            DB::rollBack();
            BetTimeSetting::bustCache();
        }

        return $results;
    }

    /** Return fixture metadata without executing — for manifest. */
    public function manifest(): array
    {
        $this->loadBetSessions();

        return array_map(fn($f) => [
            'endpoint'         => $f['endpoint'],
            'fixture'          => $f['fixture'],
            'type'             => $f['type'] ?? 'happy',
            'method'           => $f['method'],
            'path'             => $f['path'],
            'as'               => $f['as'] ?? $f['web_as'] ?? null,
            'verifiers'        => $f['verifiers'],
            'livewire_updates' => $f['livewire_updates'] ?? null,
            'livewire_calls'   => $f['livewire_calls'] ?? null,
        ], $this->fixtures());
    }

    private function seedUsers(): array
    {
        $admin  = User::create(['name' => 'VfyAdmin',  'username' => '_vfy_admin_',  'email' => '_vfy_a@vfy.local', 'password' => Hash::make(self::PASSWORD), 'role' => 'admin',  'is_active' => true]);
        $master = User::create(['name' => 'VfyMaster', 'username' => '_vfy_master_', 'email' => '_vfy_m@vfy.local', 'password' => Hash::make(self::PASSWORD), 'role' => 'master', 'is_active' => true, 'created_by' => $admin->id]);
        $staff  = User::create(['name' => 'VfyStaff',  'username' => '_vfy_staff_',  'email' => '_vfy_s@vfy.local', 'password' => Hash::make(self::PASSWORD), 'role' => 'staff',  'is_active' => true, 'created_by' => $master->id]);
        // A second staff under the SAME master — the adversarial "other staff" whose
        // rows must never leak into staff's own list.
        $staff2 = User::create(['name' => 'VfyStaff2', 'username' => '_vfy_staff2_', 'email' => '_vfy_s2@vfy.local', 'password' => Hash::make(self::PASSWORD), 'role' => 'staff',  'is_active' => true, 'created_by' => $master->id]);

        return compact('admin', 'master', 'staff', 'staff2');
    }

    /**
     * Seed cross-tenant tickets so the scope verifier has a real leak to catch:
     * staff and staff2 both own tickets; a correct /bets response for staff must
     * return staff's rows only. Created directly (no factory) to avoid a faker
     * dependency at runtime. All rolled back with the surrounding transaction.
     */
    private function seedTickets(array $u): void
    {
        $td = date('Y-m-d');
        $make = function (User $owner, string $session, int $n) use ($td) {
            Ticket::create([
                'user_id'        => $owner->id,
                'session'        => $session,
                'bet_date'       => $td,
                'total_amount'   => 1000,
                'invoice_number' => 'INV-VFY-' . $owner->id . '-' . $n,
                'status'         => 'pending',
                'win_amount'     => 0,
            ]);
        };

        $make($u['staff'],  'morning', 1);
        $make($u['staff'],  'noon',    2);
        $make($u['staff2'], 'morning', 1); // foreign staff, same master — must not leak
        $make($u['master'], 'morning', 1); // master's own ticket
    }

    /**
     * Seed a single always-open BetTimeSetting so Livewire bet fixtures can
     * call addBet / addRangeBet without hitting a closed-session guard.
     * All real settings are disabled within the transaction (rolled back later).
     */
    private function seedSettings(): void
    {
        BetTimeSetting::bustCache();
        BetTimeSetting::query()->update(['is_active' => false]);
        BetTimeSetting::create([
            'session_key'   => '_vfy_',
            'session_name'  => 'Verify Session',
            'result_time'   => '23:59:59',
            'group_type'    => ['A'],
            'group1_types'  => ['A'],
            'group1_cutoff' => '23:59:00',
            'group2_types'  => ['A'],
            'group2_cutoff' => '23:59:30',
            'is_active'     => true,
            'sort_order'    => 1,
        ]);
        BetTimeSetting::bustCache();
    }

    private function runFixture(array $fix): array
    {
        Auth::forgetGuards();

        // Web-guard pre-auth: setUser() seats the user on the guard instance
        // before the kernel runs, so the auth middleware finds them without a session.
        if (isset($fix['web_as']) && isset($this->users[$fix['web_as']])) {
            Auth::guard('web')->setUser($this->users[$fix['web_as']]);
        }

        $headers = [];
        if (isset($fix['as']) && isset($this->tokens[$fix['as']])) {
            $headers['Authorization'] = 'Bearer ' . $this->tokens[$fix['as']];
        }

        if (isset($fix['livewire_calls'])) {
            return $this->dispatchLivewire($fix);
        }

        try {
            $response = $this->dispatch($fix['method'], $fix['path'], $fix['body'] ?? [], $headers, $fix['accept'] ?? 'application/json');
            $status   = $response->getStatusCode();
            $body     = json_decode($response->getContent(), true);

            $checks  = array_merge(...array_map(fn($v) => $this->check($v, $status, $body), $fix['verifiers']));
            $failed  = collect($checks)->contains(fn($c) => $c['result'] === 'fail');
            $verdict = $failed ? 'FAIL' : 'PASS';

            return [
                'endpoint' => $fix['endpoint'],
                'fixture'  => $fix['fixture'],
                'type'     => $fix['type'] ?? 'happy',
                'verdict'  => $verdict,
                'http'     => $status,
                'checks'   => $checks,
                'body'     => $verdict === 'FAIL' ? $response->getContent() : null,
            ];
        } catch (\Throwable $e) {
            return [
                'endpoint' => $fix['endpoint'],
                'fixture'  => $fix['fixture'],
                'type'     => $fix['type'] ?? 'happy',
                'verdict'  => 'BLOCKED',
                'http'     => null,
                'checks'   => [],
                'body'     => $e->getMessage(),
            ];
        }
    }

    /** @return list<array{name:string,result:string,detail:string}> */
    private function check(string $v, int $status, ?array $body): array
    {
        if (str_starts_with($v, 'status:')) {
            $exp = (int) substr($v, 7);
            $ok  = $status === $exp;
            return [['name' => $v, 'result' => $ok ? 'ok' : 'fail', 'detail' => $ok ? "got $status ✓" : "expected $exp, got $status"]];
        }

        if ($v === 'envelope') {
            $ok = is_array($body) && array_key_exists('success', $body) && is_bool($body['success']);
            return [['name' => 'envelope', 'result' => $ok ? 'ok' : 'fail',
                     'detail' => $ok ? '`success` bool present ✓' : 'missing or non-bool `success` key — envelope broken']];
        }

        if ($v === 'has-token') {
            $ok = is_array($body) && isset($body['data']['token']) && is_string($body['data']['token']);
            return [['name' => 'has-token', 'result' => $ok ? 'ok' : 'fail',
                     'detail' => $ok ? 'data.token present ✓' : 'data.token missing or non-string']];
        }

        if (str_starts_with($v, 'scope:')) {
            $owner     = substr($v, 6);
            $allowedId = $this->userIds[$owner] ?? null;
            if ($allowedId === null) {
                return [['name' => $v, 'result' => 'warn', 'detail' => "unknown scope owner '$owner'"]];
            }
            $rows = $this->rowsOf($body);
            if ($rows === null) {
                return [['name' => $v, 'result' => 'fail', 'detail' => 'no rows array in response to scope-check']];
            }
            // A vacuous pass proves nothing — the seeded owner must actually have rows.
            if (count($rows) === 0) {
                return [['name' => $v, 'result' => 'fail', 'detail' => "no rows returned for $owner — cannot prove isolation"]];
            }
            $leaked = array_values(array_filter($rows, fn ($r) => ($r['user_id'] ?? null) !== $allowedId));
            $ok     = count($leaked) === 0;
            $ids    = implode(',', array_unique(array_map(fn ($r) => (string) ($r['user_id'] ?? '?'), $leaked)));
            return [['name' => $v, 'result' => $ok ? 'ok' : 'fail',
                     'detail' => $ok
                        ? count($rows) . " row(s), all owned by $owner ✓"
                        : count($leaked) . " row(s) leaked from other users (user_id: $ids) — isolation broken"]];
        }

        if (str_starts_with($v, 'has-fields:')) {
            $fields = explode(',', substr($v, 11));
            return array_map(function ($f) use ($body) {
                $ok = is_array($body) && array_key_exists(trim($f), $body);
                return ['name' => "has:$f", 'result' => $ok ? 'ok' : 'fail',
                        'detail' => $ok ? "$f present ✓" : "missing key `$f`"];
            }, $fields);
        }

        return [['name' => $v, 'result' => 'warn', 'detail' => "unknown verifier '$v'"]];
    }

    /** Locate the list of row objects in a response, handling both a paginator
     *  (`data.data`) and a plain list (`data`). Null if neither. */
    private function rowsOf(?array $body): ?array
    {
        $data = $body['data'] ?? null;
        if (is_array($data) && isset($data['data']) && is_array($data['data'])) {
            return $data['data']; // Laravel paginator envelope
        }
        if (is_array($data) && array_is_list($data)) {
            return $data;         // plain list / resource collection
        }
        return null;
    }

    /**
     * Two-step Livewire fixture: GET the page to capture the snapshot + session
     * cookie, then POST to /livewire/update with the snapshot + property updates
     * + method calls. CSRF is satisfied by forwarding the session cookie so the
     * token embedded in the page (data-csrf) matches the restored session.
     */
    private function dispatchLivewire(array $fix): array
    {
        // Step 1 — load the page, capture session + CSRF state
        $pageResp = $this->dispatch('GET', $fix['path'], [], [], 'text/html');
        if ($pageResp->getStatusCode() !== 200) {
            return [
                'endpoint' => $fix['endpoint'], 'fixture' => $fix['fixture'],
                'type' => $fix['type'] ?? 'happy', 'verdict' => 'BLOCKED',
                'http' => $pageResp->getStatusCode(),
                'checks' => [['name' => 'page-load', 'result' => 'fail', 'detail' => 'page returned '.$pageResp->getStatusCode()]],
                'body' => null,
            ];
        }

        $html = $pageResp->getContent();

        $snapshot = $this->extractSnapshot($html);
        if ($snapshot === null) {
            return [
                'endpoint' => $fix['endpoint'], 'fixture' => $fix['fixture'],
                'type' => $fix['type'] ?? 'happy', 'verdict' => 'BLOCKED',
                'http' => null,
                'checks' => [['name' => 'snapshot', 'result' => 'fail', 'detail' => 'wire:snapshot not found in page HTML']],
                'body' => null,
            ];
        }

        // Extract CSRF token Livewire embeds as data-csrf on its <script> tag
        $csrfToken = null;
        if (preg_match('/data-csrf="([^"]+)"/', $html, $m)) {
            $csrfToken = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
        }

        // Forward the session cookie so VerifyCsrfToken can match the token
        $cookies = [];
        $cookieName = config('session.cookie', 'laravel_session');
        foreach ($pageResp->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                $cookies[$cookie->getName()] = $cookie->getValue();
                break;
            }
        }

        // Step 2 — call the Livewire component update endpoint
        $payload = [
            'components' => [[
                'snapshot' => $snapshot,
                'updates'  => $fix['livewire_updates'] ?? [],
                'calls'    => $fix['livewire_calls'],
            ]],
        ];

        // X-Livewire header makes Livewire skip ConvertEmptyStringsToNull middleware,
        // which would otherwise coerce '' params to null and break typed method signatures.
        $postHeaders = ['X-Livewire' => 'true'];
        if ($csrfToken) {
            $postHeaders['X-CSRF-TOKEN'] = $csrfToken;
        }
        $response = $this->dispatch('POST', '/livewire/update', $payload, $postHeaders, 'application/json', $cookies);
        $status   = $response->getStatusCode();
        $body     = json_decode($response->getContent(), true);

        $checks = array_merge(...array_map(fn ($v) => $this->checkLivewire($v, $status, $body), $fix['verifiers']));
        $failed = collect($checks)->contains(fn ($c) => $c['result'] === 'fail');

        return [
            'endpoint' => $fix['endpoint'],
            'fixture'  => $fix['fixture'],
            'type'     => $fix['type'] ?? 'happy',
            'verdict'  => $failed ? 'FAIL' : 'PASS',
            'http'     => $status,
            'checks'   => $checks,
            'body'     => $failed ? $response->getContent() : null,
        ];
    }

    /** Extract the wire:snapshot JSON string from Livewire-rendered HTML. */
    private function extractSnapshot(string $html): ?string
    {
        // Livewire 3 HTML-encodes the JSON so inner " become &quot; — safe to
        // split on the attribute's outer double-quote delimiter.
        $raw = (string) str($html)->betweenFirst('wire:snapshot="', '"');

        return $raw !== '' ? htmlspecialchars_decode($raw, ENT_QUOTES | ENT_SUBSTITUTE) : null;
    }

    /** Verifier for Livewire update responses. Falls back to the standard check for status: verifiers. */
    private function checkLivewire(string $v, int $status, ?array $body): array
    {
        if (str_starts_with($v, 'status:')) {
            return $this->check($v, $status, $body);
        }

        if (str_starts_with($v, 'livewire:')) {
            $event = substr($v, 9);
            $dispatches = $body['components'][0]['effects']['dispatches'] ?? [];
            $names = array_column($dispatches, 'name');
            $ok = in_array($event, $names, true);

            return [['name' => "livewire:$event", 'result' => $ok ? 'ok' : 'fail',
                'detail' => $ok ? "$event dispatched ✓" : "expected '$event', got: [".implode(',', $names).']']];
        }

        return [['name' => $v, 'result' => 'warn', 'detail' => "unknown verifier '$v'"]];
    }

    private function dispatch(string $method, string $uri, array $body, array $headers, string $accept = 'application/json', array $cookies = []): \Symfony\Component\HttpFoundation\Response
    {
        $server = ['HTTP_ACCEPT' => $accept];
        foreach ($headers as $k => $v) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $k))] = $v;
        }

        $content = in_array(strtoupper($method), ['POST', 'PUT', 'PATCH']) ? json_encode($body) : null;
        $params  = ($method === 'GET') ? $body : [];
        $request = Request::create($uri, $method, $params, $cookies, [], $server, $content);

        if ($content !== null) {
            $request->headers->set('Content-Type', 'application/json');
        }

        return app(Kernel::class)->handle($request);
    }

    private function loadBetSessions(): void
    {
        $this->betSessions = BetTimeSetting::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['session_name', 'session_key', 'result_time'])
            ->toArray();
    }

    private function betModeFixtures(): array
    {
        $sessions = $this->betSessions ?: [
            ['session_name' => 'Default', 'session_key' => '_vfy_', 'result_time' => '23:59:59'],
        ];

        $modes = [
            ['fixture' => 'bet_mode_1_normal',      'updates' => ['betMode' => 1], 'calls' => [['path' => '', 'method' => 'addBet',       'params' => ['47', '1000']]]],
            ['fixture' => 'bet_mode_2_head_less',    'updates' => ['betMode' => 2], 'calls' => [['path' => '', 'method' => 'addRangeBet', 'params' => ['23', '500', '']]]],
            ['fixture' => 'bet_mode_3_middle_equal', 'updates' => ['betMode' => 3], 'calls' => [['path' => '', 'method' => 'addRangeBet', 'params' => ['456', '750', '']]]],
            ['fixture' => 'bet_mode_4_tail_greater', 'updates' => ['betMode' => 4], 'calls' => [['path' => '', 'method' => 'addRangeBet', 'params' => ['38', '300', '']]]],
            ['fixture' => 'bet_mode_5_multiple_x',   'updates' => ['betMode' => 5], 'calls' => [['path' => '', 'method' => 'addRangeBet', 'params' => ['123', '200', '']]]],
        ];

        $out = [];
        foreach ($sessions as $s) {
            $time = substr($s['result_time'], 0, 5);
            $ep   = $s['session_name'].' ✓ · '.$time;
            foreach ($modes as $m) {
                $out[] = [
                    'endpoint'         => $ep,
                    'fixture'          => $m['fixture'],
                    'type'             => 'happy',
                    'method'           => 'GET',
                    'path'             => '/home',
                    'accept'           => 'text/html',
                    'web_as'           => 'staff',
                    'livewire_updates' => $m['updates'],
                    'livewire_calls'   => $m['calls'],
                    'verifiers'        => ['status:200', 'livewire:bet-added'],
                ];
            }
        }

        return $out;
    }

    /** All fixtures — happy paths and probes. */
    private function fixtures(): array
    {
        $pw = self::PASSWORD;
        $td = date('Y-m-d');

        return [
            // ── Auth ──────────────────────────────────────────────────────────
            ['endpoint'=>'Auth','fixture'=>'valid_login',      'type'=>'happy','method'=>'POST','path'=>'/api/v1/login','body'=>['username'=>'_vfy_staff_','password'=>$pw],'verifiers'=>['status:200','envelope','has-token']],
            ['endpoint'=>'Auth','fixture'=>'wrong_password',   'type'=>'probe','method'=>'POST','path'=>'/api/v1/login','body'=>['username'=>'_vfy_staff_','password'=>'wrong_password'],'verifiers'=>['status:401','envelope']],
            ['endpoint'=>'Auth','fixture'=>'missing_fields',   'type'=>'probe','method'=>'POST','path'=>'/api/v1/login','body'=>[],'verifiers'=>['status:422']],
            ['endpoint'=>'Auth','fixture'=>'me_authenticated', 'type'=>'happy','method'=>'GET', 'path'=>'/api/v1/me','as'=>'staff','verifiers'=>['status:200','envelope']],
            ['endpoint'=>'Auth','fixture'=>'me_no_token',      'type'=>'probe','method'=>'GET', 'path'=>'/api/v1/me','verifiers'=>['status:401']],

            // ── Bets ──────────────────────────────────────────────────────────
            ['endpoint'=>'Bets','fixture'=>'list_authenticated','type'=>'happy','method'=>'GET', 'path'=>'/api/v1/bets','as'=>'staff','verifiers'=>['status:200','envelope']],
            ['endpoint'=>'Bets','fixture'=>'list_no_token',     'type'=>'probe','method'=>'GET', 'path'=>'/api/v1/bets','verifiers'=>['status:401']],
            ['endpoint'=>'Bets','fixture'=>'store_no_token',    'type'=>'probe','method'=>'POST','path'=>'/api/v1/bets','body'=>[],'verifiers'=>['status:401']],
            ['endpoint'=>'Bets','fixture'=>'store_bad_body',    'type'=>'probe','method'=>'POST','path'=>'/api/v1/bets','as'=>'staff','body'=>[],'verifiers'=>['status:422']],
            // Role-scope isolation: the headline security invariant. staff2 owns a
            // ticket too — staff must never see it; master sees only their own row.
            ['endpoint'=>'Bets','fixture'=>'scope_staff_isolation', 'type'=>'probe','method'=>'GET','path'=>'/api/v1/bets','as'=>'staff', 'verifiers'=>['status:200','envelope','scope:staff']],
            ['endpoint'=>'Bets','fixture'=>'scope_master_isolation','type'=>'probe','method'=>'GET','path'=>'/api/v1/bets','as'=>'master','verifiers'=>['status:200','envelope','scope:master']],

            // ── Records ───────────────────────────────────────────────────────
            ['endpoint'=>'Records','fixture'=>'list_as_staff',          'type'=>'happy','method'=>'GET','path'=>'/api/v1/records','as'=>'staff', 'verifiers'=>['status:200','envelope','has-fields:data,totals,meta']],
            ['endpoint'=>'Records','fixture'=>'list_as_master',         'type'=>'happy','method'=>'GET','path'=>'/api/v1/records','as'=>'master','verifiers'=>['status:200','envelope','has-fields:data,totals,meta']],
            ['endpoint'=>'Records','fixture'=>'list_as_admin',          'type'=>'happy','method'=>'GET','path'=>'/api/v1/records','as'=>'admin', 'verifiers'=>['status:200','envelope','has-fields:data,totals,meta']],
            ['endpoint'=>'Records','fixture'=>'list_with_date',         'type'=>'happy','method'=>'GET','path'=>'/api/v1/records','as'=>'staff', 'body'=>['date'=>$td],'verifiers'=>['status:200','envelope']],
            ['endpoint'=>'Records','fixture'=>'list_with_session',      'type'=>'happy','method'=>'GET','path'=>'/api/v1/records','as'=>'staff', 'body'=>['session'=>'morning'],'verifiers'=>['status:200','envelope']],
            ['endpoint'=>'Records','fixture'=>'list_no_token',          'type'=>'probe','method'=>'GET','path'=>'/api/v1/records','verifiers'=>['status:401']],

            // ── Results ───────────────────────────────────────────────────────
            ['endpoint'=>'Results','fixture'=>'list_authenticated',    'type'=>'happy','method'=>'GET', 'path'=>'/api/v1/results','as'=>'staff','verifiers'=>['status:200','envelope']],
            ['endpoint'=>'Results','fixture'=>'list_no_token',         'type'=>'probe','method'=>'GET', 'path'=>'/api/v1/results','verifiers'=>['status:401']],
            ['endpoint'=>'Results','fixture'=>'store_staff_forbidden', 'type'=>'probe','method'=>'POST','path'=>'/api/v1/results','as'=>'staff', 'body'=>['result_date'=>$td,'session'=>'morning','results'=>[['position'=>'A','number'=>'42']]],'verifiers'=>['status:403']],
            ['endpoint'=>'Results','fixture'=>'store_master_forbidden','type'=>'probe','method'=>'POST','path'=>'/api/v1/results','as'=>'master','body'=>['result_date'=>$td,'session'=>'morning','results'=>[['position'=>'A','number'=>'42']]],'verifiers'=>['status:403']],
            ['endpoint'=>'Results','fixture'=>'store_admin_ok',        'type'=>'happy','method'=>'POST','path'=>'/api/v1/results','as'=>'admin', 'body'=>['result_date'=>$td,'session'=>'morning','results'=>[['position'=>'A','number'=>'42']]],'verifiers'=>['status:201','envelope']],

            // ── Report ────────────────────────────────────────────────────────
            ['endpoint'=>'Report','fixture'=>'summary_admin',          'type'=>'happy','method'=>'GET','path'=>'/api/v1/report/summary','as'=>'admin', 'verifiers'=>['status:200','envelope']],
            ['endpoint'=>'Report','fixture'=>'summary_master',         'type'=>'happy','method'=>'GET','path'=>'/api/v1/report/summary','as'=>'master','verifiers'=>['status:200','envelope']],
            ['endpoint'=>'Report','fixture'=>'summary_staff_forbidden','type'=>'probe','method'=>'GET','path'=>'/api/v1/report/summary','as'=>'staff', 'verifiers'=>['status:403']],
            ['endpoint'=>'Report','fixture'=>'summary_no_token',       'type'=>'probe','method'=>'GET','path'=>'/api/v1/report/summary','verifiers'=>['status:401']],

            // ── Settings ──────────────────────────────────────────────────────
            ['endpoint'=>'Settings','fixture'=>'get_authenticated','type'=>'happy','method'=>'GET','path'=>'/api/v1/settings','as'=>'staff','verifiers'=>['status:200','envelope']],
            ['endpoint'=>'Settings','fixture'=>'get_no_token',     'type'=>'probe','method'=>'GET','path'=>'/api/v1/settings','verifiers'=>['status:401']],

            // ── BetForm Livewire interactions — 5 bet modes × active sessions ─
            // One group per BetTimeSetting session (loaded before the transaction).
            // seedSettings() seeds a single always-open _vfy_ session so
            // isBetAllowed is true for all groups regardless of time-of-day.
            ...$this->betModeFixtures(),
        ];
    }
}
