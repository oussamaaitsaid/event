<template>
  <div class="auth-page">
    <div class="auth-card">

      <!-- Logo -->
      <div class="auth-logo">
        <a href="/" class="logo">⚡ EventHub</a>
      </div>

      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-sub">Sign in to your account to continue</p>

      <!-- Error -->
      <div v-if="form.errors.email" class="alert-error">
        {{ form.errors.email }}
      </div>

      <form @submit.prevent="submit" class="auth-form">
        <!-- Email -->
        <div class="field">
          <label>Email address</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="you@example.com"
            autocomplete="email"
            :class="{ 'input-error': form.errors.email }"
          />
        </div>

        <!-- Password -->
        <div class="field">
          <div class="field-row">
            <label>Password</label>
          </div>
          <div class="input-wrap">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              autocomplete="current-password"
              :class="{ 'input-error': form.errors.password }"
            />
            <button type="button" class="toggle-pw" @click="showPassword = !showPassword">
              {{ showPassword ? '🙈' : '👁️' }}
            </button>
          </div>
        </div>

        <!-- Remember me -->
        <div class="field-check">
          <label class="check-label">
            <input type="checkbox" v-model="form.remember" />
            <span>Remember me</span>
          </label>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-submit" :disabled="form.processing">
          <span v-if="form.processing">Signing in…</span>
          <span v-else>Sign In →</span>
        </button>
      </form>

      <p class="auth-switch">
        Don't have an account?
        <a href="/register">Create one</a>
      </p>
    </div>

    <!-- Background -->
    <div class="auth-bg">
      <div class="bg-orb orb-1"></div>
      <div class="bg-orb orb-2"></div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const showPassword = ref(false);

const form = useForm({
  email:    '',
  password: '',
  remember: false,
});

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
}
</script>

<style>
@import './Login.css';
</style>
