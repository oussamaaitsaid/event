<template>
  <div class="user-page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-inner">
        <a href="/" class="logo">⚡ EventHub</a>
        <div class="nav-right">
          <span class="role-chip" :class="user.role">{{ user.role }}</span>
          <span class="user-name">{{ user.name }}</span>
          <a v-if="user.role === 'organizer'" href="/organizer/events/create" class="btn-create">
            + {{ t('user.createEvent') }}
          </a>
          <!-- Language Switcher -->
          <div class="lang-switcher">
            <button class="lang-btn" :class="{ active: locale === 'en' }" @click="switchLang('en')">EN</button>
            <span class="lang-sep">|</span>
            <button class="lang-btn" :class="{ active: locale === 'fr' }" @click="switchLang('fr')">FR</button>
          </div>
          <button @click="logout" class="btn-logout">{{ t('user.logout') }}</button>
        </div>
      </div>
    </nav>

    <!-- HERO WELCOME -->
    <section class="welcome-banner">
      <div class="welcome-inner">
        <div class="welcome-avatar">{{ userInitials }}</div>
        <div>
          <h1 class="welcome-title">{{ t('user.welcomeBack') }}, {{ user.name }}! 👋</h1>
          <p class="welcome-sub">
            <span v-if="user.role === 'organizer'">
              {{ t('user.organizerDesc') }}
            </span>
            <span v-else>
              {{ t('user.participantDesc') }}
            </span>
          </p>
        </div>
      </div>
    </section>

    <!-- TABS -->
    <div class="tabs-bar">
      <div class="tabs-inner">
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'browse' }"
          @click="activeTab = 'browse'"
        >
          🔍 {{ t('user.browseEvents') }}
          <span class="tab-count">{{ events.length }}</span>
        </button>
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'mine' }"
          @click="activeTab = 'mine'"
        >
          📋 {{ t('user.myRegistrations') }}
          <span class="tab-count">{{ myRegistrations.length }}</span>
        </button>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

      <!-- ══ BROWSE EVENTS TAB ══ -->
      <div v-if="activeTab === 'browse'">

        <!-- Search -->
        <div class="search-bar">
          <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input
              v-model="search"
              type="text"
              :placeholder="t('user.searchPlaceholder')"
              class="search-input"
            />
          </div>
          <select v-model="sortBy" class="sort-select">
            <option value="date">{{ t('user.sortByDate') }}</option>
            <option value="spots">{{ t('user.mostSpots') }}</option>
            <option value="title">{{ t('user.aToZ') }}</option>
          </select>
        </div>

        <!-- Empty -->
        <div v-if="filteredEvents.length === 0" class="empty-state">
          <div class="empty-icon">🗓</div>
          <h3>{{ t('user.noEventsFound') }}</h3>
          <p>{{ search ? t('user.tryDifferent') : t('user.noOpenEvents') }}</p>
          <button v-if="search" @click="search = ''" class="btn-outline">{{ t('user.clearSearch') }}</button>
        </div>

        <!-- Events Grid -->
        <div v-else class="events-grid">
          <div v-for="event in filteredEvents" :key="event.id" class="event-card">

            <div class="card-top">
              <span class="card-badge" :class="getBadgeClass(event.spots_left)">
                {{ event.spots_left > 0 ? event.spots_left + ' ' + t('user.spotsLeft') : t('user.full') }}
              </span>
              <span class="card-date">{{ event.date }}</span>
            </div>

            <h3 class="card-title">{{ event.title }}</h3>
            <p class="card-desc">{{ truncate(event.description, 100) }}</p>

            <div class="card-meta">
              <span>📍 {{ event.location }}</span>
              <span>👤 {{ event.organizer }}</span>
              <span>👥 {{ event.capacity }} {{ t('user.capacity') }}</span>
            </div>

            <!-- Capacity Bar -->
            <div class="capacity-bar">
              <div
                class="capacity-fill"
                :style="{ width: getCapacityPct(event) + '%' }"
                :class="getCapacityFillClass(event)"
              ></div>
            </div>

            <!-- Action -->
            <div class="card-action">
              <span v-if="event.is_registered" class="registered-tag">
                ✅ {{ t('user.registered') }}
              </span>
              <button
                v-else-if="event.spots_left > 0"
                class="btn-register"
                @click="registerEvent(event)"
              >
                {{ t('user.registerNow') }}
              </button>
              <span v-else class="full-tag">{{ t('user.eventFull') }}</span>
            </div>

          </div>
        </div>
      </div>

      <!-- ══ MY REGISTRATIONS TAB ══ -->
      <div v-if="activeTab === 'mine'">

        <!-- Empty -->
        <div v-if="myRegistrations.length === 0" class="empty-state">
          <div class="empty-icon">📋</div>
          <h3>{{ t('user.noRegistrationsYet') }}</h3>
          <p>{{ t('user.noRegistrationsDesc') }}</p>
          <button class="btn-primary" @click="activeTab = 'browse'">{{ t('user.browseEvents') }} →</button>
        </div>

        <!-- Registrations List -->
        <div v-else class="registrations-list">
          <div
            v-for="reg in myRegistrations"
            :key="reg.id"
            class="reg-card"
            :class="{ cancelled: reg.status === 'cancelled' }"
          >
            <div class="reg-info">
              <div class="reg-icon">🎟️</div>
              <div>
                <h3 class="reg-title">{{ reg.event_title }}</h3>
                <div class="reg-meta">
                  <span>📅 {{ reg.event_date }}</span>
                  <span>📍 {{ reg.event_location }}</span>
                </div>
              </div>
            </div>
            <div class="reg-right">
              <span class="event-status-badge" :class="reg.event_status">
                {{ reg.event_status }}
              </span>
              <span class="reg-status-badge" :class="reg.status">
                {{ reg.status }}
              </span>
              <button
                v-if="reg.status === 'registered' && reg.event_status === 'open'"
                class="btn-cancel"
                @click="cancelRegistration(reg)"
              >
                {{ t('user.cancel') }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
      <p>© 2026 EventHub · {{ t('user.builtWith') }}</p>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps({
  events:          { type: Array, default: () => [] },
  myRegistrations: { type: Array, default: () => [] },
});

const page      = usePage();
const user      = page.props.auth.user;
const activeTab = ref('browse');
const search    = ref('');
const sortBy    = ref('date');

function switchLang(lang) {
  locale.value = lang;
  localStorage.setItem('locale', lang);
}

const userInitials = computed(() =>
  user.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) ?? '??'
);

const filteredEvents = computed(() => {
  let list = props.events.filter(e =>
    e.title.toLowerCase().includes(search.value.toLowerCase()) ||
    e.location.toLowerCase().includes(search.value.toLowerCase())
  );
  if (sortBy.value === 'date')  list = [...list].sort((a, b) => new Date(a.date) - new Date(b.date));
  if (sortBy.value === 'spots') list = [...list].sort((a, b) => b.spots_left - a.spots_left);
  if (sortBy.value === 'title') list = [...list].sort((a, b) => a.title.localeCompare(b.title));
  return list;
});

function truncate(text, length) {
  return text?.length > length ? text.slice(0, length) + '…' : text;
}
function getBadgeClass(spotsLeft) {
  if (spotsLeft <= 0) return 'badge-full';
  if (spotsLeft <= 5) return 'badge-low';
  return 'badge-open';
}
function getCapacityPct(event) {
  const used = event.capacity - event.spots_left;
  return Math.min(100, Math.round((used / event.capacity) * 100));
}
function getCapacityFillClass(event) {
  const pct = getCapacityPct(event);
  if (pct >= 90) return 'fill-red';
  if (pct >= 60) return 'fill-amber';
  return 'fill-green';
}
function registerEvent(event) {
  router.post(`/events/${event.id}/register`);
}
function cancelRegistration(reg) {
  if (confirm(t('user.cancelConfirm'))) {
    router.patch(`/registrations/${reg.id}/cancel`);
  }
}
function logout() {
  router.post('/logout');
}
</script>

<style scoped>
@import './css/Dashboard.css';


</style>
