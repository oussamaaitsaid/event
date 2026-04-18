<template>
  <div class="org-page">

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-logo">⚡ EventHub</div>
      <nav class="sidebar-nav">
        <a class="nav-item" :class="{ active: activeTab === 'overview' }" @click="activeTab = 'overview'">
          <span class="nav-icon">📊</span> {{ t('org.overview') }}
        </a>
        <a class="nav-item" :class="{ active: activeTab === 'events' }" @click="activeTab = 'events'">
          <span class="nav-icon">📅</span> {{ t('org.myEvents') }}
        </a>
        <a class="nav-item" href="/">
          <span class="nav-icon">🌐</span> {{ t('org.publicPage') }}
        </a>
        <a class="nav-item" href="/dashboard">
          <span class="nav-icon">👤</span> {{ t('org.myDashboard') }}
        </a>
      </nav>
      <div class="sidebar-footer">
        <div class="org-info">
          <div class="org-avatar">{{ userInitials }}</div>
          <div>
            <p class="org-name">{{ user.name }}</p>
            <p class="org-role">{{ t('org.organizer') }}</p>
          </div>
        </div>
        <!-- Language Switcher -->
        <div class="lang-switcher">
          <button class="lang-btn" :class="{ active: locale === 'en' }" @click="switchLang('en')">EN</button>
          <span class="lang-sep">|</span>
          <button class="lang-btn" :class="{ active: locale === 'fr' }" @click="switchLang('fr')">FR</button>
        </div>
        <button @click="logout" class="btn-logout">{{ t('org.logout') }}</button>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main">

      <!-- TOPBAR -->
      <header class="topbar">
        <div>
          <h1 class="page-title">
            {{ activeTab === 'overview' ? t('org.overviewTitle') : t('org.myEventsTitle') }}
          </h1>
          <p class="page-sub">{{ today }}</p>
        </div>
        <a href="/organizer/events/create" class="btn-create">+ {{ t('org.createEvent') }}</a>
      </header>

      <!-- SUCCESS TOAST -->
      <transition name="fade">
        <div v-if="showSuccess" class="toast-success">✅ {{ success }}</div>
      </transition>

      <!-- ══ OVERVIEW TAB ══ -->
      <div v-if="activeTab === 'overview'" class="tab-content">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon blue">📅</div>
            <div class="stat-info">
              <span class="stat-num">{{ events.length }}</span>
              <span class="stat-label">{{ t('org.totalEvents') }}</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green">✅</div>
            <div class="stat-info">
              <span class="stat-num">{{ openEvents }}</span>
              <span class="stat-label">{{ t('org.openEvents') }}</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon purple">👥</div>
            <div class="stat-info">
              <span class="stat-num">{{ totalRegistrations }}</span>
              <span class="stat-label">{{ t('org.totalRegistrations') }}</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon amber">💺</div>
            <div class="stat-info">
              <span class="stat-num">{{ totalSpotsLeft }}</span>
              <span class="stat-label">{{ t('org.spotsRemaining') }}</span>
            </div>
          </div>
        </div>

        <div class="table-card">
          <div class="table-header">
            <h3>{{ t('org.recentEvents') }}</h3>
            <button class="btn-view-all" @click="activeTab = 'events'">{{ t('org.viewAll') }} →</button>
          </div>
          <div v-if="events.length === 0" class="empty-table">
            <p>{{ t('org.noEventsYet') }} <a href="/organizer/events/create">{{ t('org.createFirst') }} →</a></p>
          </div>
          <table v-else class="data-table">
            <thead>
              <tr>
                <th>{{ t('org.event') }}</th>
                <th>{{ t('org.date') }}</th>
                <th>{{ t('org.status') }}</th>
                <th>{{ t('org.registrations') }}</th>
                <th>{{ t('org.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="event in events.slice(0, 5)" :key="event.id">
                <td>
                  <p class="cell-name">{{ event.title }}</p>
                  <p class="cell-sub">📍 {{ event.location }}</p>
                </td>
                <td class="cell-muted">{{ event.date }}</td>
                <td><span class="status-badge" :class="event.status">{{ event.status }}</span></td>
                <td>
                  <div class="reg-cell">
                    <span class="reg-count">{{ event.registrations_count }} / {{ event.capacity }}</span>
                    <div class="mini-bar">
                      <div class="mini-fill" :style="{ width: getCapacityPct(event) + '%' }" :class="getCapacityFillClass(event)"></div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="action-btns">
                    <a :href="`/organizer/events/${event.id}/edit`" class="btn-action edit">{{ t('org.edit') }}</a>
                    <a :href="`/organizer/events/${event.id}/participants`" class="btn-action view">{{ t('org.participants') }}</a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ══ MY EVENTS TAB ══ -->
      <div v-if="activeTab === 'events'" class="tab-content">
        <div class="filter-bar">
          <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input v-model="search" type="text" :placeholder="t('org.searchEvents')" class="search-input" />
          </div>
          <select v-model="filterStatus" class="sort-select">
            <option value="all">{{ t('org.allStatus') }}</option>
            <option value="open">{{ t('org.open') }}</option>
            <option value="closed">{{ t('org.closed') }}</option>
            <option value="finished">{{ t('org.finished') }}</option>
          </select>
        </div>

        <div v-if="filteredEvents.length === 0" class="empty-state">
          <div class="empty-icon">📅</div>
          <h3>{{ t('org.noEventsFound') }}</h3>
          <p>{{ search ? t('org.tryDifferent') : t('org.noEventsYet') }}</p>
          <a href="/organizer/events/create" class="btn-primary">+ {{ t('org.createFirst') }}</a>
        </div>

        <div v-else class="events-grid">
          <div v-for="event in filteredEvents" :key="event.id" class="event-card">
            <div class="card-header">
              <span class="status-badge" :class="event.status">{{ event.status }}</span>
              <div class="card-actions-top">
                <a :href="`/organizer/events/${event.id}/edit`" class="icon-btn edit" title="Edit">✏️</a>
                <button class="icon-btn delete" :title="t('org.delete')" @click="confirmDelete(event)">🗑️</button>
              </div>
            </div>
            <h3 class="card-title">{{ event.title }}</h3>
            <p class="card-desc">{{ truncate(event.description, 90) }}</p>
            <div class="card-meta">
              <span>📅 {{ event.date }}</span>
              <span>📍 {{ event.location }}</span>
            </div>
            <div class="capacity-section">
              <div class="capacity-info">
                <span>{{ event.registrations_count }} {{ t('org.registered') }}</span>
                <span>{{ event.spots_left }} {{ t('org.spotsLeft') }}</span>
              </div>
              <div class="capacity-bar">
                <div class="capacity-fill" :style="{ width: getCapacityPct(event) + '%' }" :class="getCapacityFillClass(event)"></div>
              </div>
            </div>
            <div class="card-footer">
              <a :href="`/organizer/events/${event.id}/participants`" class="btn-participants">
                👥 {{ t('org.viewParticipants') }}
              </a>
              <a :href="`/organizer/events/${event.id}/edit`" class="btn-edit">
                {{ t('org.edit') }} →
              </a>
            </div>
          </div>
        </div>
      </div>

    </main>

    <!-- DELETE CONFIRM MODAL -->
    <transition name="fade">
      <div v-if="eventToDelete" class="modal-overlay" @click.self="eventToDelete = null">
        <div class="modal">
          <h3 class="modal-title">{{ t('org.deleteEvent') }}</h3>
          <p class="modal-body">
            {{ t('org.deleteConfirm') }}
            <strong>"{{ eventToDelete.title }}"</strong>?
            {{ t('org.deleteWarning') }}
          </p>
          <div class="modal-footer">
            <button class="btn-modal-cancel" @click="eventToDelete = null">{{ t('org.cancel') }}</button>
            <button class="btn-modal-delete" @click="deleteEvent">{{ t('org.yesDelete') }}</button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps({
  events:  { type: Array,  default: () => [] },
  success: { type: String, default: null },
});

const page          = usePage();
const user          = page.props.auth.user;
const activeTab     = ref('overview');
const search        = ref('');
const filterStatus  = ref('all');
const eventToDelete = ref(null);
const showSuccess   = ref(false);

const today = new Date().toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});

function switchLang(lang) {
  locale.value = lang;
  localStorage.setItem('locale', lang);
}

const userInitials = computed(() =>
  user.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) ?? '??'
);

onMounted(() => {
  if (props.success) {
    showSuccess.value = true;
    setTimeout(() => showSuccess.value = false, 3000);
  }
});

const openEvents         = computed(() => props.events.filter(e => e.status === 'open').length);
const totalRegistrations = computed(() => props.events.reduce((sum, e) => sum + e.registrations_count, 0));
const totalSpotsLeft     = computed(() => props.events.reduce((sum, e) => sum + e.spots_left, 0));

const filteredEvents = computed(() =>
  props.events.filter(e => {
    const matchSearch = e.title.toLowerCase().includes(search.value.toLowerCase()) ||
                        e.location.toLowerCase().includes(search.value.toLowerCase());
    const matchStatus = filterStatus.value === 'all' || e.status === filterStatus.value;
    return matchSearch && matchStatus;
  })
);

function truncate(text, length) {
  return text?.length > length ? text.slice(0, length) + '…' : text;
}
function getCapacityPct(event) {
  return Math.min(100, Math.round((event.registrations_count / event.capacity) * 100));
}
function getCapacityFillClass(event) {
  const pct = getCapacityPct(event);
  if (pct >= 90) return 'fill-red';
  if (pct >= 60) return 'fill-amber';
  return 'fill-green';
}
function confirmDelete(event) { eventToDelete.value = event; }
function deleteEvent() {
  router.delete(`/organizer/events/${eventToDelete.value.id}`, {
    onFinish: () => eventToDelete.value = null,
  });
}
function logout() { router.post('/logout'); }
</script>

<style scoped>
@import './css/Events.css';
</style>
