<x-filament::page>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">📸 Administrar Galería</h1>

        @if(isset($record))
            <p class="text-gray-600 mb-6">📌 ID del Producto: <span class="font-semibold text-gray-800">{{ $record->id }}</span></p>

            {{-- Mensajes de respuesta --}}
            <div id="response-message" class="hidden p-4 rounded-lg mb-6 text-white text-center font-medium"></div>

            {{-- Tarjeta para subir imágenes --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
                <h2 class="text-xl font-semibold text-indigo-700 mb-4">📤 Subir Imagen</h2>
                <form id="upload-form" action="{{ route('gallery.upload', $record->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    @csrf
                    <label class="cursor-pointer bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg border border-indigo-200 hover:bg-indigo-100 transition-colors duration-200 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                        </svg>
                        Seleccionar Imagen
                        <input type="file" name="image" accept="image/*" required class="hidden" id="image-input">
                    </label>
                    <button type="submit" style="background-color: #4F46E5; color: white;" class="hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        Subir Imagen
                    </button>
                </form>

                {{-- Vista previa de la imagen antes de subir --}}
                <div id="preview-container" class="hidden mt-6">
                    <h3 class="text-md font-semibold text-indigo-700 mb-2">Vista previa:</h3>
                    <img id="preview-image" class="h-48 w-auto object-cover rounded-lg shadow-sm border border-gray-200">
                </div>
            </div>

            {{-- Tarjeta para la galería de imágenes --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-semibold text-indigo-700 mb-4">🖼 Galería de Imágenes</h2>

                {{-- Galería en grid de 5 columnas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach($record->gallery as $image)
                        <div class="relative group">
                            <div class="relative overflow-hidden rounded-lg">
                                <img src="{{ $image->image_url }}" alt="Imagen del producto" class="h-48 w-full object-cover rounded-lg shadow-sm border border-gray-200 cursor-pointer transition-transform duration-300 group-hover:scale-105" onclick="openModal('{{ $image->image_url }}')">

                                {{-- Overlay con botones de acción --}}
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 flex items-end justify-between transition-all duration-200 p-3">
                                    <button onclick="openModal('{{ $image->image_url }}')" class="bg-indigo-600 text-white p-2 rounded-lg shadow-md hover:bg-indigo-700 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <button onclick="confirmDelete('{{ $image->id }}')" style="background-color: #DC2626;" class="text-white p-2 rounded-lg shadow-md hover:bg-red-700 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            {{-- Formulario oculto para eliminar --}}
                            <form id="delete-form-{{ $image->id }}" action="{{ route('gallery.delete', $image->id) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <p class="text-red-600 text-lg font-medium">❌ Error: No se encontró el producto.</p>
        @endif
    </div>

    {{-- Modal para ver imágenes en grande --}}
    <div id="image-modal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg overflow-hidden max-w-4xl w-full max-h-[80vh]" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center p-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-indigo-700">Vista ampliada</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-4 flex justify-center">
                <img id="modal-image" class="max-h-[60vh] w-auto object-contain">
            </div>
            <div class="p-4 bg-gray-50 flex justify-end">
                <button onclick="closeModal()" style="background-color: #4F46E5;" class="text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div id="delete-confirm-modal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg overflow-hidden max-w-md w-full shadow-xl" onclick="event.stopPropagation()">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">¿Eliminar esta imagen?</h3>
                <p class="text-gray-600 text-center mb-6">Esta acción no se puede deshacer. La imagen será eliminada permanentemente de la galería.</p>
                <div class="flex justify-center gap-4">
                    <button onclick="closeDeleteModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-5 py-2 rounded-lg transition-colors duration-200">
                        Cancelar
                    </button>
                    <button id="confirm-delete-btn" onclick="executeDelete()" style="background-color: #DC2626;" class="text-white font-medium px-5 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        Eliminar Imagen
                    </button>
                </div>
                <!-- Loader dentro del modal -->
                <div id="delete-modal-loader" class="hidden mt-4 flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                </div>
                <!-- Loader para la subida de imágenes -->
                <div id="upload-loader" class="hidden mt-4 flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script para manejar respuestas y vista previa --}}
    <script>
        let currentDeleteId = null;

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

            // Mostrar el loader
            document.getElementById('upload-loader').classList.remove('hidden');

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
                    // Ocultar el loader
                    document.getElementById('upload-loader').classList.add('hidden');

                    const messageBox = document.getElementById('response-message');
                    if (data.error) {
                        messageBox.textContent = `❌ ${data.error}`;
                        messageBox.style.backgroundColor = '#DC2626';
                    } else {
                        messageBox.textContent = "✅ Imagen subida correctamente";
                        messageBox.style.backgroundColor = '#10B981';
                        setTimeout(() => location.reload(), 1500); // Recargar para mostrar la imagen nueva
                    }
                    messageBox.classList.remove('hidden');
                    messageBox.scrollIntoView({ behavior: 'smooth' });
                })
                .catch(() => {
                    // Ocultar el loader en caso de error
                    document.getElementById('upload-loader').classList.add('hidden');

                    const messageBox = document.getElementById('response-message');
                    messageBox.textContent = "❌ Hubo un error al subir la imagen";
                    messageBox.style.backgroundColor = '#DC2626';
                    messageBox.classList.remove('hidden');
                });
        });

        function openModal(imageUrl) {
            const modal = document.getElementById('image-modal');
            document.getElementById('modal-image').src = imageUrl;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevenir scroll

            // Cerrar al hacer clic fuera del modal
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            // Añadir evento para cerrar con tecla Escape
            document.addEventListener('keydown', handleEscKey);
        }

        function handleEscKey(event) {
            if (event.key === 'Escape') {
                closeModal();
                closeDeleteModal();
            }
        }

        function closeModal() {
            document.getElementById('image-modal').classList.add('hidden');
            document.body.style.overflow = ''; // Restaurar scroll
            document.removeEventListener('keydown', handleEscKey);
        }

        // Funciones para el modal de confirmación
        function confirmDelete(imageId) {
            currentDeleteId = imageId;
            const modal = document.getElementById('delete-confirm-modal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Cerrar al hacer clic fuera
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeDeleteModal();
                }
            });

            document.addEventListener('keydown', handleEscKey);
        }

        function closeDeleteModal() {
            document.getElementById('delete-confirm-modal').classList.add('hidden');
            document.body.style.overflow = '';
            currentDeleteId = null;
            document.removeEventListener('keydown', handleEscKey);
        }

        function executeDelete() {
            if (currentDeleteId) {
                // Mostrar el loader
                document.getElementById('delete-modal-loader').classList.remove('hidden');

                const form = document.getElementById(`delete-form-${currentDeleteId}`);
                const formData = new FormData(form);
                const url = form.action;
                const method = form.method;

                fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        // Ocultar el loader
                        document.getElementById('delete-modal-loader').classList.add('hidden');

                        if (data.success) {
                            const messageBox = document.getElementById('response-message');
                            messageBox.textContent = "✅ Imagen eliminada correctamente";
                            messageBox.style.backgroundColor = '#10B981';
                            messageBox.classList.remove('hidden');

                            // Cerrar el modal de confirmación
                            closeDeleteModal();

                            // Recargar la página después de 1.5 segundos
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            const messageBox = document.getElementById('response-message');
                            messageBox.textContent = "❌ " + data.error;
                            messageBox.style.backgroundColor = '#DC2626';
                            messageBox.classList.remove('hidden');
                        }
                    })
                    .catch(() => {
                        // Ocultar el loader en caso de error
                        document.getElementById('delete-modal-loader').classList.add('hidden');

                        const messageBox = document.getElementById('response-message');
                        messageBox.textContent = "❌ Hubo un error al eliminar la imagen";
                        messageBox.style.backgroundColor = '#DC2626';
                        messageBox.classList.remove('hidden');
                    });
            }
        }
    </script>
</x-filament::page>
