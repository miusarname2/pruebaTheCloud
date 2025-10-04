<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { useAuth } from '@/composables/useAuth';
import { ref } from 'vue';

const { login: apiLogin } = useAuth();
const apiLoginError = ref<string>('');

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const handleSubmit = async (data: any, { setErrors }: any) => {
    try {
        // First, call API login to get token
        await apiLogin(data.email, data.password);
        apiLoginError.value = '';

        // Then, proceed with Inertia login for web session
        return true; // Let Inertia handle the form submission
    } catch (error: any) {
        apiLoginError.value = error.response?.data?.message || 'Login failed';
        setErrors({ email: apiLoginError.value });
        return false; // Prevent Inertia submission
    }
};

const onSubmit = async () => {
    // This will be called on form submit, but since we're using Inertia Form,
    // perhaps better to modify the AuthenticatedSessionController or find another way.

    // Actually, since the form is bound to AuthenticatedSessionController.store.form(),
    // the submit is handled by Inertia. To intercept, I need to override the submit.

    // Perhaps it's easier to call API login after successful Inertia login.
    // But since Inertia redirects on success, I can call it in a watcher or something.

    // For now, let's try to call API login on submit, and if successful, then submit the form.

    const form = document.querySelector('form') as HTMLFormElement;
    const formData = new FormData(form);
    const email = formData.get('email') as string;
    const password = formData.get('password') as string;

    try {
        await apiLogin(email, password);
    } catch (error: any) {
        apiLoginError.value = error.response?.data?.message || 'Login failed';
    }
};
</script>

<template>
    <AuthBase
        title="Log in to your account"
        description="Enter your email and password below to log in"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="AuthenticatedSessionController.store.form()"
            :reset-on-success="['password']"
            @submit.prevent="onSubmit"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email || apiLoginError" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Log in
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <TextLink :href="register()" :tabindex="5">Sign up</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
