<x-lumina-layout title="Importar Dados de Saúde | Lumina">
<div class="max-w-3xl mx-auto px-4 py-10 space-y-8">

    {{-- Cabeçalho --}}
    <div>
        <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-1 text-sm text-slate-400 hover:text-indigo-600 mb-4 transition-colors">
            <i class="ri-arrow-left-line"></i> Voltar ao Perfil
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Importar Dados de Saúde</h1>
        <p class="text-slate-500 mt-1 text-sm">Importa métricas do teu wearable (Apple Watch, Fitbit, Garmin, etc.) para acompanhares a tua saúde ao longo do tempo.</p>
    </div>

    {{-- Flash de sucesso --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-sm font-medium">
            <i class="ri-checkbox-circle-line text-xl"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Erros de validação --}}
    @if($errors->any())
        <div class="flex items-start gap-3 p-4 bg-rose-50 border border-rose-100 rounded-2xl text-rose-700 text-sm">
            <i class="ri-error-warning-line text-xl mt-0.5"></i>
            <div>
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Formulário de upload --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm shadow-slate-900/5 p-6 space-y-6"
         x-data="{
             dragging: false,
             fileName: null,
             format: 'csv',
             handleDrop(e) {
                 this.dragging = false;
                 const file = e.dataTransfer.files[0];
                 if (file) {
                     this.fileName = file.name;
                     this.format = file.name.endsWith('.json') ? 'json' : 'csv';
                     document.getElementById('file-input').files = e.dataTransfer.files;
                 }
             }
         }">

        <h2 class="font-semibold text-slate-700">1. Escolhe o ficheiro</h2>

        {{-- Drop zone --}}
        <label for="file-input"
               class="flex flex-col items-center justify-center gap-3 border-2 border-dashed rounded-2xl p-10 cursor-pointer transition-colors"
               :class="dragging ? 'border-indigo-400 bg-indigo-50' : 'border-slate-200 hover:border-indigo-300 hover:bg-slate-50'"
               @dragover.prevent="dragging = true"
               @dragleave.prevent="dragging = false"
               @drop.prevent="handleDrop($event)">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-400 text-2xl">
                <i class="ri-upload-cloud-2-line"></i>
            </div>
            <div class="text-center">
                <p class="text-sm font-medium text-slate-600" x-text="fileName ?? 'Arrasta o ficheiro ou clica para selecionar'"></p>
                <p class="text-xs text-slate-400 mt-1">CSV ou JSON · máx. 5 MB</p>
            </div>
        </label>

        <form action="{{ route('health.process') }}" method="POST" enctype="multipart/form-data" id="import-form">
            @csrf
            <input type="file" id="file-input" name="file" accept=".csv,.txt,.json" class="hidden"
                   @change="fileName = $event.target.files[0]?.name; format = fileName?.endsWith('.json') ? 'json' : 'csv'">
            <input type="hidden" name="format" :value="format">

            {{-- Formato --}}
            <div class="pt-2">
                <h2 class="font-semibold text-slate-700 mb-3">2. Confirma o formato</h2>
                <div class="flex gap-3">
                    <label class="flex-1 flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors"
                           :class="format === 'csv' ? 'border-indigo-400 bg-indigo-50' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" x-model="format" value="csv" class="hidden">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm"
                             :class="format === 'csv' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-500'">
                            <i class="ri-file-text-line"></i>
                        </div>
                        <span class="text-sm font-medium text-slate-700">CSV</span>
                    </label>
                    <label class="flex-1 flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors"
                           :class="format === 'json' ? 'border-indigo-400 bg-indigo-50' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" x-model="format" value="json" class="hidden">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm"
                             :class="format === 'json' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-500'">
                            <i class="ri-code-s-slash-line"></i>
                        </div>
                        <span class="text-sm font-medium text-slate-700">JSON</span>
                    </label>
                </div>
            </div>

            <button type="submit" form="import-form"
                    class="w-full mt-4 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors disabled:opacity-50"
                    :disabled="!fileName">
                <i class="ri-upload-2-line mr-2"></i> Importar
            </button>
        </form>
    </div>

    {{-- Instruções de formato --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm shadow-slate-900/5 p-6 space-y-4">
        <h2 class="font-semibold text-slate-700">Formato do ficheiro</h2>

        <div class="grid sm:grid-cols-2 gap-4">
            {{-- CSV --}}
            <div class="bg-slate-50 rounded-2xl p-4 space-y-2">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">CSV</p>
                <pre class="text-xs text-slate-600 overflow-x-auto">date,type,value
2024-01-15,heart_rate,72
2024-01-15,sleep_hours,7.5
2024-01-15,steps,8432
2024-01-15,hrv,45</pre>
            </div>
            {{-- JSON --}}
            <div class="bg-slate-50 rounded-2xl p-4 space-y-2">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">JSON</p>
                <pre class="text-xs text-slate-600 overflow-x-auto">[
  {"date":"2024-01-15",
   "type":"heart_rate",
   "value":72},
  {"date":"2024-01-15",
   "type":"sleep_hours",
   "value":7.5}
]</pre>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2">
            @foreach(\App\Models\HealthMetric::TYPES as $key => $label)
                <div class="flex items-center gap-2 text-xs text-slate-500 bg-slate-50 rounded-xl px-3 py-2">
                    <i class="{{ \App\Models\HealthMetric::ICONS[$key] }} text-indigo-400"></i>
                    <span><strong class="font-mono text-slate-700">{{ $key }}</strong><br>{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Métricas importadas --}}
    @if($recent->isNotEmpty())
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm shadow-slate-900/5 p-6 space-y-5"
             x-data="{
                 activeType: '{{ $recent->keys()->first() }}',
                 chart: null,
                 async loadChart(type) {
                     this.activeType = type;
                     const res = await fetch('{{ route('health.chart') }}?type=' + type);
                     const data = await res.json();
                     const labels = data.map(d => d.date);
                     const values = data.map(d => d.value);
                     if (this.chart) this.chart.destroy();
                     const ctx = document.getElementById('health-chart').getContext('2d');
                     this.chart = new Chart(ctx, {
                         type: 'line',
                         data: {
                             labels,
                             datasets: [{
                                 data: values,
                                 borderColor: '#6366f1',
                                 backgroundColor: 'rgba(99,102,241,0.08)',
                                 borderWidth: 2,
                                 pointRadius: 3,
                                 fill: true,
                                 tension: 0.4,
                             }]
                         },
                         options: {
                             responsive: true,
                             plugins: { legend: { display: false } },
                             scales: {
                                 x: { grid: { display: false } },
                                 y: { grid: { color: '#f1f5f9' } }
                             }
                         }
                     });
                 }
             }"
             x-init="loadChart(activeType)">

            <div class="flex items-center justify-between flex-wrap gap-3">
                <h2 class="font-semibold text-slate-700">Os teus dados (últimos 30 dias)</h2>
                @if($lastImport)
                    <span class="text-xs text-slate-400">Última importação: {{ \Carbon\Carbon::parse($lastImport)->diffForHumans() }}</span>
                @endif
            </div>

            {{-- Seletor de tipo --}}
            <div class="flex flex-wrap gap-2">
                @foreach($recent->keys() as $type)
                    <button @click="loadChart('{{ $type }}')"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl transition-colors"
                            :class="activeType === '{{ $type }}' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        <i class="{{ \App\Models\HealthMetric::ICONS[$type] ?? 'ri-pulse-line' }}"></i>
                        {{ \App\Models\HealthMetric::TYPES[$type] ?? $type }}
                    </button>
                @endforeach
            </div>

            <canvas id="health-chart" height="80"></canvas>

            {{-- Resumo estatístico --}}
            <div class="grid grid-cols-3 gap-3">
                @foreach($recent as $type => $metrics)
                    <div class="bg-slate-50 rounded-2xl p-3 text-center" x-show="activeType === '{{ $type }}'">
                        <p class="text-xs text-slate-500 mb-1">Média</p>
                        <p class="text-lg font-bold text-indigo-600">{{ number_format($metrics->avg('value'), 1) }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 text-center" x-show="activeType === '{{ $type }}'">
                        <p class="text-xs text-slate-500 mb-1">Mínimo</p>
                        <p class="text-lg font-bold text-slate-700">{{ number_format($metrics->min('value'), 1) }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 text-center" x-show="activeType === '{{ $type }}'">
                        <p class="text-xs text-slate-500 mb-1">Máximo</p>
                        <p class="text-lg font-bold text-slate-700">{{ number_format($metrics->max('value'), 1) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<x-slot name="scripts">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</x-slot>
</x-lumina-layout>
