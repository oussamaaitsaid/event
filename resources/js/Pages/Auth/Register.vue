<template>
  <div class="auth-page">
    <div class="auth-card">

      <div class="auth-logo">
        <a href="/" class="logo">⚡ EventHub</a>
      </div>

      <!-- Language Switcher -->
      <div class="lang-switcher">
        <button class="lang-btn" :class="{ active: locale === 'en' }" @click="switchLang('en')">EN</button>
        <span class="lang-sep">|</span>
        <button class="lang-btn" :class="{ active: locale === 'fr' }" @click="switchLang('fr')">FR</button>
      </div>

      <h1 class="auth-title">{{ t('register.title') }}</h1>
      <p class="auth-sub">{{ t('register.sub') }}</p>

      <!-- Errors -->
      <div v-if="Object.keys(form.errors).length" class="alert-error">
        <p v-for="(error, field) in form.errors" :key="field">{{ error }}</p>
      </div>

      <form @submit.prevent="submit" class="auth-form">

        <!-- Name -->
        <div class="field">
          <label>{{ t('register.name') }}</label>
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
          <label>{{ t('register.email') }}</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="example@gmail.com"
            autocomplete="email"
            :class="{ 'input-error': form.errors.email }"
          />
        </div>

        <!-- Role -->
        <div class="field">
          <label>{{ t('register.joinAs') }}</label>
          <div class="role-grid">
            <label class="role-option" :class="{ active: form.role === 'user' }">
              <input type="radio" v-model="form.role" value="user" />
              <span class="role-icon">🎟️</span>
              <span class="role-name">{{ t('register.participant') }}</span>
              <span class="role-desc">{{ t('register.participantDesc') }}</span>
            </label>
            <label class="role-option" :class="{ active: form.role === 'organizer' }">
              <input type="radio" v-model="form.role" value="organizer" />
              <span class="role-icon">📋</span>
              <span class="role-name">{{ t('register.organizer') }}</span>
              <span class="role-desc">{{ t('register.organizerDesc') }}</span>
            </label>
          </div>
        </div>

        <!-- Password -->
        <div class="field">
          <label>{{ t('register.password') }}</label>
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
          <label>{{ t('register.confirmPassword') }}</label>
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
          <span v-if="form.processing">{{ t('register.creating') }}</span>
          <span v-else>{{ t('register.submit') }}</span>
        </button>
      </form>

      <p class="auth-switch">
        {{ t('register.haveAccount') }}
        <a href="/login">{{ t('register.signIn') }}</a>
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
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const showPassword  = ref(false);

const form = useForm({
  name:                  '',
  email:                 '',
  password:              '',
  password_confirmation: '',
  role:                  'user',
});

function switchLang(lang) {
  locale.value = lang;
  localStorage.setItem('locale', lang);
}

const strength = computed(() => {
  const pw = form.password;
  if (!pw) return { pct: 0, label: '', cls: '' };
  let score = 0;
  if (pw.length >= 8)  score++;
  if (pw.length >= 12) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^a-zA-Z0-9]/.test(pw)) score++;
  if (score <= 1) return { pct: 25,  label: t('register.weak'),   cls: 'weak' };
  if (score <= 2) return { pct: 50,  label: t('register.fair'),   cls: 'fair' };
  if (score <= 3) return { pct: 75,  label: t('register.good'),   cls: 'good' };
  return              { pct: 100, label: t('register.strong'), cls: 'strong' };
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
