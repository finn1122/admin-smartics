<x-filament::page>
    <x-filament::card>
        <div class="p-6">
            <h2 class="text-lg font-bold mb-4">Actualizar productos por marca</h2>

            <!-- Formulario para seleccionar la marca -->
            <form wire:submit.prevent="updateProductsByBrand">
                {{ $this->form }}

                <!-- Botón para ejecutar la acción -->
                <x-filament::button type="submit" class="mt-6"> <!-- Aumenté el margen superior -->
                    Actualizar productos
                </x-filament::button>
            </form>
        </div>
    </x-filament::card>
</x-filament::page>
