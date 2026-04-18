<template>
  <div class="org-page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-inner">
        <a href="/organizer/events" class="logo">⚡ EventHub</a>
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
        <div class="header-top">
          <span class="status-badge" :class="event.status">{{ event.status }}</span>
        </div>
        <h1 class="page-title">{{ t('org.editEvent') }}</h1>
        <p class="page-sub">{{ event.title }}</p>
      </div>
    </div>

    <!-- FORM -->
    <div class="form-wrapper">
      <form @submit.prevent="submit" class="event-form">

        <div v-if="form.recentlySuccessful" class="alert-success">
          ✅ {{ t('org.eventUpdated') }}
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
            <input v-model="form.date" type="datetime-local" :class="{ 'input-error': form.errors.date }" />
            <span class="field-error" v-if="form.errors.date">{{ form.errors.date }}</span>
          </div>
          <div class="field">
            <label>{{ t('org.location') }} <span class="required">*</span></label>
            <input v-model="form.location" type="text" placeholder="e.g. Casablanca Tech Hub" :class="{ 'input-error': form.errors.location }" />
            <span class="field-error" v-if="form.errors.location">{{ form.errors.location }}</span>
          </div>
        </div>

        <div class="fields-row">
          <div class="field">
            <label>{{ t('org.maxCapacity') }} <span class="required">*</span></label>
            <input v-model.number="form.capacity" type="number" min="1" max="10000" placeholder="e.g. 100" :class="{ 'input-error': form.errors.capacity }" />
            <span class="field-error" v-if="form.errors.capacity">{{ form.errors.capacity }}</span>
          </div>
          <div class="field">
            <label>{{ t('org.eventStatus') }} <span class="required">*</span></label>
            <select v-model="form.status" :class="{ 'input-error': form.errors.status }">
              <option value="open">{{ t('org.statusOpen') }}</option>
              <option value="closed">{{ t('org.statusClosed') }}</option>
              <option value="finished">{{ t('org.statusFinished') }}</option>
            </select>
            <span class="field-error" v-if="form.errors.status">{{ form.errors.status }}</span>
          </div>
        </div>

        <div class="preview-section">
          <h3 class="preview-label">{{ t('org.preview') }}</h3>
          <div class="preview-card">
            <span class="preview-badge" :class="form.status">{{ form.status }}</span>
            <h4>{{ form.title || t('org.yourEventTitle') }}</h4>
            <p class="preview-desc">{{ truncate(form.description, 100) || t('org.yourDescription') }}</p>
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
            <span v-if="form.processing">{{ t('org.saving') }}</span>
            <span v-else>{{ t('org.saveChanges') }}</span>
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
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps({
  event: { type: Object, required: true },
});

const form = useForm({
  title:       props.event.title,
  description: props.event.description,
  date:        props.event.date,
  location:    props.event.location,
  capacity:    props.event.capacity,
  status:      props.event.status,
});

function switchLang(lang) {
  locale.value = lang;
  localStorage.setItem('locale', lang);
}
function submit() { form.put(`/organizer/events/${props.event.id}`); }
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
@import './css/EditEvent.css';
</style>
