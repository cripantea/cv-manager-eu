<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Sign in — CV Manager EU" />

    <div class="min-h-screen flex">

        <!-- ── Left branded panel ─────────────────────────────────── -->
        <div class="hidden lg:flex lg:w-[58%] relative overflow-hidden flex-col" style="background: linear-gradient(145deg, #1a3461 0%, #112040 55%, #0b1628 100%);">

            <!-- Dot-grid background -->
            <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="dotgrid" x="0" y="0" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="1.5" cy="1.5" r="1.2" fill="white" fill-opacity="0.07"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#dotgrid)"/>
            </svg>

            <!-- Soft radial glows -->
            <div class="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full pointer-events-none" style="background: radial-gradient(circle, rgba(74,159,213,0.18) 0%, transparent 68%)"></div>
            <div class="absolute -bottom-32 -right-32 w-[520px] h-[520px] rounded-full pointer-events-none" style="background: radial-gradient(circle, rgba(74,159,213,0.14) 0%, transparent 68%)"></div>

            <!-- Decorative network lines (bottom) -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 900 320" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <polyline points="0,250  180,130  360,200  540,80  720,160  900,100" fill="none" stroke="#4a9fd5" stroke-width="1.2" opacity="0.25"/>
                <polyline points="0,290  180,170  360,240  540,120  720,200  900,140" fill="none" stroke="#4a9fd5" stroke-width="0.7" opacity="0.13"/>
                <circle cx="180" cy="130" r="3.5" fill="#4a9fd5" opacity="0.45"/>
                <circle cx="360" cy="200" r="3.5" fill="#4a9fd5" opacity="0.45"/>
                <circle cx="540" cy="80"  r="3.5" fill="#4a9fd5" opacity="0.45"/>
                <circle cx="720" cy="160" r="3.5" fill="#4a9fd5" opacity="0.45"/>
                <!-- faint vertical drops from each node -->
                <line x1="180" y1="130" x2="180" y2="320" stroke="#4a9fd5" stroke-width="0.6" opacity="0.12"/>
                <line x1="360" y1="200" x2="360" y2="320" stroke="#4a9fd5" stroke-width="0.6" opacity="0.12"/>
                <line x1="540" y1="80"  x2="540" y2="320" stroke="#4a9fd5" stroke-width="0.6" opacity="0.12"/>
                <line x1="720" y1="160" x2="720" y2="320" stroke="#4a9fd5" stroke-width="0.6" opacity="0.12"/>
            </svg>

            <!-- Thin diagonal accent bar (top-right) -->
            <div class="absolute top-0 right-0 w-1 h-full opacity-20" style="background: linear-gradient(to bottom, #4a9fd5, transparent)"></div>

            <!-- Panel content -->
            <div class="relative z-10 flex flex-col h-full justify-between p-14">

                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18);">
                        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 5v7a7 7 0 0 0 14 0V5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="9" y1="3" x2="9" y2="5" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <line x1="15" y1="3" x2="15" y2="5" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-base leading-none tracking-wide">CV Manager EU</p>
                        <p class="text-blue-300 text-[10px] font-semibold tracking-[0.18em] mt-0.5 uppercase">&nbsp;</p>
                    </div>
                </div>

                <!-- Hero copy -->
                <div class="max-w-md">
                    <div class="inline-flex items-center gap-2 mb-5 px-3 py-1 rounded-full text-xs font-medium text-blue-200" style="background: rgba(74,159,213,0.15); border: 1px solid rgba(74,159,213,0.25);">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                        DIGIT-TM II · EU Framework Contracts
                    </div>
                    <h1 class="text-white font-bold leading-tight mb-4" style="font-size: clamp(1.9rem, 3vw, 2.6rem);">
                        Your professional CV,<br/>ready for European<br/>institutions.
                    </h1>
                    <p class="text-blue-200 text-[0.93rem] leading-relaxed">
                        Compile, manage and export your CV in the standardised DIGIT-TM II format required for EU Commission framework contracts — in minutes.
                    </p>

                    <!-- Feature pills -->
                    <div class="flex flex-wrap gap-2 mt-7">
                        <span v-for="f in ['AI-assisted import', 'DOCX export', 'Expertise tracking', 'Secure &amp; private']" :key="f"
                              class="inline-flex items-center gap-1.5 text-xs text-blue-100 px-3 py-1 rounded-full"
                              style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1);">
                            <svg class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span v-html="f"></span>
                        </span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center gap-4">
                    <p class="text-blue-400 text-xs">© 2025 Fusion Soft.</p>
                    <span class="w-px h-3 bg-blue-700"></span>
                    <p class="text-blue-400 text-xs">All rights reserved</p>
                </div>
            </div>
        </div>

        <!-- ── Right form panel ───────────────────────────────────── -->
        <div class="w-full lg:w-[42%] flex flex-col items-center justify-center bg-white px-8 sm:px-14 py-14">

            <!-- Mobile header -->
            <div class="mb-8 text-center lg:hidden">
                <p class="text-gray-900 font-bold text-xl">CV Manager EU</p>
                <p class="text-blue-700 text-xs font-semibold tracking-widest uppercase mt-0.5">Uni Systems</p>
            </div>

            <div class="w-full max-w-[340px]">

                <!-- Heading -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
                    <p class="text-gray-400 text-sm mt-1">Sign in to access your CV</p>
                </div>

                <!-- Status message (e.g. password reset success) -->
                <div v-if="status" class="mb-5 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <div>
                        <InputLabel for="email" value="Email address" class="text-xs font-semibold text-gray-600 uppercase tracking-wide" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1.5 block w-full text-sm"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="you@example.com"
                        />
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" class="text-xs font-semibold text-gray-600 uppercase tracking-wide" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1.5 block w-full text-sm"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                        />
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="text-sm text-gray-500">Remember me</span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm font-medium text-blue-700 hover:text-blue-900 transition-colors"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="signin-btn w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg text-white text-sm font-semibold transition-opacity disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        {{ form.processing ? 'Signing in…' : 'Sign in' }}
                    </button>

                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.signin-btn {
    background-color: #1F3864;
}
.signin-btn:hover:not(:disabled) {
    background-color: #162a50;
}
</style>
