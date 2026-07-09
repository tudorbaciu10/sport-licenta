<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('venues.edit_heading') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('venues.update', $venue) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PATCH')
                    @include('venues.partials.form-fields', ['venue' => $venue])

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('venues.save') }}</x-primary-button>
                            <a href="{{ route('venues.show', $venue) }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('venues.destroy', $venue) }}" class="mt-6 pt-6 border-t border-gray-100"
                      onsubmit="return confirm('{{ __('venues.delete_confirm') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-500 text-sm font-semibold">{{ __('venues.delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
