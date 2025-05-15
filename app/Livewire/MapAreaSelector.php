<?php

namespace App\Livewire;

use Livewire\Component;

class MapAreaSelector extends Component
{
    public $coordinates = [];
    public $latitude = 19.4326;
    public $longitude = -99.1332;
    public $fieldId; // Añadimos identificador único para el campo
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
            'fieldId' => $this->fieldId
        ]);
    }
}
