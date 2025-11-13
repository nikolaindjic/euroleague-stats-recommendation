<template>
    <div>
        <!-- Sync Button -->
        <button
            @click="showModal = true"
            type="button"
            class="text-white bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 dark:focus:ring-green-800 rounded-lg text-sm px-3 py-2 transition-colors font-medium"
        >
            <svg
                :class="{ 'animate-spin': isSyncing }"
                class="w-4 h-4 inline-block mr-1"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
            </svg>
            <span>{{ buttonText }}</span>
        </button>

        <!-- Modal -->
        <Teleport to="body">
            <div
                v-if="showModal"
                @click.self="showModal = false"
                class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center pt-20"
            >
                <div class="relative p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <div class="mt-3">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Sync Game Data</h3>
                            <button
                                @click="showModal = false"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                            >
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Initial Status -->
                        <div v-if="!isSyncing && !syncComplete && !syncError" class="mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                This will sync:
                            </p>
                            <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 mt-2 space-y-1">
                                <li>Complete game schedule (all games)</li>
                                <li>Game stats for played games</li>
                                <li>Player position data</li>
                            </ul>
                        </div>

                        <!-- Progress -->
                        <div v-if="isSyncing" class="mb-4">
                            <div class="flex items-center justify-center mb-4">
                                <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                                Syncing data... This may take a minute.
                            </p>
                        </div>

                        <!-- Success -->
                        <div v-if="syncComplete" class="mb-4">
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800 dark:text-green-400">Sync Complete!</h3>
                                        <div class="mt-2 text-sm text-green-700 dark:text-green-500">
                                            <p><strong>Schedule:</strong> {{ results.schedule.created }} created, {{ results.schedule.skipped }} skipped</p>
                                            <p><strong>Games:</strong> {{ results.games.success }} synced, {{ results.games.failed }} failed</p>
                                            <p><strong>Positions:</strong> {{ results.positions.updated }} updated</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error -->
                        <div v-if="syncError" class="mb-4">
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800 dark:text-red-400">Sync Failed</h3>
                                        <div class="mt-2 text-sm text-red-700 dark:text-red-500">
                                            {{ errorMessage }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end mt-6 space-x-3">
                            <button
                                @click="showModal = false"
                                :disabled="isSyncing"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ isSyncing ? 'Please Wait...' : 'Cancel' }}
                            </button>
                            <button
                                v-if="!syncComplete && !syncError"
                                @click="startSync"
                                :disabled="isSyncing"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Start Sync
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const showModal = ref(false);
const isSyncing = ref(false);
const syncComplete = ref(false);
const syncError = ref(false);
const errorMessage = ref('');
const results = ref({
    schedule: { created: 0, skipped: 0 },
    games: { success: 0, failed: 0 },
    positions: { updated: 0 }
});

const buttonText = computed(() => {
    if (isSyncing.value) return 'Syncing...';
    if (syncComplete.value) return 'Synced!';
    return 'Sync Data';
});

const startSync = async () => {
    isSyncing.value = true;
    syncComplete.value = false;
    syncError.value = false;
    errorMessage.value = '';

    try {
        const response = await fetch('/sync-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        const data = await response.json();

        if (data.success) {
            results.value = data.results;
            syncComplete.value = true;

            // Reset button text after 3 seconds
            setTimeout(() => {
                syncComplete.value = false;
            }, 3000);
        } else {
            syncError.value = true;
            errorMessage.value = data.message || 'Sync failed';
        }
    } catch (error) {
        syncError.value = true;
        errorMessage.value = 'Network error: ' + error.message;
    } finally {
        isSyncing.value = false;
    }
};
</script>

