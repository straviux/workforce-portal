<script setup>
import InputError from '@/Components/ui/inputs/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    username: '',
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
    <Head title="Sign In" />

    <div
        class="min-h-screen flex items-center justify-center antialiased"
        style="background: linear-gradient(160deg, #f5f5f7 0%, #e8e8ed 40%, #f0f0f3 70%, #e5e5ea 100%)"
    >
        <div class="relative z-[1] flex flex-col items-center w-full max-w-[340px] p-5">
            <Message v-if="status" severity="success" :closable="false" class="w-full mb-4">
                {{ status }}
            </Message>

            <div class="flex flex-col items-center w-full">
                <h1 class="text-[22px] font-semibold tracking-tight m-0 mb-1 text-center text-gray-900">
                    Workforce Portal
                </h1>
                <p class="text-[13px] mt-0 mb-7 font-normal text-gray-500">Sign in to continue</p>

                <form @submit.prevent="submit" class="w-full flex flex-col items-center gap-2.5">
                    <div class="w-full">
                        <IconField>
                            <InputIcon class="pi pi-user" />
                            <InputText
                                id="username"
                                v-model="form.username"
                                placeholder="Username"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full"
                            />
                        </IconField>
                        <InputError class="mt-1 pl-1 text-xs" :message="form.errors.username" />
                    </div>

                    <div class="w-full">
                        <Password
                            v-model="form.password"
                            placeholder="Password"
                            :feedback="false"
                            toggleMask
                            inputClass="w-full"
                            class="w-full"
                            required
                            autocomplete="current-password"
                        />
                        <InputError class="mt-1 pl-1 text-xs" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between w-full mt-0.5">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-600">
                            <ToggleSwitch v-model="form.remember" size="small" />
                            <span>Remember me</span>
                        </label>
                    </div>

                    <Button
                        type="submit"
                        label="Sign In"
                        icon="pi pi-arrow-right"
                        iconPos="right"
                        :loading="form.processing"
                        :disabled="form.processing"
                        rounded
                        class="w-full mt-2"
                    />
                </form>
            </div>

            <div class="mt-10 flex items-center gap-1.5 text-[11px] text-gray-400">
                <span>Workforce Portal</span>
                <span class="text-sm leading-none">·</span>
                <span>© {{ new Date().getFullYear() }}</span>
            </div>
        </div>
    </div>
</template>
