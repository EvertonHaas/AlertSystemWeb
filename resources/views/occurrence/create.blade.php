@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center mb-4 display-4 text-primary">Nova Ocorrência</h1>
    
    <form method="POST" action="{{ route('occurrence.store') }}">
        @csrf
        <div class="mb-3">
            <label for="product-select" class="form-label">Selecione a Categoria:</label>
            <select id="product-select" name="productid" class="form-select" required>
                @foreach ($products as $product)
                    <option value="{{ $product->productid }}" data-type="{{ $product->type }}">
                        {{ $product->description }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <!-- Campos de entrada para os diferentes tipos -->
        <div class="mb-3 type-input" id="type-int" style="display: none;">
            <label for="value-int" class="form-label">Valor (Inteiro)</label>
            <input type="number" step="1" class="form-control" id="value-int" name="value_int">
        </div>
        
        <div class="mb-3 type-input" id="type-float" style="display: none;">
            <label for="value-float" class="form-label">Valor (Decimal)</label>
            <input type="number" step="0.01" class="form-control" id="value-float" name="value_float">
        </div>
        
        <div class="mb-3 type-input" id="type-text" style="display: none;">
            <label for="value-text" class="form-label">Valor (Texto)</label>
            <input type="text" class="form-control" id="value-text" name="value_text">
        </div>
        
        <div class="mb-3 type-input" id="type-logic" style="display: none;">
            <label for="value-logic" class="form-label">Valor (Lógico)</label>
            <select class="form-select" id="value-logic" name="value_logic">
                <option value="true">Sim</option>
                <option value="false">Não</option>
            </select>
        </div>

        <!-- Mapas e outros campos permanecem iguais -->
        <div class="mb-3">
            <label for="map" class="form-label">Localização</label>
            <div id="map" style="height: 500px;"></div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="latitude" class="form-label">Latitude</label>
                    <input type="number" step="0.000001" class="form-control" id="latitude" name="latitude" required readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="longitude" class="form-label">Longitude</label>
                    <input type="number" step="0.000001" class="form-control" id="longitude" name="longitude" required readonly>
                </div>
            </div>
        </div>        
        
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>

<!-- Incluindo o Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = document.getElementById('product-select');
        const typeInputs = document.querySelectorAll('.type-input');

        // Função para esconder todos os campos e mostrar o correto
        function updateFieldVisibility() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const selectedType = selectedOption.getAttribute('data-type');

            // Oculta todos os campos
            typeInputs.forEach(input => input.style.display = 'none');

            // Exibe o campo correto baseado no tipo
            if (selectedType) {
                const typeInput = document.getElementById(`type-${selectedType.toLowerCase()}`);
                if (typeInput) {
                    typeInput.style.display = 'block';
                }
            }
        }

        // Atualiza os campos na mudança de seleção
        productSelect.addEventListener('change', updateFieldVisibility);

        // Atualiza os campos ao carregar a página
        updateFieldVisibility();
    });

    // Script do Leaflet permanece o mesmo
    var initialLat = -29.444988604470275;
    var initialLng = -51.95472657680512;

    var map = L.map('map').setView([initialLat, initialLng], 17);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
    document.getElementById('latitude').value = initialLat;
    document.getElementById('longitude').value = initialLng;

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('latitude').value = e.latlng.lat;
        document.getElementById('longitude').value = e.latlng.lng;
    });

    marker.on('dragend', function(e) {
        var latlng = e.target.getLatLng();
        document.getElementById('latitude').value = latlng.lat;
        document.getElementById('longitude').value = latlng.lng;
    });
</script>
@endsection
