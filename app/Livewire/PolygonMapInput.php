<?php

namespace App\Livewire;

use Livewire\Component;

class PolygonMapInput extends Component
{
    public $coordinates = [];
    public $initialCoordinates = [];
    public $height = '500px';
    public $statePath;
    public $leafletLoaded = false;

    protected $listeners = ['leafletLoaded' => 'setLeafletLoaded'];

    public function mount($coordinates = [], $height = '500px', $statePath = null)
    {
        $this->height = $height;
        $this->initialCoordinates = $coordinates;
        $this->statePath = $statePath;
    }
    public function setLeafletLoaded()
    {
        $this->leafletLoaded = true;
    }


    public function render()
    {
        return view('livewire.polygon-map-input');
    }
}
