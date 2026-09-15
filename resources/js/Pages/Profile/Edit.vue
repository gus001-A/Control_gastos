<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Mi perfil" />

    <AuthenticatedLayout title="Mi perfil" subtitle="Tu información de cuenta y seguridad">
        <div class="max-w-2xl space-y-6">
            <Card>
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
            </Card>

            <Card>
                <UpdatePasswordForm />
            </Card>

            <Card>
                <DeleteUserForm />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
