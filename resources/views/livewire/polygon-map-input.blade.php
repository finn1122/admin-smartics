@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.js"></script>
@endonce
<div wire:ignore>
    <!-- Cargar recursos de Leaflet dinámicamente -->
    @unless($leafletLoaded)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.css" />
        <script>
            function loadScript(src, callback) {
                var script = document.createElement('script');
                script.src = src;
                script.onload = callback;
                document.body.appendChild(script);
            }

            loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', function() {
                loadScript('https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.js', function() {
                    window.dispatchEvent(new Event('leafletLoaded'));
                });
            });
        </script>
    @endunless

    <!-- Contenedor del mapa -->
    <div
        x-data="{
            map: null,
            drawnItems: null,
            initMap() {
                // Esperar a que Leaflet esté cargado
                const checkLeaflet = setInterval(() => {
                    if (typeof L !== 'undefined') {
                        clearInterval(checkLeaflet);
                        this.setupMap();
                    }
                }, 100);
            },
            setupMap() {
                // Configuración del mapa
                this.map = L.map(this.$refs.map).setView([-34.6037, -58.3816], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(this.map);

                this.drawnItems = new L.FeatureGroup();
                this.map.addLayer(this.drawnItems);

                const drawControl = new L.Control.Draw({
                    draw: {
                        polygon: {
                            allowIntersection: false,
                            showArea: true,
                            shapeOptions: {
                                color: '#1E90FF',
                                fillOpacity: 0.5
                            }
                        },
                        polyline: false,
                        rectangle: false,
                        circle: false,
                        marker: false
                    },
                    edit: {
                        featureGroup: this.drawnItems,
                        remove: true
                    }
                });
                this.map.addControl(drawControl);

                // Cargar polígono inicial si existe
                @if(!empty($initialCoordinates))
                    const initialPolygon = L.geoJSON(@json($initialCoordinates), {
                        style: {
                            color: '#1E90FF',
                            fillOpacity: 0.5
                        }
                    }).addTo(this.map);
                    this.drawnItems.addLayer(initialPolygon);
                    this.map.fitBounds(initialPolygon.getBounds());
                @endif

                // Eventos
                this.map.on('draw:created', (e) => {
                    this.drawnItems.clearLayers();
                    this.drawnItems.addLayer(e.layer);
                    @this.set('coordinates', e.layer.toGeoJSON());
                });

                this.map.on('draw:edited', (e) => {
                    e.layers.eachLayer((layer) => {
                        @this.set('coordinates', layer.toGeoJSON());
                    });
                });

                this.map.on('draw:deleted', () => {
                    @this.set('coordinates', null);
                });
            }
        }"
        x-init="initMap()"
        x-on:leafletLoaded.window="initMap()"
    >
        <div x-ref="map" style="height: {{ $height }}; width: 100%;"></div>
    </div>
</div>
