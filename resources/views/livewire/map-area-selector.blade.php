<div wire:ignore id="{{ $fieldId }}">
    <div
        x-data="mapAreaSelector({
            coordinates: @entangle('coordinates'),
            initialCoordinates: @js($coordinates),
            center: [@js($latitude), @js($longitude)],
            fieldId: '{{ $fieldId }}'
        })"
        x-init="initMap"
        style="width: 100%;"
    >
        <div x-ref="map" style="height: {{ $mapHeight }}; border-radius: 0.375rem; border: 1px solid #e5e7eb;"></div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <style>
        .leaflet-container {
            z-index: 0 !important;
        }
        .leaflet-top, .leaflet-bottom {
            z-index: 1 !important;
        }
        .leaflet-draw-toolbar a {
            background-image: url('https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/images/spritesheet.png') !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script>
        function mapAreaSelector(config) {
            return {
                map: null,
                drawnItems: null,
                drawControl: null,
                ...config,

                initMap() {
                    // Inicializar mapa centrado en Oaxaca
                    this.map = L.map(this.$refs.map).setView(this.center, 13);

                    // Añadir capa base
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(this.map);

                    // Configurar capa de dibujo
                    this.drawnItems = new L.FeatureGroup();
                    this.map.addLayer(this.drawnItems);

                    // Configurar controles de dibujo más visibles
                    this.drawControl = new L.Control.Draw({
                        position: 'topright',
                        draw: {
                            polygon: {
                                allowIntersection: false,
                                drawError: {
                                    color: '#e1e100',
                                    message: '¡El polígono no puede cruzarse a sí mismo!'
                                },
                                shapeOptions: {
                                    color: '#3388ff',
                                    weight: 4,
                                    opacity: 0.8,
                                    fillOpacity: 0.4,
                                    fillColor: '#3388ff'
                                },
                                showArea: true,
                                metric: true,
                                guideLayers: [this.drawnItems]
                            },
                            marker: false,
                            circle: false,
                            polyline: false,
                            rectangle: false
                        },
                        edit: {
                            featureGroup: this.drawnItems,
                            edit: true,
                            remove: true
                        }
                    });

                    this.map.addControl(this.drawControl);

                    // Manejar eventos de dibujo
                    this.map.on(L.Draw.Event.CREATED, (e) => {
                        const layer = e.layer;
                        this.drawnItems.clearLayers();
                        this.drawnItems.addLayer(layer);
                        this.coordinates = layer.toGeoJSON();
                    });

                    // Eventos para edición
                    this.map.on(L.Draw.Event.EDITED, (e) => {
                        const layers = e.layers;
                        layers.eachLayer((layer) => {
                            this.coordinates = layer.toGeoJSON();
                        });
                    });

                    this.map.on(L.Draw.Event.DELETED, () => {
                        this.coordinates = null;
                    });

                    // Dibujar polígono existente si existe
                    if (this.initialCoordinates && this.initialCoordinates.coordinates) {
                        const existingLayer = L.geoJSON(this.initialCoordinates, {
                            style: {
                                color: '#3388ff',
                                weight: 4,
                                opacity: 0.8,
                                fillOpacity: 0.4,
                                fillColor: '#3388ff'
                            }
                        }).addTo(this.map);

                        this.drawnItems.addLayer(existingLayer);
                        this.map.fitBounds(existingLayer.getBounds());
                    }
                }
            }
        }
    </script>
@endpush
