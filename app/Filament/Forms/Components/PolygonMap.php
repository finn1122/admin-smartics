<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Livewire\Livewire;
class PolygonMap extends Field
{
    protected string $view = 'filament.forms.components.polygon-map';

    protected string $height = '500px';

    public function height(string $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): string
    {
        return $this->height;
    }
}
