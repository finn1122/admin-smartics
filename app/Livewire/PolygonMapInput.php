<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class PolygonMapInput extends Component
{
    public $coordinates = [];
    public $initialCoordinates = [];
    public $height = '500px';
    public $statePath;
    public $componentId;

    public function mount($coordinates = [], $height = '500px', $statePath = null)
    {
        $this->height = $height;
        $this->statePath = $statePath;
        $this->initialCoordinates = $this->normalizeCoordinates($coordinates);
        $this->componentId = 'map-' . uniqid();
    }

    protected function normalizeCoordinates($coordinates)
    {
        if (empty($coordinates)) return [];

        if (isset($coordinates['type']) && $coordinates['type'] === 'Polygon') {
            return $coordinates;
        }

        return [
            'type' => 'Polygon',
            'coordinates' => [$coordinates]
        ];
    }

    #[On('refreshMap')]
    public function refresh()
    {
        $this->dispatch('mapRefreshed');
    }

    // Método modificado para evitar inyección de dependencias
    public function handlePolygonUpdate($eventData = null)
    {
        // Manejar tanto el formato de evento como el de datos directos
        $data = is_array($eventData) ? $eventData : (array) $eventData;

        if ($this->statePath && (!isset($data['statePath']) || $data['statePath'] !== $this->statePath)) {
            return;
        }

        $this->coordinates = $data['coordinates'] ?? null;

        $this->dispatch('polygonUpdated',
            statePath: $this->statePath,
            coordinates: $this->coordinates
        );
    }

    public function render()
    {
        return view('livewire.polygon-map-input', [
            'normalizedCoordinates' => $this->normalizeCoordinates($this->initialCoordinates),
            'componentId' => $this->componentId
        ]);
    }
}
