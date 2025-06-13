@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">Add New Rental</h2>
    <form method="POST" action="{{ route('rentals.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block mb-1 font-medium">Title</label>
            <input type="text" name="title" class="w-full border rounded p-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Barangay</label>
            <input type="text" name="barangay" class="w-full border rounded p-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Property Type</label>
            <select name="property_type" class="w-full border rounded p-2" required>
                <option value="studio">Studio</option>
                <option value="apartment">Apartment</option>
                <option value="house">House</option>
            </select>
        </div>
        <div>
            <label class="block mb-1 font-medium">Description</label>
            <textarea name="description" class="w-full border rounded p-2" rows="4"></textarea>
        </div>
        <div>
            <label class="block mb-1 font-medium">Price (₱/month)</label>
            <input type="number" name="price" class="w-full border rounded p-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Image</label>
            <input type="file" name="image" class="w-full border rounded p-2">
        </div>
        <button type="submit" class="btn-primary">Create Rental</button>
    </form>
</div>
@endsection
