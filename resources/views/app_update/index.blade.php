@extends('layouts.app')

@section('content')


<div>
    <div class="grid grid-cols-1 px-4 pt-6 xl:grid-cols-2 xl:gap-4 dark:bg-gray-900">
        <div class="mb-4 col-span-full xl:mb-2">
            <h1 class="text-base font-semibold text-gray-900 dark:text-white">@lang('menu.appUpdate')</h1>
        </div>
    </div>



    <div class="flex w-full flex-col p-4">
        <x-alert type="success" icon="check-circle">
            <h4 class="font-semibold">System is up to date</h4>
            <p>You are currently running the latest version of the application.</p>
        </x-alert>
    </div>


</div>


@endsection


@push('scripts')
    @include('vendor.froiden-envato.update.update_script')
@endpush
