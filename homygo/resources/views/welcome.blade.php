<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to HomyGo</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

  <!-- Header -->
  <header class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <div class="flex items-center gap-3">
        <img src="{{ asset('images/homygo-mascot.png') }}" alt="HomyGo" class="h-10 w-10">
        <span class="text-2xl font-bold text-gray-800">HomyGo</span>
      </div>
      <nav class="space-x-4">
        <a href="#" class="text-gray-600 hover:text-gray-800">Dashboard</a>
        <a href="#" class="text-gray-600 hover:text-gray-800">Listings</a>
        <a href="#" class="text-gray-600 hover:text-gray-800">Login</a>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="bg-gray-50 py-20">
    <div class="max-w-5xl mx-auto text-center px-6">
      <h1 class="text-4xl font-extrabold mb-4">Find Your Perfect Rental in Cagayan de Oro</h1>
      <p class="text-lg mb-8 text-gray-600">Hassle-free. Easy to use. Exclusively for Kagay-anons.</p>
      <a href="{{ route('rental.index') }}" class="bg-gray-800 text-white px-6 py-3 rounded-full shadow hover:bg-gray-700 transition duration-300">
        Browse Rentals Now
      </a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-white border-t mt-20">
    <div class="max-w-7xl mx-auto px-4 py-6 text-center text-sm text-gray-500">
      &copy; {{ date('Y') }} HomyGo. All rights reserved.
    </div>
  </footer>

</body>
</html>
