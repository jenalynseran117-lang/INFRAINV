@can('admin-access')
@extends('layouts.Admin.app')

@section('content')


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session()->has('success'))
<script>

</script>
@endif


this is issue material

<form action="#" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <!-- Business Permit -->
    <div class="relative">
        <label for="business_permit" class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="heroicon-outline-document-text mr-2"></i>
            Business Permit (JPG, PNG, PDF)
        </label>
        <input id="business_permit" type="file" name="business_permit" accept=".jpg,.jpeg,.png,.pdf" required class="w-full px-4 py-3 border border-gray-300 rounded-lg
                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                          transition-all duration-200
                          file:mr-4 file:py-2 file:px-4 file:rounded-lg
                          file:border-0 file:text-sm file:font-semibold
                          file:bg-blue-50 file:text-blue-700
                          hover:file:bg-blue-100">
    </div>

    <!-- Submit Button -->
    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold py-3 px-4 rounded-lg
                   hover:from-teal-600 hover:to-teal-700
                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500
                   transform transition-all duration-200 hover:scale-105
                   shadow-lg hover:shadow-xl">
        <i class="heroicon-outline-paper-airplane mr-2"></i>
        Submit
    </button>

</form>

@endsection
@endcan