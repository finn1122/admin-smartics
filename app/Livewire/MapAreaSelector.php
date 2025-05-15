<?php

namespace App\Livewire;

use Livewire\Component;

class MapAreaSelector extends Component
{
    public $coordinates = [];
    public $latitude = 17.0732;  // Centro de Oaxaca
    public $longitude = -96.7266;
    public $fieldId;
    public $mapHeight = '500px';
    protected $listeners = ['areaUpdated' => 'updateCoordinates'];

    public function mount($coordinates = null)
    {
        $this->fieldId = 'map-'.uniqid(); // Generamos un ID único

        if ($coordinates) {
            $this->coordinates = $coordinates;
        }
    }

    public function updateCoordinates($geoJson)
    {
        $this->coordinates = $geoJson;
        $this->dispatch('mapAreaUpdated', coordinates: $this->coordinates);
    }

    public function render()
    {
        return view('livewire.map-area-selector', [
            'fieldId' => $this->fieldId,
            'mapHeight' => $this->mapHeight
        ]);
    }
}
