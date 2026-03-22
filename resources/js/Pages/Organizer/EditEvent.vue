<template>
  <div class="org-page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-inner">
        <a href="/organizer/events" class="logo">⚡ EventHub</a>
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
        <div class="header-top">
          <span class="status-badge" :class="event.status">{{ event.status }}</span>
        </div>
        <h1 class="page-title">Edit Event</h1>
        <p class="page-sub">{{ event.title }}</p>
      </div>
    </div>

    <!-- FORM -->
    <div class="form-wrapper">
      <form @submit.prevent="submit" class="event-form">

        <!-- Success -->
        <div v-if="form.recentlySuccessful" class="alert-success">
          ✅ Event updated successfully!
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
            placeholder="Describe your event..."
            :class="{ 'input-error': form.errors.description }"
          ></textarea>
          <span class="char-count">{{ form.description.length }} characters</span>
          <span class="field-error" v-if="form.errors.description">{{ form.errors.description }}</span>
        </div>

        <!-- Date + Location -->
        <div class="fields-row">
          <div class="field">
            <label>Date &amp; Time <span class="required">*</span></label>
            <input
              v-model="form.date"
              type="datetime-local"
              :class="{ 'input-error': form.errors.date }"
            />
            <span class="field-error" v-if="form.errors.date">{{ form.errors.date }}</span>
          </div>
          <div class="field">
            <label>Location <span class="required">*</span></label>
            <input
              v-model="form.location"
              type="text"
              placeholder="e.g. Casablanca Tech Hub"
              :class="{ 'input-error': form.errors.location }"
            />
            <span class="field-error" v-if="form.errors.location">{{ form.errors.location }}</span>
          </div>
        </div>

        <!-- Capacity + Status -->
        <div class="fields-row">
          <div class="field">
            <label>Maximum Capacity <span class="required">*</span></label>
            <input
              v-model.number="form.capacity"
              type="number"
              min="1"
              max="10000"
              placeholder="e.g. 100"
              :class="{ 'input-error': form.errors.capacity }"
            />
            <span class="field-error" v-if="form.errors.capacity">{{ form.errors.capacity }}</span>
          </div>
          <div class="field">
            <label>Event Status <span class="required">*</span></label>
            <select
              v-model="form.status"
              :class="{ 'input-error': form.errors.status }"
            >
              <option value="open">Open — accepting registrations</option>
              <option value="closed">Closed — no new registrations</option>
              <option value="finished">Finished — event has ended</option>
            </select>
            <span class="field-error" v-if="form.errors.status">{{ form.errors.status }}</span>
          </div>
        </div>

        <!-- Preview -->
        <div class="preview-section">
          <h3 class="preview-label">Preview</h3>
          <div class="preview-card">
            <span class="preview-badge" :class="form.status">{{ form.status }}</span>
            <h4>{{ form.title || 'Your Event Title' }}</h4>
            <p class="preview-desc">{{ truncate(form.description, 100) || 'Your description...' }}</p>
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
          <button
            type="submit"
            class="btn-submit"
            :disabled="form.processing"
          >
            <span v-if="form.processing">Saving…</span>
            <span v-else>Save Changes →</span>
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
import { useForm, router } from '@inertiajs/vue3';

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

function submit() {
  form.put(`/organizer/events/${props.event.id}`);
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
@import './css/EditEvent.css';

</style>
