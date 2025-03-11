<div class="p-6 bg-white rounded-lg shadow-lg">
    <!-- Título y detalles del producto -->
    <div class="mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Detalles del Producto</h2>
        <p class="text-gray-600"><span class="font-semibold">ID:</span> {{ $product->id }}</p>
        <p class="text-gray-600"><span class="font-semibold">Nombre:</span> {{ $product->name }}</p>
        <p class="text-gray-600"><span class="font-semibold">SKU:</span> {{ $product->sku }}</p>
    </div>

    <!-- Galería de imágenes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($product->gallery as $image)
            <div class="relative rounded-lg overflow-hidden shadow-md cursor-pointer group"
                 onclick="showImageModal('{{ $image->image_url }}')">
                <img src="{{ $image->image_url }}"
                     alt="Imagen del producto"
                     class="w-full h-48 object-cover transition-all duration-300 transform group-hover:scale-105 group-hover:shadow-lg">

                <!-- Overlay efecto al pasar el mouse -->
                <div class="absolute inset-0 bg-black bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <span class="text-white text-lg font-semibold">🔍 Ver imagen</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal de Imagen -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center hidden z-50 transition-opacity duration-300">
        <div class="relative bg-white rounded-lg shadow-xl p-4 max-w-3xl w-full">
            <!-- Botón de cierre -->
            <button class="absolute top-3 right-3 bg-red-600 text-white rounded-full p-2 hover:bg-red-700 transition"
                    onclick="closeImageModal()">
                ✖
            </button>
            <img id="modalImage" class="w-full max-h-[80vh] rounded-lg shadow-md">
        </div>
    </div>
</div>

<script>
    function showImageModal(imageUrl) {
        document.getElementById('modalImage').src = imageUrl;
        document.getElementById('imageModal').classList.remove('hidden');
        document.getElementById('imageModal').classList.add('opacity-100');
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.getElementById('imageModal').classList.remove('opacity-100');
    }
</script>
