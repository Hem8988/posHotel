<x-dialog-modal wire:model.live="showRaiseSupportTicketModal">
    <x-slot name="title">
        <h2 class="text-lg">@lang('superadmin.raiseSupportTicket')</h2>
    </x-slot>

    <x-slot name="content">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Header -->
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">Choose Your Support Option</h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Select the support service that best fits your needs</p>
            </div>
            
            <!-- Support Options -->
            <div class="space-y-6">
                <!-- Company Support Card -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl p-6 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center mb-4">
                        <div class="bg-skin-base/10 p-2 rounded-lg mr-3">
                            <svg class="h-8 w-8 text-skin-base" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ config('app.name') }} Support</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Dedicated assistance for your business</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="space-y-2">
                            <div class="flex items-center text-sm">
                                <svg class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-400">Response time: Within 24 hours</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <svg class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-400">Priority email support</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center text-sm">
                                <svg class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-400">Technical documentation</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <svg class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-zinc-600 dark:text-zinc-400">System maintenance</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex">
                        <a href="mailto:support@hungryhippo.com" target="_blank" 
                           class="inline-flex items-center px-6 py-2 bg-skin-base hover:bg-skin-base/90 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
            </div>

            <div class="flex w-full pb-4 space-x-4 rtl:space-x-reverse mt-6 justify-end">
                <x-button-cancel  wire:click="$set('showRaiseSupportTicketModal', false)">@lang('app.close')</x-button-cancel>
            </div>
        </div>
    </x-slot>
</x-dialog-modal> 