<x-filament::page>
    <style>
        .import-container {
            max-width: 48rem;
            margin: 4rem auto;
            background: white;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            padding: 2.5rem;
        }

        .import-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .import-title {
            font-size: 1.875rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .import-description {
            color: #6b7280;
            font-size: 1rem;
            line-height: 1.5;
            max-width: 42rem;
            margin: 0 auto;
        }

        .import-form {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .required-field {
            color: #ef4444;
        }

        .file-input {
            display: block;
            width: 100%;
            font-size: 0.875rem;
            color: #1f2937;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            padding: 0.625rem 1rem;
        }

        .file-input:focus {
            outline: none;
            ring: 2px;
            ring-color: #3b82f6;
            border-color: #3b82f6;
        }

        .file-input::file-selector-button {
            margin-right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 0;
            font-size: 0.875rem;
            font-weight: 600;
            background-color: #2563eb;
            color: white;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .file-input::file-selector-button:hover {
            background-color: #1d4ed8;
        }

        .error-message {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc2626;
            padding-left: 0.25rem;
        }

        .success-message {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #16a34a;
            padding-left: 0.25rem;
        }

        .checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
        }

        .checkbox-input {
            width: 1.25rem;
            height: 1.25rem;
            margin-top: 0.125rem;
            color: #2563eb;
            border-color: #d1d5db;
            border-radius: 0.25rem;
        }

        .checkbox-label {
            font-size: 0.875rem;
            color: #374151;
        }

        .checkbox-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .checkbox-description {
            color: #6b7280;
        }

        .submit-button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background-color: #2563eb;
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
        }

        .submit-button:hover {
            background-color: #1d4ed8;
        }

        .submit-button:focus {
            outline: none;
            ring: 2px;
            ring-color: #93c5fd;
        }

        .submit-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .button-icon {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.5rem;
        }

        .loading-text {
            margin-left: 0.5rem;
            font-size: 0.75rem;
            animation: pulse 1.5s infinite;
        }

        .alert-success {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            font-size: 0.875rem;
            color: #166534;
            background-color: #dcfce7;
            border: 1px solid #bbf7d0;
            border-radius: 0.5rem;
        }

        .alert-icon {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
            color: #22c55e;
        }

        .alert-title {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>

    <div class="import-container">
        <div class="import-header">
            <h2 class="import-title">Importar datos de INEGI</h2>
            <p class="import-description">Selecciona un archivo Excel válido para importar la información. Puedes elegir eliminar los datos existentes antes de comenzar.</p>
        </div>

        <form wire:submit.prevent="import" class="import-form">
            {{-- File Upload Input --}}
            <div class="form-group">
                <label class="form-label">
                    Archivo Excel <span class="required-field">*</span>
                </label>

                <input
                    type="file"
                    wire:model="file"
                    accept=".xlsx,.xls"
                    class="file-input"
                >

                @error('file')
                <p class="error-message">{{ $message }}</p>
                @enderror

                @if ($file)
                    <p class="success-message">
                        Archivo seleccionado: <strong>{{ $file->getClientOriginalName() }}</strong>
                    </p>
                @endif
            </div>

            {{-- Truncate Checkbox --}}
            <div class="checkbox-container">
                <input
                    id="truncate"
                    type="checkbox"
                    wire:model="truncate"
                    class="checkbox-input"
                >
                <div class="checkbox-label">
                    <div class="checkbox-title">Eliminar datos existentes antes de importar</div>
                    <p class="checkbox-description">Esta opción borrará todos los registros anteriores relacionados con INEGI.</p>
                </div>
            </div>

            {{-- Submit Button --}}
            <div style="text-align: right; padding-top: 1rem;">
                <button
                    type="submit"
                    class="submit-button"
                    wire:loading.attr="disabled"
                >
                    <x-heroicon-o-cloud-arrow-up class="button-icon" />
                    Iniciar Importación
                    <span wire:loading wire:target="import" class="loading-text">Procesando...</span>
                </button>
            </div>
        </form>

        {{-- Success Message --}}
        @if ($imported)
            <div class="alert-success" role="alert">
                <x-heroicon-o-check-circle class="alert-icon" />
                <div>
                    <p class="alert-title">¡Importación exitosa!</p>
                    <p>Se procesaron <strong>{{ number_format($rowCount, 0) }}</strong> registros correctamente.</p>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
