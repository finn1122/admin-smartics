<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Field;

class PolygonMap extends Field
{
    protected string $view = 'filament.forms.components.polygon-map'; // Ruta actualizada

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


    // Asegurar que el estado se maneje como array
    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (PolygonMap $component, $state) {
            if (is_string($state)) {
                $component->state(json_decode($state, true));
            }
        });

        $this->dehydrateStateUsing(fn ($state) => is_array($state) ? $state : null);
    }
}
