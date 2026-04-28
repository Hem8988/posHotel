@extends('layouts.app')

@section('content')
<div class="p-4 bg-white block dark:bg-gray-800 dark:border-gray-700">
    <div class="flex justify-between mb-4">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">@lang('modules.dashboard.onboarding')</h1>
    </div>

    <div class="mb-4">
        <p class="text-gray-700 dark:text-gray-300">
            @lang('modules.dashboard.onboardingDescription')
        </p>
    </div>

    <div class="space-y-4">
        @php
            // Use $urlHasPublic from controller, or calculate if not provided (fallback for other routes)
            if (!isset($urlHasPublic)) {
                $currentUrl = request()->url();
                $urlPath = parse_url($currentUrl, PHP_URL_PATH);
                $urlHasPublic = str_contains($urlPath, '/public/') || str_ends_with($urlPath, '/public') || str_starts_with($urlPath, '/public');
            }
        @endphp

        <!-- Step 1: Installation -->
        <div class="p-4 border rounded-lg dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <span class="flex items-center justify-center w-8 h-8 text-white bg-green-500 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">1. @lang('modules.dashboard.installation')</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        @lang('modules.dashboard.installationCompleted')
                    </p>
                </div>
            </div>
        </div>

        @if($urlHasPublic)
        <!-- Step 2: Remove Public from URL -->
        <div class="p-4 border rounded-lg dark:border-gray-700 border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/10">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <span class="flex items-center justify-center w-8 h-8 text-white bg-red-500 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">2. Remove "public" from URL</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Your application URL contains "public" which is not recommended for production. Removing "public" from the URL is very important to run the application smoothly and securely.
                    </p>

                </div>
            </div>
        </div>
        @endif

        <!-- Step {{ $urlHasPublic ? '3' : '2' }}: SMTP Configuration -->
        <div class="p-4 border rounded-lg dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <span class="flex items-center justify-center w-8 h-8 text-white {{ !$smtpConfigured ? 'bg-red-500' : 'bg-green-500' }} rounded-full">
                        @if(!$smtpConfigured)
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $urlHasPublic ? '3' : '2' }}. @lang('modules.dashboard.smtpConfiguration')</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        @lang('modules.dashboard.smtpConfigurationDescription')
                    </p>
                    <div class="mt-2">
                        <x-button type='button' wire:navigate href="{{ route('superadmin.superadmin-settings.index').'?tab=email' }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                            @lang('modules.settings.emailSettings')
                        </x-button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step {{ $urlHasPublic ? '4' : '3' }}: CRON Job Configuration -->
        <div class="p-4 border rounded-lg dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 {{ !$cronConfigured ? 'bg-red-500' : 'bg-green-500' }} rounded-full">
                    <span class="flex items-center justify-center w-8 h-8 text-white rounded-full">
                        @if(!$cronConfigured)
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>

                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $urlHasPublic ? '4' : '3' }}. @lang('modules.dashboard.cronJobConfiguration')</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        @lang('modules.dashboard.cronJobConfigurationDescription')
                    </p>
                    @if(!$cronConfigured)
                        <div class="mt-2">
                            <x-cron-message :showModal="true" :modal="true" :showModalOnboarding="true"/>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Step {{ $urlHasPublic ? '5' : '4' }}: Application Name Change -->
        <div class="p-4 border rounded-lg dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 {{ !$appNameChanged ? 'bg-red-500' : 'bg-green-500' }} rounded-full">
                    <span class="flex items-center justify-center w-8 h-8 text-white rounded-full">
                        @if(!$appNameChanged)
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $urlHasPublic ? '5' : '4' }}. @lang('modules.dashboard.applicationNameChange')</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        @lang('modules.dashboard.applicationNameChangeDescription')
                    </p>
                    <div class="mt-2">
                        <x-button type='button' wire:navigate href="{{ route('superadmin.superadmin-settings.index') }}"  class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        @lang('modules.settings.appSettings')</x-button>

                    </div>
                </div>
            </div>
        </div>



    </div>


</div>
@endsection
