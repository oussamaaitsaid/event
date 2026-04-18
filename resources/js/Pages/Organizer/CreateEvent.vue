<template>
  <div class="org-page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-inner">
        <a href="/dashboard" class="logo">⚡ EventHub</a>
        <div class="nav-right">
          <a href="/organizer/events" class="btn-outline">← {{ t('org.myEvents') }}</a>
          <span class="role-chip">{{ t('org.organizer') }}</span>
          <div class="lang-switcher">
            <button class="lang-btn" :class="{ active: locale === 'en' }" @click="switchLang('en')">EN</button>
            <span class="lang-sep">|</span>
            <button class="lang-btn" :class="{ active: locale === 'fr' }" @click="switchLang('fr')">FR</button>
          </div>
          <button @click="logout" class="btn-logout">{{ t('org.logout') }}</button>
        </div>
      </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
      <div class="page-header-inner">
        <h1 class="page-title">{{ t('org.createNewEvent') }}</h1>
        <p class="page-sub">{{ t('org.createSubtitle') }}</p>
      </div>
    </div>

    <!-- FORM -->
    <div class="form-wrapper">
      <form @submit.prevent="submit" class="event-form">

        <div v-if="form.recentlySuccessful" class="alert-success">
          ✅ {{ t('org.eventCreated') }}
        </div>

        <div v-if="Object.keys(form.errors).length" class="alert-error">
          <p v-for="(error, field) in form.errors" :key="field">⚠ {{ error }}</p>
        </div>

        <div class="field">
          <label>{{ t('org.eventTitle') }} <span class="required">*</span></label>
          <input v-model="form.title" type="text" placeholder="e.g. Laravel & Vue.js Workshop 2026" :class="{ 'input-error': form.errors.title }" />
          <span class="field-error" v-if="form.errors.title">{{ form.errors.title }}</span>
        </div>

        <div class="field">
          <label>{{ t('org.description') }} <span class="required">*</span></label>
          <textarea v-model="form.description" rows="5" :placeholder="t('org.descriptionPlaceholder')" :class="{ 'input-error': form.errors.description }"></textarea>
          <span class="char-count">{{ form.description.length }} {{ t('org.characters') }}</span>
          <span class="field-error" v-if="form.errors.description">{{ form.errors.description }}</span>
        </div>

        <div class="fields-row">
          <div class="field">
            <label>{{ t('org.dateTime') }} <span class="required">*</span></label>
            <input v-model="form.date" type="datetime-local" :min="minDate" :class="{ 'input-error': form.errors.date }" />
            <span class="field-error" v-if="form.errors.date">{{ form.errors.date }}</span>
          </div>
          <div class="field">
            <label>{{ t('org.location') }} <span class="required">*</span></label>
            <input v-model="form.location" type="text" placeholder="e.g. Casablanca Tech Hub or Online (Zoom)" :class="{ 'input-error': form.errors.location }" />
            <span class="field-error" v-if="form.errors.location">{{ form.errors.location }}</span>
          </div>
        </div>

        <div class="field capacity-field">
          <label>{{ t('org.maxCapacity') }} <span class="required">*</span></label>
          <div class="capacity-wrap">
            <input v-model.number="form.capacity" type="number" min="1" max="10000" placeholder="e.g. 100" :class="{ 'input-error': form.errors.capacity }" />
            <div class="capacity-hint">
              <span class="hint-icon">👥</span>
              <span>{{ t('org.maxParticipants') }}</span>
            </div>
          </div>
          <span class="field-error" v-if="form.errors.capacity">{{ form.errors.capacity }}</span>
        </div>

        <div class="preview-section" v-if="form.title || form.date || form.location">
          <h3 class="preview-title">{{ t('org.preview') }}</h3>
          <div class="preview-card">
            <div class="preview-badge">{{ t('org.open') }}</div>
            <h4>{{ form.title || t('org.yourEventTitle') }}</h4>
            <p class="preview-desc">{{ truncate(form.description, 80) || t('org.yourDescription') }}</p>
            <div class="preview-meta">
              <span v-if="form.date">📅 {{ formatPreviewDate(form.date) }}</span>
              <span v-if="form.location">📍 {{ form.location }}</span>
              <span v-if="form.capacity">👥 {{ form.capacity }} {{ t('org.capacity') }}</span>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <a href="/organizer/events" class="btn-cancel">{{ t('org.cancel') }}</a>
          <button type="submit" class="btn-submit" :disabled="form.processing">
            <span v-if="form.processing">{{ t('org.creating') }}</span>
            <span v-else>{{ t('org.publishEvent') }}</span>
          </button>
        </div>

      </form>
    </div>

    <footer class="footer">
      <p>© 2026 EventHub · {{ t('org.builtWith') }}</p>
    </footer>

  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const form = useForm({
  title:       '',
  description: '',
  date:        '',
  location:    '',
  capacity:    '',
});

const minDate = computed(() => {
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  return now.toISOString().slice(0, 16);
});

function switchLang(lang) {
  locale.value = lang;
  localStorage.setItem('locale', lang);
}
function submit() { form.post('/organizer/events'); }
function logout() { router.post('/logout'); }
function truncate(text, length) {
  return text?.length > length ? text.slice(0, length) + '…' : text;
}
function formatPreviewDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
    weekday: 'short', month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit',
  });
}
</script>

<style scoped>
@import './css/CreateEvent.css';
</style>
