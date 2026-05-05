<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    token: { type: String, required: true },
    email: { type: String, required: true },
});

const resetUrl = `/reset-password/${props.token}?email=${encodeURIComponent(props.email)}`;
</script>

<template>
    <GuestLayout>
        <Head title="Imposta la tua password" />

        <div class="text-center space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Benvenuto!</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Prima di continuare, imposta la tua password per accedere al portale.
                </p>
            </div>

            <a
                :href="resetUrl"
                class="inline-block w-full px-4 py-2 bg-[#1F3864] text-white text-sm font-semibold rounded-lg text-center hover:bg-[#162a4e] transition-colors"
            >
                Imposta la tua password
            </a>
        </div>
    </GuestLayout>
</template>
