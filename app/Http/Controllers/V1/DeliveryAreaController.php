<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\DeliveryArea;
use Illuminate\Http\Request;

class DeliveryAreaController extends Controller
{
    public function create()
    {
        return view('delivery-areas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(DeliveryArea::rules());

        $area = DeliveryArea::create($validated);

        return redirect()->route('delivery-areas.index')
            ->with('success', 'Área creada correctamente');
    }
}
