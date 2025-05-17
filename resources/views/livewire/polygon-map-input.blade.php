<div wire:ignore>
    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.js"></script>
    @endonce

    <div
        x-data="{
            map: null,
            drawnItems: null,
            drawControl: null,
            isDrawing: false,

            initMap() {
                if (typeof L === 'undefined') return;

                // Inicializar mapa
                this.map = L.map(this.$refs.map).setView([-34.6037, -58.3816], 13);

                // Capa base
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(this.map);

                // Grupo para formas dibujadas
                this.drawnItems = new L.FeatureGroup();
                this.map.addLayer(this.drawnItems);

                // Configurar controles de dibujo
                this.setupDrawControl();

                // Cargar polígono inicial
                this.loadInitialPolygon();

                // Eventos del mapa
                this.setupMapEvents();
            },

            setupDrawControl() {
                this.drawControl = new L.Control.Draw({
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
                        featureGroup: this.drawnItems,
                        remove: true
                    }
                });
                this.map.addControl(this.drawControl);
            },

            loadInitialPolygon() {
                @if(!empty($normalizedCoordinates))
                    try {
                        const initialLayer = L.geoJSON(@json($normalizedCoordinates), {
                            style: {
                                color: '#1E90FF',
                                fillOpacity: 0.5,
                                weight: 2
                            }
                        });
                        this.drawnItems.addLayer(initialLayer);
                        this.map.fitBounds(initialLayer.getBounds());
                    } catch (e) {
                        console.error('Error loading initial polygon:', e);
                    }
                @endif
            },

            setupMapEvents() {
                // Evento cuando comienza el dibujo
                this.map.on(L.Draw.Event.DRAWSTART, () => {
                    this.isDrawing = true;
                });

                // Evento cuando termina el dibujo
                this.map.on(L.Draw.Event.DRAWSTOP, () => {
                    this.isDrawing = false;
                });

                // Polígono creado
                this.map.on(L.Draw.Event.CREATED, (e) => {
                    this.handleDrawCreated(e);
                });

                // Polígono editado
                this.map.on(L.Draw.Event.EDITED, (e) => {
                    this.handleDrawEdited(e);
                });

                // Polígono eliminado
                this.map.on(L.Draw.Event.DELETED, () => {
                    this.handleDrawDeleted();
                });
            },

            handleDrawCreated(e) {
                this.drawnItems.clearLayers();
                this.drawnItems.addLayer(e.layer);
                this.updateCoordinates(e.layer.toGeoJSON());

                // Desactivar el modo de dibujo después de crear
                if (this.drawControl) {
                    this.drawControl._toolbars.draw._modes.polygon.handler.disable();
                }
            },

            handleDrawEdited(e) {
                const layers = e.layers;
                layers.eachLayer((layer) => {
                    this.updateCoordinates(layer.toGeoJSON());
                });
            },

            handleDrawDeleted() {
                this.updateCoordinates(null);
            },

            updateCoordinates(geoJson) {
                if (!geoJson) {
                    @this.set('coordinates', null);
                    return;
                }

                const normalized = {
                    type: 'Polygon',
                    coordinates: geoJson.geometry.coordinates
                };

                @this.set('coordinates', normalized);
            }
        }"
        x-init="initMap()"
        x-on:mapRefreshed.window="initMap()"
    >
        <div x-ref="map" style="height: {{ $height }}; width: 100%;"></div>
    </div>
</div>
