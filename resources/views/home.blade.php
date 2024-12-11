@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Mapa de Calor</h1>

    <!-- Filtro por produto -->
    <div class="mb-3">
        <label for="product-select" class="form-label">Selecione a Categoria:</label>
        <div class="d-flex">
            <select id="product-select" class="form-select">
                @foreach ($products as $product)
                    <option value="{{ $product->productid }}">{{ $product->description }}</option>
                @endforeach
            </select>
            <button id="apply-filter" class="btn btn-primary ms-2">Aplicar Filtro</button>
        </div>
    </div>

    <!-- Filtro por período -->
    <div class="mb-3">
        <label class="form-label">Selecione o Período:</label>
        <div>
            <label class="form-check-label me-3">
                <input type="radio" name="date-filter" value="" checked> Desde Sempre
            </label>
            <label class="form-check-label me-3">
                <input type="radio" name="date-filter" value="7"> Últimos 7 dias
            </label>
            <label class="form-check-label me-3">
                <input type="radio" name="date-filter" value="30"> Últimos 30 dias
            </label>
            <label class="form-check-label">
                <input type="radio" name="date-filter" value="365"> Últimos 365 dias
            </label>
        </div>
    </div>

    <!-- Mapa de Calor -->
    <div id="heatmap" style="height: 500px;"></div>
</div>

<!-- Incluindo o Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

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
    function loadHeatmap(productId = '', days = '') {
        // URL da API com os filtros opcionais
        let url = "{{ route('heatmap.data') }}";
        if (productId) {
            url += `/${productId}`;
        }
        if (days) {
            url += `?days=${days}`;
        }

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
        loadHeatmap(selectedProduct, selectedDays); // Recarregar o mapa com os filtros
    });

    // Aplicar filtro inicial com os valores selecionados no carregamento da página
    document.addEventListener('DOMContentLoaded', () => {
        const initialProduct = document.getElementById('product-select').value;
        const initialDays = document.querySelector('input[name="date-filter"]:checked').value;
        loadHeatmap(initialProduct, initialDays); // Recarregar o mapa com o filtro inicial
    });
</script>
@endsection
