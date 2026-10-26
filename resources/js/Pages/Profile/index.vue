<template>

    <Head title="My Profile" />

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-bold text-surface-800 dark:text-surface-50">My Profile</h1>
            <p class="text-sm text-surface-400 mt-0.5">Update your personal details, photo, and password.</p>
        </div>
    </div>

    <div class="space-y-4 max-w-3xl">
        <!-- Photo -->
        <div class="ios-card p-6">
            <p class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-4">Profile Photo</p>
            <div class="flex items-center gap-5 flex-wrap">
                <div
                    class="w-20 h-20 rounded-full bg-linear-to-br from-blue-500 to-purple-600 flex items-center justify-center shrink-0 overflow-hidden">
                    <img v-if="profile.has_profile_photo && profile.profile_photo_url" :src="profile.profile_photo_url"
                        class="w-20 h-20 object-cover" />
                    <AppIcon v-else name="User" :size="24" class="text-white" />
                </div>

                <div class="flex-1 min-w-[220px] space-y-2">
                    <input ref="fileInputRef" type="file" accept="image/*" class="w-full text-sm"
                        @change="onFileSelected" />
                    <p class="text-xs text-surface-400">Supported: JPEG, PNG, WebP (max 5 MB)</p>
                    <div class="flex items-center gap-2 pt-1">
                        <Button :label="uploadAction.label" class="rounded" size="small"
                            :loading="uploadAction.loading"
                            :disabled="uploadAction.disabled || !selectedFile || removeAction.disabled"
                            :class="uploadAction.morphClass"
                            @click="uploadPhoto"><template #icon><AppIcon v-if="uploadAction.state === 'success'" name="Check" /><AppIcon v-else name="Upload" /></template></Button>
                        <Button v-if="profile.has_profile_photo" :label="removeAction.label" severity="danger"
                            text class="rounded" size="small" :loading="removeAction.loading"
                            :disabled="removeAction.disabled || uploadAction.disabled"
                            :class="removeAction.morphClass"
                            @click="removePhoto"><template #icon><AppIcon v-if="removeAction.state === 'success'" name="Check" /><AppIcon v-else name="Trash2" /></template></Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Info -->
        <div class="ios-card p-6">
            <p class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-4">Profile Information</p>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="ios-form-group">
                        <label class="ios-label">Full Name <span class="text-red-500">*</span></label>
                        <InputText v-model="infoForm.name" class="w-full" size="small" />
                        <InputError :message="firstInfoError('name')" />
                    </div>
                    <div class="ios-form-group">
                        <label class="ios-label">Username</label>
                        <InputText :model-value="profile.username" class="w-full" size="small" disabled />
                    </div>
                </div>
                <div class="ios-form-group">
                    <label class="ios-label">Email</label>
                    <InputText v-model="infoForm.email" class="w-full" size="small" />
                    <InputError :message="firstInfoError('email')" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="ios-form-group">
                        <label class="ios-label">Office</label>
                        <InputText v-model="infoForm.office" class="w-full" size="small" />
                        <InputError :message="firstInfoError('office')" />
                    </div>
                    <div class="ios-form-group">
                        <label class="ios-label">Designation</label>
                        <InputText v-model="infoForm.designation" class="w-full" size="small" />
                        <InputError :message="firstInfoError('designation')" />
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-5">
                <Button :label="infoAction.label" class="rounded" size="small" :loading="infoAction.loading"
                    :disabled="infoAction.disabled || !infoForm.name.trim()" :class="infoAction.morphClass"
                    @click="submitInfo"><template #icon><AppIcon name="Check" /></template></Button>
            </div>
        </div>

        <!-- Change Password -->
        <div class="ios-card p-6">
            <p class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-4">Change Password</p>
            <div class="space-y-4">
                <div class="ios-form-group">
                    <label class="ios-label">Current Password <span class="text-red-500">*</span></label>
                    <InputText v-model="passwordForm.current_password" type="password" class="w-full" size="small" />
                    <InputError :message="firstPasswordError('current_password')" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="ios-form-group">
                        <label class="ios-label">New Password <span class="text-red-500">*</span></label>
                        <InputText v-model="passwordForm.password" type="password" class="w-full" size="small" />
                        <InputError :message="firstPasswordError('password')" />
                    </div>
                    <div class="ios-form-group">
                        <label class="ios-label">Confirm New Password <span class="text-red-500">*</span></label>
                        <InputText v-model="passwordForm.password_confirmation" type="password" class="w-full"
                            size="small" placeholder="Repeat new password" />
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-5">
                <Button :label="passwordAction.label" class="rounded" size="small"
                    :loading="passwordAction.loading"
                    :disabled="passwordAction.disabled || !passwordForm.current_password || !passwordForm.password || !passwordForm.password_confirmation"
                    :class="passwordAction.morphClass"
                    @click="submitPassword"><template #icon><AppIcon v-if="passwordAction.state === 'success'" name="Check" /><AppIcon v-else name="Lock" /></template></Button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import InputError from '@/Components/ui/inputs/InputError.vue';
import { useActionButton } from '@/Composables/useActionButton';

defineOptions({ layout: WorkforceLayout });

const toast = useToast();

const profile = reactive({
    name: '',
    username: '',
    email: '',
    office: '',
    designation: '',
    profile_photo_url: null,
    has_profile_photo: false,
});

const infoForm = reactive({ name: '', email: '', office: '', designation: '' });
const infoErrors = ref({});
const infoAction = useActionButton({
    idleLabel: 'Save Changes',
    processingLabel: 'Saving...',
    successLabel: 'Saved',
});

const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' });
const passwordErrors = ref({});
const passwordAction = useActionButton({
    idleLabel: 'Update Password',
    processingLabel: 'Updating...',
    successLabel: 'Updated',
});

const selectedFile = ref(null);
const fileInputRef = ref(null);
const uploadAction = useActionButton({
    idleLabel: 'Upload Photo',
    processingLabel: 'Uploading...',
    successLabel: 'Uploaded',
});
// "Remove" deletes the photo, so it takes the Delete verb's pair.
const removeAction = useActionButton({
    idleLabel: 'Remove',
    processingLabel: 'Deleting...',
    successLabel: 'Deleted',
});

function normalizeErrors(errors = {}) {
    return Object.fromEntries(
        Object.entries(errors).map(([field, messages]) => [field, Array.isArray(messages) ? messages[0] : messages]),
    );
}

function firstInfoError(field) {
    return infoErrors.value[field] ?? '';
}

function firstPasswordError(field) {
    return passwordErrors.value[field] ?? '';
}

async function load() {
    try {
        const { data } = await axios.get('/api/profile');
        Object.assign(profile, data.data);
        infoForm.name = profile.name;
        infoForm.email = profile.email ?? '';
        infoForm.office = profile.office ?? '';
        infoForm.designation = profile.designation ?? '';
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not load your profile.', life: 3000 });
    }
}

async function submitInfo() {
    infoErrors.value = {};

    try {
        await infoAction.run(async () => {
            const payload = {
                name: infoForm.name,
                email: infoForm.email || null,
                office: infoForm.office || null,
                designation: infoForm.designation || null,
            };
            await axios.put('/api/profile', payload);
            Object.assign(profile, payload);
            router.reload({ only: ['auth'] });
            toast.add({ severity: 'success', summary: 'Saved', detail: 'Profile updated.', life: 3000 });
        });
    } catch (error) {
        if (error.response?.status === 422) {
            infoErrors.value = normalizeErrors(error.response.data.errors);
            toast.add({ severity: 'warn', summary: 'Validation Error', detail: 'Please check the form.', life: 3500 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message || 'Could not update profile.', life: 3500 });
        }
    }
}

async function submitPassword() {
    passwordErrors.value = {};

    try {
        await passwordAction.run(async () => {
            await axios.put('/api/profile/password', { ...passwordForm });
            passwordForm.current_password = '';
            passwordForm.password = '';
            passwordForm.password_confirmation = '';
            toast.add({ severity: 'success', summary: 'Saved', detail: 'Password updated.', life: 3000 });
        });
    } catch (error) {
        if (error.response?.status === 422) {
            passwordErrors.value = normalizeErrors(error.response.data.errors);
            toast.add({ severity: 'warn', summary: 'Validation Error', detail: 'Please check the form.', life: 3500 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message || 'Could not update password.', life: 3500 });
        }
    }
}

function onFileSelected(event) {
    selectedFile.value = event.target.files?.[0] ?? null;
}

async function uploadPhoto() {
    if (!selectedFile.value) return;

    try {
        await uploadAction.run(async () => {
            const formData = new FormData();
            formData.append('photo', selectedFile.value);

            const { data } = await axios.post('/api/profile/photo', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            profile.profile_photo_url = data.data.profile_photo_url + '?t=' + Date.now();
            profile.has_profile_photo = data.data.has_profile_photo;
            selectedFile.value = null;
            if (fileInputRef.value) fileInputRef.value.value = '';
            router.reload({ only: ['auth'] });

            toast.add({ severity: 'success', summary: 'Updated', detail: 'Profile photo updated.', life: 3000 });
        });
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Upload Failed', detail: error.response?.data?.message || 'Could not upload photo.', life: 3500 });
    }
}

async function removePhoto() {
    try {
        await removeAction.run(async () => {
            const { data } = await axios.delete('/api/profile/photo');
            profile.profile_photo_url = data.data.profile_photo_url;
            profile.has_profile_photo = data.data.has_profile_photo;
            selectedFile.value = null;
            if (fileInputRef.value) fileInputRef.value.value = '';
            router.reload({ only: ['auth'] });

            toast.add({ severity: 'success', summary: 'Removed', detail: 'Profile photo removed.', life: 3000 });
        });
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message || 'Could not remove photo.', life: 3500 });
    }
}

onMounted(load);
</script>
