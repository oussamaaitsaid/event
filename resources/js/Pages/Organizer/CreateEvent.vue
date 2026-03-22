<template>
  <div class="org-page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-inner">
        <a href="/dashboard" class="logo">⚡ EventHub</a>
        <div class="nav-right">
          <a href="/organizer/events" class="btn-outline">← My Events</a>
          <span class="role-chip">Organizer</span>
          <button @click="logout" class="btn-logout">Logout</button>
        </div>
      </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
      <div class="page-header-inner">
        <h1 class="page-title">Create New Event</h1>
        <p class="page-sub">Fill in the details below to publish your event</p>
      </div>
    </div>

    <!-- FORM -->
    <div class="form-wrapper">
      <form @submit.prevent="submit" class="event-form">

        <!-- Success -->
        <div v-if="form.recentlySuccessful" class="alert-success">
          ✅ Event created successfully!
        </div>

        <!-- Errors -->
        <div v-if="Object.keys(form.errors).length" class="alert-error">
          <p v-for="(error, field) in form.errors" :key="field">⚠ {{ error }}</p>
        </div>

        <!-- Title -->
        <div class="field">
          <label>Event Title <span class="required">*</span></label>
          <input
            v-model="form.title"
            type="text"
            placeholder="e.g. Laravel & Vue.js Workshop 2026"
            :class="{ 'input-error': form.errors.title }"
          />
          <span class="field-error" v-if="form.errors.title">{{ form.errors.title }}</span>
        </div>

        <!-- Description -->
        <div class="field">
          <label>Description <span class="required">*</span></label>
          <textarea
            v-model="form.description"
            rows="5"
            placeholder="Describe your event — what will attendees learn or experience?"
            :class="{ 'input-error': form.errors.description }"
          ></textarea>
          <span class="char-count">{{ form.description.length }} characters</span>
          <span class="field-error" v-if="form.errors.description">{{ form.errors.description }}</span>
        </div>

        <!-- Two columns: Date + Location -->
        <div class="fields-row">
          <div class="field">
            <label>Date &amp; Time <span class="required">*</span></label>
            <input
              v-model="form.date"
              type="datetime-local"
              :min="minDate"
              :class="{ 'input-error': form.errors.date }"
            />
            <span class="field-error" v-if="form.errors.date">{{ form.errors.date }}</span>
          </div>

          <div class="field">
            <label>Location <span class="required">*</span></label>
            <input
              v-model="form.location"
              type="text"
              placeholder="e.g. Casablanca Tech Hub or Online (Zoom)"
              :class="{ 'input-error': form.errors.location }"
            />
            <span class="field-error" v-if="form.errors.location">{{ form.errors.location }}</span>
          </div>
        </div>

        <!-- Capacity -->
        <div class="field capacity-field">
          <label>Maximum Capacity <span class="required">*</span></label>
          <div class="capacity-wrap">
            <input
              v-model.number="form.capacity"
              type="number"
              min="1"
              max="10000"
              placeholder="e.g. 100"
              :class="{ 'input-error': form.errors.capacity }"
            />
            <div class="capacity-hint">
              <span class="hint-icon">👥</span>
              <span>Max participants allowed</span>
            </div>
          </div>
          <span class="field-error" v-if="form.errors.capacity">{{ form.errors.capacity }}</span>
        </div>

        <!-- Preview Card -->
        <div class="preview-section" v-if="form.title || form.date || form.location">
          <h3 class="preview-title">Preview</h3>
          <div class="preview-card">
            <div class="preview-badge">Open</div>
            <h4>{{ form.title || 'Your Event Title' }}</h4>
            <p class="preview-desc">{{ truncate(form.description, 80) || 'Your event description will appear here.' }}</p>
            <div class="preview-meta">
              <span v-if="form.date">📅 {{ formatPreviewDate(form.date) }}</span>
              <span v-if="form.location">📍 {{ form.location }}</span>
              <span v-if="form.capacity">👥 {{ form.capacity }} capacity</span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <a href="/organizer/events" class="btn-cancel">Cancel</a>
          <button type="submit" class="btn-submit" :disabled="form.processing">
            <span v-if="form.processing">Creating Event…</span>
            <span v-else>Publish Event →</span>
          </button>
        </div>

      </form>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
      <p>© 2026 EventHub · Built with Laravel &amp; Vue.js</p>
    </footer>

  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

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

function submit() {
  form.post('/organizer/events');
}

function logout() {
  router.post('/logout');
}

function truncate(text, length) {
  return text?.length > length ? text.slice(0, length) + '…' : text;
}

function formatPreviewDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    weekday: 'short', month: 'long',
    day: 'numeric', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}
</script>

<style scoped>
@import './css/CreateEvent.css';
</style>
