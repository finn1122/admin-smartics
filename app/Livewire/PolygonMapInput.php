<?php

namespace App\Livewire;

use Livewire\Component;

class PolygonMapInput extends Component
{
    public $coordinates = [];
    public $initialCoordinates = [];
    public $height = '500px';
    public $statePath;

    protected $listeners = ['refreshMap' => 'refresh'];

    public function mount($coordinates = [], $height = '500px', $statePath = null)
    {
        $this->height = $height;
        $this->statePath = $statePath;

        // Asegurar que las coordenadas iniciales están en formato correcto
        $this->initialCoordinates = $this->normalizeCoordinates($coordinates);
    }

    protected function normalizeCoordinates($coordinates)
    {
        if (empty($coordinates)) return [];

        // Si ya es un array con la estructura correcta, devolverlo
        if (isset($coordinates['type']) && $coordinates['type'] === 'Polygon') {
            return $coordinates;
        }

        // Convertir formato si es necesario
        return [
            'type' => 'Polygon',
            'coordinates' => [$coordinates] // Asegurar estructura GeoJSON
        ];
    }

    public function refresh()
    {
        // Forzar actualización del componente
        $this->dispatch('mapRefreshed');
    }

    public function render()
    {
        return view('livewire.polygon-map-input', [
            'normalizedCoordinates' => $this->normalizeCoordinates($this->initialCoordinates)
        ]);
    }
}
