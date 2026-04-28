<x-admin-layout>
    <x-slot:title>Dashboard — HT ភ្នាក់ Admin</x-slot:title>
    <x-slot:pageTitle>Dashboard</x-slot:pageTitle>

    {{-- ========== STAT CARDS ========== --}}
    <div class="row">

        <div class="col-6 col-lg-3">
            <div class="small-box" style="background-color:#DC143C;color:#fff;">
                <div class="inner">
                    <h3>{{ $totalMasters ?? 0 }}</h3>
                    <p>Total Masters</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
                <a href="{{ route('account') }}" class="small-box-footer" style="background-color:rgba(0,0,0,0.15);">
                    Manage <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="small-box" style="background-color:#D4A017;color:#fff;">
                <div class="inner">
                    <h3>{{ $totalStaff ?? 0 }}</h3>
                    <p>Total Staff</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('account') }}" class="small-box-footer" style="background-color:rgba(0,0,0,0.15);">
                    Manage <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $todayTickets ?? 0 }}</h3>
                    <p>Today's Tickets</p>
                </div>
                <div class="icon"><i class="fas fa-ticket-alt"></i></div>
                <a href="{{ route('record') }}" class="small-box-footer">
                    View Records <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 style="font-size:1.4rem;">{{ number_format($todayAmount ?? 0) }}</h3>
                    <p>Today's Amount (KHR)</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                <a href="{{ route('report') }}" class="small-box-footer">
                    View Report <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ========== MASTER STATS TABLE + CHART ========== --}}
    <div class="row">

        {{-- Master Stats Table --}}
        <div class="col-lg-5">
            <div class="card card-outline" style="border-top:3px solid #DC143C;">
                <div class="card-header d-flex align-items-center" style="background-color:#DC143C;">
                    <h3 class="card-title text-white font-weight-bold">
                        <i class="fas fa-table mr-1"></i> Master Summary
                    </h3>
                    <span class="ml-auto badge badge-light">{{ today()->format('d M Y') }}</span>
                </div>
                <div class="card-body p-0">
                    @if(isset($masterStats) && $masterStats->isNotEmpty())
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr style="background-color:#DC143C;color:#fff;">
                                <th>Master</th>
                                <th class="text-center">Staff</th>
                                <th class="text-center">Tickets</th>
                                <th class="text-right">Amount (KHR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($masterStats as $master)
                            <tr>
                                <td class="font-weight-bold" style="color:#DC143C;">{{ $master->name }}</td>
                                <td class="text-center">{{ $master->staff_count }}</td>
                                <td class="text-center">{{ $master->tickets_today }}</td>
                                <td class="text-right font-weight-bold" style="color:#D4A017;">
                                    {{ number_format($master->amount_today) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color:#D4A017;color:#fff;" class="font-weight-bold">
                                <td>Total</td>
                                <td class="text-center">{{ $masterStats->sum('staff_count') }}</td>
                                <td class="text-center">{{ $masterStats->sum('tickets_today') }}</td>
                                <td class="text-right">{{ number_format($masterStats->sum('amount_today')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                        No master data for today
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 7-Day Chart --}}
        <div class="col-lg-7">
            <div class="card card-outline" style="border-top:3px solid #D4A017;">
                <div class="card-header" style="background-color:#D4A017;">
                    <h3 class="card-title text-white font-weight-bold">
                        <i class="fas fa-chart-line mr-1"></i> Bet Amount — Last 7 Days
                    </h3>
                </div>
                <div class="card-body">
                    @if(isset($masterStats) && $masterStats->isNotEmpty())
                    <canvas id="masterChart" style="min-height:220px;"></canvas>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                        No chart data yet
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ========== QUICK LINKS ========== --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold" style="color:#DC143C;">
                        <i class="fas fa-bolt mr-1"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('result') }}" class="btn btn-sm mr-2 mb-2" style="background-color:#DC143C;color:#fff;">
                        <i class="fas fa-trophy mr-1"></i> Enter Results
                    </a>
                    <a href="{{ route('record') }}" class="btn btn-sm mr-2 mb-2" style="background-color:#D4A017;color:#fff;">
                        <i class="fas fa-book-open mr-1"></i> View Records
                    </a>
                    <a href="{{ route('report') }}" class="btn btn-sm mr-2 mb-2 btn-info">
                        <i class="fas fa-chart-bar mr-1"></i> Reports
                    </a>
                    <a href="{{ route('setting') }}" class="btn btn-sm mr-2 mb-2 btn-secondary">
                        <i class="fas fa-cogs mr-1"></i> Settings
                    </a>
                    <a href="{{ route('account') }}" class="btn btn-sm mb-2 btn-dark">
                        <i class="fas fa-users mr-1"></i> Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(isset($masterStats) && $masterStats->isNotEmpty())
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const COLORS = ['#DC143C','#D4A017','#2563eb','#16a34a','#9333ea','#ea580c','#0891b2','#db2777'];
        const labels  = @json($chartLabels);
        const masters = @json($masterStats->map(fn($m) => ['name' => $m->name, 'data' => $m->chart_data])->values());

        const datasets = masters.map((m, i) => ({
            label           : m.name,
            data            : m.data,
            borderColor     : COLORS[i % COLORS.length],
            backgroundColor : COLORS[i % COLORS.length] + '22',
            borderWidth     : 2,
            pointRadius     : 4,
            tension         : 0.3,
            fill            : true,
        }));

        new Chart(document.getElementById('masterChart'), {
            type : 'line',
            data : { labels, datasets },
            options : {
                responsive : true,
                plugins : {
                    legend  : { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip : {
                        callbacks : {
                            label : ctx => ' ' + ctx.dataset.label + ': ' + Number(ctx.parsed.y).toLocaleString() + ' KHR',
                        },
                    },
                },
                scales : {
                    y : {
                        beginAtZero : true,
                        ticks : {
                            font     : { size: 10 },
                            callback : v => v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v,
                        },
                    },
                    x : { ticks : { font : { size: 10 } } },
                },
            },
        });
    })();
    </script>
    @endpush
    @endif

</x-admin-layout>
