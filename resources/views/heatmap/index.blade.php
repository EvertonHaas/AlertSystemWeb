@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Mapa de Calor</h1>

    <!-- Mapa de Calor -->
    <div id="heatmap" style="height: 500px;"></div>
</div>

<script>
    // Criação do mapa
    var map = L.map('heatmap').setView([51.505, -0.09], 13); // Defina as coordenadas iniciais

    // Adicionar camada de mapa (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Função para carregar os pontos de latitude e longitude do servidor
    fetch("{{ route('heatmap.data') }}")
        .then(response => response.json())
        .then(data => {
            // Criação do mapa de calor
            var heatData = [];

            // Iterar sobre os dados para criar os pontos do mapa de calor
            data.forEach(item => {
                // Verifique se latitude e longitude existem
                if (item.latitude && item.longitude) {
                    heatData.push([item.latitude, item.longitude]); // Adiciona cada ponto ao mapa de calor
                }
            });

            // Verifique se há pontos de calor para adicionar ao mapa
            if (heatData.length > 0) {
                // Cria o mapa de calor
                L.heatLayer(heatData, {
                    radius: 25,   // Tamanho do círculo de calor
                    blur: 15,     // Suavização do calor
                    maxZoom: 17   // Zoom máximo do mapa
                }).addTo(map);
            } else {
                console.log("Nenhum dado de latitude/longitude encontrado.");
            }
        })
        .catch(error => {
            console.error("Erro ao carregar dados de mapa de calor: ", error);
        });
</script>
@endsection
