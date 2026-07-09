<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('venues.create_heading') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('venues.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @include('venues.partials.form-fields', ['venue' => null])

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('venues.save') }}</x-primary-button>
                        <a href="{{ route('venues.mine') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
