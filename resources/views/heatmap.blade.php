@extends('layouts.app')

@section('content')
<div class="container">

    <div class="row mb-3">
        <div class="col">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Ocorrências Resolvidas</h5>
                    <p class="fs-4 fw-bold">{{ $resolvedOccurrences }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-white bg-danger shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Ocorrências Pendentes</h5>
                    <p class="fs-4 fw-bold">{{ $pendingOccurrences }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Última Ocorrência</h5>
                    <p class="fs-4 fw-bold">
                        {{ $timeSinceLastOccurrence }}.
                    </p>
                </div>
            </div>
        </div>
    </div>   
    <br><br>

    <h1 class="text-center mb-4 display-4 text-primary">Dashboard</h1>
    <div class="row">
        <div class="col-md-4">
            <!-- Painéis laterais -->
            <div class="list-group">
                <button class="list-group-item list-group-item-action active" onclick="updatePieChart('total', this)">Total de Ocorrências: {{ array_sum($dataTotal) }}</button>
                <button class="list-group-item list-group-item-action" onclick="updatePieChart('year', this)">Ocorrências no último ano: {{ array_sum($dataYear) }}</button>
                <button class="list-group-item list-group-item-action" onclick="updatePieChart('month', this)">Ocorrências no último mês: {{ array_sum($dataMonth) }}</button>
                <button class="list-group-item list-group-item-action" onclick="updatePieChart('week', this)">Ocorrências na última semana: {{ array_sum($dataWeek) }}</button>
                <button class="list-group-item list-group-item-action" onclick="updatePieChart('day', this)">Ocorrências no último dia: {{ array_sum($dataDay) }}</button>
            </div>
        </div>
        <div class="col-md-8">
            <!-- Gráfico -->
            <div class="card">
                <div class="card-header text-center text-primary">
                    <h4 id="titlePieChart">Total de ocorrências</h4>
                </div>
                <div class="card-body">
                    <canvas id="pieChart" style="height: 300px; max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <br><br>   

    <h1 class="text-center mb-4 display-4 text-primary">Mapa de Calor</h1>

    <!-- Filtro por produto -->
    <div class="mb-3">
        <label for="product-select" class="form-label">Selecione a Categoria:</label>
        <div class="d-flex mb-2">
            <select id="product-select" class="form-select">
                @foreach ($products as $product)
                    <option value="{{ $product->productid }}">{{ $product->description }}</option>
                @endforeach
            </select>
            
        </div>
    </div>
    
    <div class="mb-3">
        <label for="status-select" class="form-label">Selecione o Status:</label>
        <div class="d-flex">
            <select id="status-select" class="form-select">
                <option value="pendentes" selected>Pendentes</option>
                <option value="resolvidas">Resolvidas</option>
                <option value="todas">Todas</option>
            </select>
        </div>
    </div>
    

    <!-- Filtro por período -->
    <div class="mb-3">
        <label class="form-label">Selecione o Período:</label>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <label class="form-check-label me-3">
                    <input type="radio" name="date-filter" value="" checked> Desde sempre.
                </label>
                <label class="form-check-label me-3">
                    <input type="radio" name="date-filter" value="7"> Último dia.
                </label>
                <label class="form-check-label me-3">
                    <input type="radio" name="date-filter" value="7"> Última semana.
                </label>
                <label class="form-check-label me-3">
                    <input type="radio" name="date-filter" value="30"> Último mês.
                </label>
                <label class="form-check-label">
                    <input type="radio" name="date-filter" value="365"> Último ano.
                </label>
            </div>
            <button id="apply-filter" class="btn btn-primary ms-auto">Aplicar Filtro</button>
        </div> 
    </div>
    

    <!-- Mapa de Calor -->
    <div id="heatmap" style="height: 500px;"></div>
</div>

<!-- Incluindo o Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Incluindo o plugin leaflet-heat -->
<script src="{{ asset('js/leaflet-heat.js') }}"></script>

<script>
    // Criação do mapa
    var map = L.map('heatmap').setView([-29.4596297, -51.991209], 13); // Localização inicial

    // Adicionar camada de mapa (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Função para carregar os dados do servidor
    function loadHeatmap(productId = '', days = '', status = '') {
        // URL da API com os filtros opcionais
        let url = "{{ route('heatmap.data') }}";
        let params = [];

        // Adicionar productId ao caminho
        if (productId) {
            url += `/${productId}`;
        }

        // Adicionar os parâmetros query (days e status)
        if (days) {
            params.push(`days=${days}`);
        }
        if (status) {
            params.push(`status=${status}`);
        }

        // Adicionar os parâmetros query à URL
        if (params.length > 0) {
            url += `?${params.join('&')}`;
        }

        console.log("URL gerada:", url);

        fetch(url)
        .then(response => response.json())
        .then(data => {
            // Limpar o mapa de calor anterior
            if (window.heatLayer) {
                map.removeLayer(window.heatLayer);
            }

            // Preparar os dados para o mapa de calor
            var heatData = data.map(item => [item.latitude, item.longitude]);

            // Adicionar nova camada de mapa de calor
            window.heatLayer = L.heatLayer(heatData, { radius: 25 }).addTo(map);
        })
        .catch(error => console.error("Erro ao carregar dados de mapa de calor: ", error));
    }

    // Evento de clique no botão de filtro
    document.getElementById('apply-filter').addEventListener('click', () => {
        const selectedProduct = document.getElementById('product-select').value;
        const selectedDays = document.querySelector('input[name="date-filter"]:checked').value;
        const selectedStatus = document.getElementById('status-select').value;
        loadHeatmap(selectedProduct, selectedDays, selectedStatus); // Recarregar o mapa com os filtros
    });

    // Aplicar filtro inicial com os valores selecionados no carregamento da página
    document.addEventListener('DOMContentLoaded', () => {
        const initialProduct = document.getElementById('product-select').value;
        const initialDays = document.querySelector('input[name="date-filter"]:checked').value;
        const initialStatus = document.getElementById('status-select').value;
        loadHeatmap(initialProduct, initialDays, initialStatus); // Recarregar o mapa com o filtro inicial
    });
</script>

<script>
    // Dados dinâmicos fornecidos pelo controlador
    const dataSets = {
        total: {
            labels: @json($labelTotal),
            data: @json($dataTotal)
        },
        year: {
            labels: @json($labelYear),
            data: @json($dataYear)
        },
        month: {
            labels: @json($labelMonth),
            data: @json($dataMonth)
        },
        week: {
            labels: @json($labelWeek),
            data: @json($dataWeek)
        },
        day: {
            labels: @json($labelDay),
            data: @json($dataDay)
        }
    };

    // Configuração inicial do gráfico
    const ctx = document.getElementById('pieChart').getContext('2d');
    let pieChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: dataSets.total.labels,
        datasets: [{
            data: dataSets.total.data,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'right',
                labels: {
                    generateLabels: (chart) => {
                        const data = chart.data;
                        if (data.labels.length && data.datasets.length) {
                            return data.labels.map((label, index) => {
                                const value = data.datasets[0].data[index];
                                return {
                                    text: `[${value}] ${label}`,
                                    fillStyle: data.datasets[0].backgroundColor[index],
                                    hidden: false,
                                    index: index
                                };
                            });
                        }
                        return [];
                    },
                    font: {
                        size: 14
                    },
                    color: '#333'
                }
            },
            tooltip: {
                enabled: true
            }
        }
    }
});

    // Atualizar o gráfico dinamicamente
    function updatePieChart(type, element) {
        const dataSet = dataSets[type];

        // Atualizar dados e rótulos
        pieChart.data.labels = dataSet.labels;
        pieChart.data.datasets[0].data = dataSet.data;
        pieChart.update();

        // Atualizar o título do gráfico
        const titulo = document.getElementById("titlePieChart");
        switch (type) {
            case "total":
                titulo.textContent = "Total de ocorrências";
                break;
            case "year":
                titulo.textContent = "Ocorrências no último ano";
                break;
            case "month":
                titulo.textContent = "Ocorrências no último mês";
                break;
            case "week":
                titulo.textContent = "Ocorrências na última semana";
                break;
            case "day":
                titulo.textContent = "Ocorrências no último dia";
                break;
            default:
                titulo.textContent = "";
                break;
        }

        // Atualizar classe 'active' do botão clicado
        const buttons = document.querySelectorAll(".list-group-item-action");
        buttons.forEach((btn) => btn.classList.remove("active"));
        element.classList.add("active");
    }
</script>

@endsection

