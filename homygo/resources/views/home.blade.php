<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Homygo</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[color:var(--bg)] text-[color:var(--text)] min-h-screen px-6 py-12">

  {{-- 🧱 Hero section --}}
  <header class="max-w-6xl mx-auto text-center mb-10">
    <h1 class="text-4xl sm:text-5xl font-bold mb-4">
      Find your next home with <span class="text-[color:var(--accent)]">AI</span>
    </h1>
    <p class="text-lg text-gray-600">
      Browse rental listings across Cagayan de Oro with smart filters
    </p>
  </header>

  {{-- 🔍 Search Bar --}}
  <section class="max-w-4xl mx-auto mb-12">
    <form method="GET" action="{{ route('home') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      <input type="text" name="barangay" value="{{ request('barangay') }}" placeholder="Barangay/District" class="border border-gray-300 rounded p-3" />
      <input type="number" name="budget" value="{{ request('budget') }}" placeholder="Budget (₱)" class="border border-gray-300 rounded p-3" />
      <select name="property_type" class="border border-gray-300 rounded p-3">
        <option value="">Property type</option>
        <option value="studio" @if(request('property_type')=='studio') selected @endif>Studio</option>
        <option value="apartment" @if(request('property_type')=='apartment') selected @endif>Apartment</option>
        <option value="house" @if(request('property_type')=='house') selected @endif>House</option>
      </select>
      <button type="submit" class="btn-primary">Search</button>
    </form>
  </section>

  {{-- 🧾 Listings Section --}}
  <section class="max-w-6xl mx-auto grid sm:grid-cols-2 md:grid-cols-3 gap-6">
    @if(isset($rentals) && count($rentals))
      @foreach($rentals as $rental)
        <div class="bg-white rounded-lg overflow-hidden shadow p-4">
          <img src="{{ $rental->image }}" alt="Rental" class="w-full h-40 object-cover rounded mb-3">
          <h3 class="text-lg font-semibold">{{ $rental->title }}</h3>
          <p class="text-gray-500">{{ $rental->barangay }} • {{ ucfirst($rental->property_type) }}</p>
          <p class="text-[color:var(--accent)] font-bold mt-2">₱{{ number_format($rental->price, 0) }}/month</p>
        </div>
      @endforeach
    @else
      <p class="text-center col-span-3 text-gray-400">No listings found.</p>
    @endif
  </section>

  {{-- Add New Rental Button for Authenticated Users --}}
  <div class="max-w-6xl mx-auto mb-8 flex justify-end">
    @auth
      <a href="{{ route('rentals.create') }}" class="btn-primary">+ Add New Rental</a>
    @endauth
  </div>

  {{-- Pagination --}}
  <div class="max-w-6xl mx-auto my-8">
    @if(isset($rentals) && $rentals instanceof \Illuminate\Pagination\LengthAwarePaginator)
      {{ $rentals->withQueryString()->links() }}
    @endif
  </div>

</body>
</html>
