<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rental;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        $query = Rental::query();

        if ($request->filled('barangay')) {
            $query->where('barangay', 'like', '%' . $request->barangay . '%');
        }
        if ($request->filled('budget')) {
            $query->where('price', '<=', $request->budget);
        }
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        $rentals = $query->latest()->paginate(12);
        return view('home', compact('rentals'));
    }

    public function create()
    {
        return view('rentals.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'property_type' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('rentals', 'public');
        }

        Rental::create($validated);
        return redirect()->route('rentals.index')->with('success', 'Rental created successfully!');
    }
}
