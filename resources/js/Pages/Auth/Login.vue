<template>
  <div class="auth-page">
    <div class="auth-card">

      <!-- Logo -->
      <div class="auth-logo">
        <a href="/" class="logo">⚡ EventHub</a>
      </div>

      <!-- Language Switcher -->
      <div class="lang-switcher">
        <button class="lang-btn" :class="{ active: locale === 'en' }" @click="switchLang('en')">EN</button>
        <span class="lang-sep">|</span>
        <button class="lang-btn" :class="{ active: locale === 'fr' }" @click="switchLang('fr')">FR</button>
      </div>

      <h1 class="auth-title">{{ t('login.title') }}</h1>
      <p class="auth-sub">{{ t('login.sub') }}</p>

      <!-- Error -->
      <div v-if="form.errors.email" class="alert-error">
        {{ form.errors.email }}
      </div>

      <form @submit.prevent="submit" class="auth-form">
        <!-- Email -->
        <div class="field">
          <label>{{ t('login.email') }}</label>
          <input
            v-model="form.email"
            type="email"
            :placeholder="t('login.emailPlaceholder')"
            autocomplete="email"
            :class="{ 'input-error': form.errors.email }"
          />
        </div>

        <!-- Password -->
        <div class="field">
          <div class="field-row">
            <label>{{ t('login.password') }}</label>
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
            <span>{{ t('login.remember') }}</span>
          </label>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-submit" :disabled="form.processing">
          <span v-if="form.processing">{{ t('login.signingIn') }}</span>
          <span v-else>{{ t('login.submit') }}</span>
        </button>
      </form>

      <p class="auth-switch">
        {{ t('login.noAccount') }}
        <a href="/register">{{ t('login.createOne') }}</a>
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
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const showPassword = ref(false);

const form = useForm({
  email:    '',
  password: '',
  remember: false,
});

function switchLang(lang) {
  locale.value = lang;
  localStorage.setItem('locale', lang);
}

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
}
</script>

<style>
@import './Login.css';
</style>
