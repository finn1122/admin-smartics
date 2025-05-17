<!-- resources/views/delivery-areas/create.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Crear Área de Entrega</h1>

        <form id="delivery-area-form" method="POST" action="{{ route('delivery-areas.store') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Precio</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Área de cobertura</label>
                <div id="map" style="height: 500px; width: 100%;"></div>
                <input type="hidden" name="coordinates" id="coordinates">
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>

    <!-- Leaflet CSS/JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('map').setView([-34.6037, -58.3816], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            const drawControl = new L.Control.Draw({
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        shapeOptions: {
                            color: '#1E90FF',
                            fillOpacity: 0.5,
                            weight: 2
                        }
                    },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    marker: false
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true
                }
            });
            map.addControl(drawControl);

            // Eventos de dibujo
            map.on(L.Draw.Event.CREATED, function(e) {
                drawnItems.clearLayers();
                drawnItems.addLayer(e.layer);
                updateCoordinates(e.layer.toGeoJSON());
            });

            map.on(L.Draw.Event.EDITED, function(e) {
                e.layers.eachLayer(function(layer) {
                    updateCoordinates(layer.toGeoJSON());
                });
            });

            map.on(L.Draw.Event.DELETED, function() {
                updateCoordinates(null);
            });

            function updateCoordinates(geoJson) {
                const coordinatesInput = document.getElementById('coordinates');
                const normalized = geoJson ? {
                    type: 'Polygon',
                    coordinates: geoJson.geometry.coordinates
                } : null;

                coordinatesInput.value = JSON.stringify(normalized);
            }

            // Validar antes de enviar
            document.getElementById('delivery-area-form').addEventListener('submit', function(e) {
                if (!document.getElementById('coordinates').value) {
                    e.preventDefault();
                    alert('Por favor dibuja un área de cobertura');
                }
            });
        });
    </script>
@endsection
