<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body class="bg-light">
    <div class="container">
        <!-- Alerta de Notificação -->
        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
            <strong>Atenção!</strong> Este é um alerta de segurança. Faça login para continuar.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        @yield('content') <!-- Aqui vai o conteúdo das outras páginas -->
    </div>

    <!-- Inicializar o Mapa -->
    <script>
        // Garantir que o DOM esteja totalmente carregado antes de executar o script
        document.addEventListener("DOMContentLoaded", function() {
            // Inicializando o mapa com um local e zoom padrão
            var map = L.map('map').setView([51.505, -0.09], 13); // Coordenadas e nível de zoom do mapa

            // Adicionar camada do OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Adicionar marcador no mapa
            L.marker([51.505, -0.09]).addTo(map)
                .bindPopup('Aqui é um local de exemplo.')
                .openPopup();
        });
    </script>
</body>
</html>