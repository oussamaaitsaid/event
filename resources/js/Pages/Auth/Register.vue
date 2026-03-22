<template>
  <div class="auth-page">
    <div class="auth-card">

      <div class="auth-logo">
        <a href="/" class="logo">⚡ EventHub</a>
      </div>

      <h1 class="auth-title">Create your account</h1>
      <p class="auth-sub">Join EventHub and start exploring events</p>

      <!-- Errors -->
      <div v-if="Object.keys(form.errors).length" class="alert-error">
        <p v-for="(error, field) in form.errors" :key="field">{{ error }}</p>
      </div>

      <form @submit.prevent="submit" class="auth-form">

        <!-- Name -->
        <div class="field">
          <label>Full Name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="John Doe"
            autocomplete="name"
            :class="{ 'input-error': form.errors.name }"
          />
        </div>

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

        <!-- Role -->
        <div class="field">
          <label>I want to join as</label>
          <div class="role-grid">
            <label
              class="role-option"
              :class="{ active: form.role === 'user' }"
            >
              <input type="radio" v-model="form.role" value="user" />
              <span class="role-icon">🎟️</span>
              <span class="role-name">Participant</span>
              <span class="role-desc">Browse &amp; join events</span>
            </label>
            <label
              class="role-option"
              :class="{ active: form.role === 'organizer' }"
            >
              <input type="radio" v-model="form.role" value="organizer" />
              <span class="role-icon">📋</span>
              <span class="role-name">Organizer</span>
              <span class="role-desc">Create &amp; manage events</span>
            </label>
          </div>
        </div>

        <!-- Password -->
        <div class="field">
          <label>Password</label>
          <div class="input-wrap">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Min. 8 characters"
              autocomplete="new-password"
              :class="{ 'input-error': form.errors.password }"
            />
            <button type="button" class="toggle-pw" @click="showPassword = !showPassword">
              {{ showPassword ? '🙈' : '👁️' }}
            </button>
          </div>
          <!-- Password strength -->
          <div class="strength-bar" v-if="form.password">
            <div
              class="strength-fill"
              :style="{ width: strength.pct + '%' }"
              :class="strength.cls"
            ></div>
          </div>
          <span class="strength-label" v-if="form.password" :class="strength.cls">
            {{ strength.label }}
          </span>
        </div>

        <!-- Confirm Password -->
        <div class="field">
          <label>Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Repeat your password"
            autocomplete="new-password"
            :class="{ 'input-error': form.errors.password }"
          />
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-submit" :disabled="form.processing">
          <span v-if="form.processing">Creating account…</span>
          <span v-else>Create Account →</span>
        </button>
      </form>

      <p class="auth-switch">
        Already have an account?
        <a href="/login">Sign in</a>
      </p>
    </div>

    <div class="auth-bg">
      <div class="bg-orb orb-1"></div>
      <div class="bg-orb orb-2"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const showPassword = ref(false);

const form = useForm({
  name:                  '',
  email:                 '',
  password:              '',
  password_confirmation: '',
  role:                  'user',
});

const strength = computed(() => {
  const pw = form.password;
  if (!pw) return { pct: 0, label: '', cls: '' };
  let score = 0;
  if (pw.length >= 8)  score++;
  if (pw.length >= 12) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^a-zA-Z0-9]/.test(pw)) score++;
  if (score <= 1) return { pct: 25,  label: 'Weak',   cls: 'weak' };
  if (score <= 2) return { pct: 50,  label: 'Fair',   cls: 'fair' };
  if (score <= 3) return { pct: 75,  label: 'Good',   cls: 'good' };
  return              { pct: 100, label: 'Strong', cls: 'strong' };
});

function submit() {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
}
</script>

<style>
@import './Register.css';
</style>
