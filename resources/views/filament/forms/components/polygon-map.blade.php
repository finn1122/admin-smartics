<div
    x-data="polygonMap({
        statePath: '{{ $getStatePath() }}',
        height: '{{ $getHeight() }}',
        initialCoordinates: {{ json_encode($getState()) }},
        mapId: 'map-{{ $getId() }}'
    })"
    wire:ignore
    {{ $attributes->merge($getExtraAttributes())->class([
        'fi-fo-field-wrp',
        'flex flex-col gap-y-1',
    ]) }}
>
    @if ($label = $getLabel())
        <label class="fi-fo-field-wrp-label flex items-center gap-x-3">
            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                {{ $label }}
            </span>
        </label>
    @endif

    <div x-ref="mapContainer" :id="mapId" :style="`height: ${height}; width: 100%;`"></div>

    @if ($hint = $getHint())
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $hint }}
        </p>
    @endif

    @error($getStatePath())
    <p class="text-sm text-danger-600 dark:text-danger-400">
        {{ $message }}
    </p>
    @enderror
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('polygonMap', (config) => ({
                map: null,
                drawnItems: null,
                drawControl: null,
                mapId: config.mapId,
                statePath: config.statePath,
                height: config.height,
                initialCoordinates: config.initialCoordinates,

                init() {
                    this.loadLeaflet().then(() => {
                        this.initMap();
                        this.setupEvents();
                        this.loadInitialPolygon();
                    });
                },

                loadLeaflet() {
                    return new Promise((resolve) => {
                        if (typeof L !== 'undefined') {
                            resolve();
                            return;
                        }

                        const styles = [
                            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                            'https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.css'
                        ];

                        const scripts = [
                            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                            'https://unpkg.com/leaflet-draw@1.0.2/dist/leaflet.draw.js'
                        ];

                        styles.forEach(href => {
                            const link = document.createElement('link');
                            link.rel = 'stylesheet';
                            link.href = href;
                            document.head.appendChild(link);
                        });

                        let loaded = 0;
                        scripts.forEach(src => {
                            const script = document.createElement('script');
                            script.src = src;
                            script.onload = () => {
                                if (++loaded === scripts.length) resolve();
                            };
                            document.body.appendChild(script);
                        });
                    });
                },

                initMap() {
                    this.map = L.map(this.mapId).setView([-34.6037, -58.3816], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    this.drawnItems = new L.FeatureGroup();
                    this.map.addLayer(this.drawnItems);

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
                    if (!this.initialCoordinates) return;

                    try {
                        const layer = L.geoJSON(this.initialCoordinates, {
                            style: {
                                color: '#1E90FF',
                                fillOpacity: 0.5,
                                weight: 2
                            }
                        });
                        this.drawnItems.addLayer(layer);
                        this.map.fitBounds(layer.getBounds());
                    } catch (e) {
                        console.error('Error loading initial polygon:', e);
                    }
                },

                setupEvents() {
                    this.map.on(L.Draw.Event.CREATED, (e) => {
                        this.drawnItems.clearLayers();
                        this.drawnItems.addLayer(e.layer);
                        this.updateState(e.layer.toGeoJSON());
                    });

                    this.map.on(L.Draw.Event.EDITED, (e) => {
                        e.layers.eachLayer((layer) => {
                            this.updateState(layer.toGeoJSON());
                        });
                    });

                    this.map.on(L.Draw.Event.DELETED, () => {
                        this.updateState(null);
                    });
                },

                updateState(geoJson) {
                    const normalized = geoJson ? {
                        type: 'Polygon',
                        coordinates: geoJson.geometry.coordinates
                    } : null;

                    this.$wire.set(this.statePath, normalized, true);
                }
            }));
        });
    </script>
@endpush
