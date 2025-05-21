<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InegiPostalDataResource\Pages;
use App\Filament\Resources\InegiPostalDataResource\RelationManagers;
use App\Models\InegiMunicipality;
use App\Models\InegiPostalData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;
use Filament\Actions\Action;
class InegiPostalDataResource extends Resource
{
    protected static ?string $model = InegiPostalData::class;

    protected static ?string $navigationIcon = 'heroicon-o-map'; // Icono consistente
    protected static ?string $navigationGroup = 'Datos Geográficos Inegi'; // Grupo en el menú
    protected static ?string $modelLabel = 'Estado';
    protected static ?string $pluralModelLabel = 'Estados';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('d_codigo')
                    ->label('Código Postal')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('d_asenta')
                    ->label('Asentamiento')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('settlementType.d_tipo_asenta')
                    ->label('Tipo Asentamiento')
                    ->badge(),

                TextColumn::make('state.d_estado')
                    ->label('Estado')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('municipality_name')
                    ->label('Municipio')
                    ->getStateUsing(function ($record) {
                        $mun = InegiMunicipality::where('c_estado', $record->c_estado)
                            ->where('c_mnpio', $record->c_mnpio)
                            ->first();
                        return $mun ? $mun->D_mnpio : 'N/A';
                    }),

                TextColumn::make('city.d_ciudad')
                    ->label('Ciudad')
                    ->placeholder('Sin ciudad')
                    ->searchable(),

                TextColumn::make('d_zona')
                    ->label('Zona')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Urbano' => 'success',
                        'Rural' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('latitud')
                    ->label('Latitud')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('longitud')
                    ->label('Longitud')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Estado con búsqueda textual
                SelectFilter::make('state')
                    ->relationship('state', 'd_estado')
                    ->label('Estado')
                    ->searchable(),

                // Municipio con búsqueda textual
                SelectFilter::make('municipio')
                    ->label('Municipio')
                    ->searchable()
                    ->options(function () {
                        return InegiMunicipality::query()
                            ->orderBy('D_mnpio')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->c_estado . '-' . $item->c_mnpio => $item->D_mnpio];
                            });
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            [$estado, $mnpio] = explode('-', $data['value']);
                            $query->where('c_estado', $estado)
                                ->where('c_mnpio', $mnpio);
                        }
                    }),

                SelectFilter::make('settlementType')
                    ->relationship('settlementType', 'd_tipo_asenta')
                    ->label('Tipo Asentamiento'),

                SelectFilter::make('zona')
                    ->options([
                        'Urbano' => 'Urbano',
                        'Rural' => 'Rural'
                    ])
                    ->label('Zona'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                'state.d_estado',
                'municipality.D_mnpio',
                'd_zona',
            ]);
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInegiPostalData::route('/'),
            'create' => Pages\CreateInegiPostalData::route('/create'),
            'edit' => Pages\EditInegiPostalData::route('/{record}/edit'),
            'import' => Pages\ImportInegiPostalData::route('/import'),
        ];
    }
}
