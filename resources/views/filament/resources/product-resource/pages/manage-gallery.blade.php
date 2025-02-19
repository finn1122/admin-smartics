<x-filament::page>
    <h1 class="text-xl font-bold mb-4">📸 Administrar Galería</h1>

    @if(isset($record))
        <p class="text-gray-700 mb-4">📌 ID del Producto: <span class="font-semibold">{{ $record->id }}</span></p>

        {{-- Mensajes de respuesta --}}
        <div id="response-message" class="hidden p-3 rounded-lg mb-4 text-white text-center"></div>

        {{-- Contenedor de formulario en una tarjeta --}}
        <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
            <h2 class="text-lg font-bold mb-4">📤 Subir Imagen</h2>
            <form id="upload-form" action="{{ route('gallery.upload', $record->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-4">
                @csrf
                <label class="cursor-pointer bg-gray-200 text-gray-700 px-4 py-2 rounded-lg shadow hover:bg-gray-300">
                    📸 Seleccionar Imagen
                    <input type="file" name="image" accept="image/*" required class="hidden" id="image-input">
                </label>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600">
                    Subir Imagen
                </button>
            </form>

            {{-- Vista previa de la imagen antes de subir --}}
            <div id="preview-container" class="hidden mt-4">
                <h3 class="text-md font-semibold">Vista previa:</h3>
                <img id="preview-image" class="h-40 w-auto object-cover rounded-lg shadow-lg border mt-2">
            </div>
        </div>

        {{-- Contenedor de galería en otra tarjeta --}}
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-lg font-bold mb-4">🖼 Galería de Imágenes</h2>

            {{-- Galería horizontal con scroll --}}
            <div class="flex gap-4 overflow-x-auto pb-2">
                @foreach($record->gallery as $image)
                    <div class="relative group flex-shrink-0">
                        <img src="{{ $image->image_url }}" alt="Imagen del producto" class="h-40 w-auto object-cover rounded-lg shadow-lg border">

                        {{-- Botón de eliminación visible al pasar el cursor --}}
                        <form action="{{ route('gallery.delete', $image->id) }}" method="POST" class="absolute top-2 right-2 hidden group-hover:block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded-lg shadow-lg hover:bg-red-600">
                                ❌
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

    @else
        <p class="text-red-600 text-lg">❌ Error: No se encontró el producto.</p>
    @endif

    {{-- Script para manejar respuestas y vista previa --}}
    <script>
        document.getElementById('image-input').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.getElementById('preview-container').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('upload-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Evitar recarga

            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
                .then(response => response.json())
                .then(data => {
                    const messageBox = document.getElementById('response-message');
                    if (data.error) {
                        messageBox.textContent = `❌ ${data.error}`;
                        messageBox.className = "bg-red-500";
                    } else {
                        messageBox.textContent = "✅ Imagen subida correctamente";
                        messageBox.className = "bg-green-500";
                        setTimeout(() => location.reload(), 1500); // Recargar para mostrar la imagen nueva
                    }
                    messageBox.classList.remove('hidden');
                })
                .catch(() => alert("Hubo un error al subir la imagen"));
        });
    </script>

</x-filament::page>
