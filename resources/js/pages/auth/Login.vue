<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    login: '',
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
    <AuthBase title="Log in to your account" description="Enter your username and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="login">Username</Label>
                    <Input
                        id="login"
                        type="text"
                        required
                        autofocus
                        autocomplete="username"
                        v-model="form.login"
                        placeholder="username"
                    />
                    <InputError :message="form.errors.login" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <div class="relative">
                        <Input
                            id="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            v-model="form.password"
                            placeholder="Password"
                        />
                        <TextLink 
                            v-if="canResetPassword" 
                            :href="route('password.request')" 
                            class="absolute right-0 top-0 text-sm -mt-6"
                        >
                            Forgot password?
                        </TextLink>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model:checked="form.remember" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button type="submit" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <span v-else>Log in</span>
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account? please contact <span class="font-medium text-purple-900 items-center">
                    <img src="/images/logo/itc-logo.png" class="inline-block h-4 w-4" alt=""> IT Center
                </span>.
            </div>
        </form>
    </AuthBase>
</template>