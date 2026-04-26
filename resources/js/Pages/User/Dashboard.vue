<template>
  <div class="user-page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-inner">
        <a href="/" class="logo">⚡ EventHub</a>
        <div class="nav-right">
          <a v-if="user.role === 'organizer'" href="/organizer/events" class="btn-outline">← {{ t('org.myEvents') }}</a>
          <span class="role-chip" :class="user.role">{{ user.role }}</span>
          <span class="user-name">{{ user.name }}</span>
          <a v-if="user.role === 'organizer'" href="/organizer/events/create" class="btn-create">
            + {{ t('user.createEvent') }}
          </a>
          <div class="lang-switcher">
            <button class="lang-btn" :class="{ active: locale === 'en' }" @click="switchLang('en')">EN</button>
            <span class="lang-sep">|</span>
            <button class="lang-btn" :class="{ active: locale === 'fr' }" @click="switchLang('fr')">FR</button>
          </div>
          <button @click="logout" class="btn-logout">{{ t('user.logout') }}</button>
        </div>

        <button class="mobile-menu-btn" @click="mobileOpen = !mobileOpen">
          <span></span><span></span><span></span>
        </button>
      </div>

      <transition name="slide">
        <div v-if="mobileOpen" class="mobile-menu">
          <a v-if="user.role === 'organizer'" href="/organizer/events" class="mobile-link" @click="mobileOpen = false">
            ← {{ t('org.myEvents') }}
          </a>
          <a v-if="user.role === 'organizer'" href="/organizer/events/create" class="mobile-link" @click="mobileOpen = false">
            + {{ t('user.createEvent') }}
          </a>
          <div class="mobile-divider"></div>
          <div class="mobile-lang">
            <button class="lang-btn" :class="{ active: locale === 'en' }" @click="switchLang('en')">EN</button>
            <span class="lang-sep">|</span>
            <button class="lang-btn" :class="{ active: locale === 'fr' }" @click="switchLang('fr')">FR</button>
          </div>
          <div class="mobile-divider"></div>
          <button @click="logout" class="mobile-logout">{{ t('user.logout') }}</button>
        </div>
      </transition>
    </nav>

    <!-- HERO WELCOME -->
    <section class="welcome-banner">
      <div class="welcome-inner">
        <div class="welcome-avatar">{{ userInitials }}</div>
        <div>
          <h1 class="welcome-title">{{ t('user.welcomeBack') }}, {{ user.name }}! 👋</h1>
          <p class="welcome-sub">
            <span v-if="user.role === 'organizer'">{{ t('user.organizerDesc') }}</span>
            <span v-else>{{ t('user.participantDesc') }}</span>
          </p>
        </div>
      </div>
    </section>

    <!-- TABS -->
    <div class="tabs-bar">
      <div class="tabs-inner">
        <button class="tab-btn" :class="{ active: activeTab === 'browse' }" @click="activeTab = 'browse'">
          🔍 {{ t('user.browseEvents') }}
          <span class="tab-count">{{ events.length }}</span>
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'mine' }" @click="activeTab = 'mine'">
          📋 {{ t('user.myRegistrations') }}
          <span class="tab-count">{{ myRegistrations.length }}</span>
        </button>
        <!-- ✅ Only for regular users -->
        <button v-if="user.role === 'user'" class="tab-btn" :class="{ active: activeTab === 'become' }" @click="activeTab = 'become'">
          🚀 {{ t('user.becomeOrganizer') }}
        </button>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

      <!-- ══ BROWSE EVENTS TAB ══ -->
      <div v-if="activeTab === 'browse'">
        <div class="search-bar">
          <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input v-model="search" type="text" :placeholder="t('user.searchPlaceholder')" class="search-input" />
          </div>
          <select v-model="sortBy" class="sort-select">
            <option value="date">{{ t('user.sortByDate') }}</option>
            <option value="spots">{{ t('user.mostSpots') }}</option>
            <option value="title">{{ t('user.aToZ') }}</option>
          </select>
        </div>

        <div v-if="filteredEvents.length === 0" class="empty-state">
          <div class="empty-icon">🗓</div>
          <h3>{{ t('user.noEventsFound') }}</h3>
          <p>{{ search ? t('user.tryDifferent') : t('user.noOpenEvents') }}</p>
          <button v-if="search" @click="search = ''" class="btn-outline">{{ t('user.clearSearch') }}</button>
        </div>

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
            <div class="capacity-bar">
              <div
                class="capacity-fill"
                :style="{ width: getCapacityPct(event) + '%' }"
                :class="getCapacityFillClass(event)"
              ></div>
            </div>
           <!-- ✅ After -->
            <div class="card-action">
            <div v-if="event.my_status === 'registered'" class="registered-tag">
                ✅ {{ t('user.registered') }}
                <p class="status-msg">{{ t('user.registeredMsg') }}</p>
            </div>
            <div v-else-if="event.my_status === 'pending'" class="pending-tag">
                ⏳ {{ t('user.pending') }}
                <p class="status-msg">{{ t('user.pendingMsg') }}</p>
            </div>
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
        <div v-if="myRegistrations.length === 0" class="empty-state">
          <div class="empty-icon">📋</div>
          <h3>{{ t('user.noRegistrationsYet') }}</h3>
          <p>{{ t('user.noRegistrationsDesc') }}</p>
          <button class="btn-primary" @click="activeTab = 'browse'">{{ t('user.browseEvents') }} →</button>
        </div>
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
              <span class="event-status-badge" :class="reg.event_status">{{ reg.event_status }}</span>
              <span class="reg-status-badge" :class="reg.status">
                {{ reg.status === 'pending' ? t('user.pendingApproval') : reg.status }}
              </span>
              <button
                v-if="(reg.status === 'pending' || reg.status === 'registered') && reg.event_status === 'open'"
                class="btn-cancel"
                @click="cancelRegistration(reg)"
              >
                {{ t('user.cancel') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ BECOME ORGANIZER TAB ══ -->
      <div v-if="activeTab === 'become'">
        <div class="become-wrapper">

          <transition name="fade">
            <div v-if="requestSuccess" class="alert-success">
              ✅ {{ t('user.requestSent') }}
            </div>
          </transition>

         <div v-if="form.errors.mail" class="alert-error">
            ⚠ {{ form.errors.mail }}
            </div>
          <div class="become-card">
            <div class="become-icon">🚀</div>
            <h2 class="become-title">{{ t('user.becomeOrganizerTitle') }}</h2>
            <p class="become-sub">{{ t('user.becomeOrganizerDesc') }}</p>

            <form @submit.prevent="sendRequest" class="become-form">
              <div class="field">
                <label>{{ t('user.whyOrganizer') }}</label>
                <textarea
                  v-model="requestMessage"
                  rows="5"
                  :placeholder="t('user.whyOrganizerPlaceholder')"
                  class="become-textarea"
                  :class="{ 'input-error': requestError }"
                ></textarea>
                <span v-if="requestError" class="field-error">{{ requestError }}</span>
                <span class="char-count">{{ requestMessage.length }} / 1000</span>
              </div>
              <button type="submit" class="btn-become" :disabled="form.processing">
                <span v-if="form.processing">{{ t('user.sending') }}</span>
                <span v-else>{{ t('user.sendRequest') }} →</span>
              </button>
            </form>
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
import { usePage, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps({
  events:          { type: Array, default: () => [] },
  myRegistrations: { type: Array, default: () => [] },
});

const page       = usePage();
const user       = page.props.auth.user;
const activeTab  = ref('browse');
const search     = ref('');
const sortBy     = ref('date');
const mobileOpen = ref(false);

// ── Become Organizer ──
const requestMessage = ref('');
const requestSuccess = ref(false);
const requestError   = ref('');
const form           = useForm({ message: '' });

function switchLang(lang) {
  locale.value = lang;
  localStorage.setItem('locale', lang);
  mobileOpen.value = false;
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

function sendRequest() {
  requestError.value = '';

  if (requestMessage.value.length < 20) {
    requestError.value = 'Please write at least 20 characters.';
    return;
  }

  form.message = requestMessage.value;

  form.post('/contact/organizer-request', {
    onSuccess: () => {
      requestSuccess.value = true;
      requestMessage.value = '';
      form.reset();
      setTimeout(() => requestSuccess.value = false, 5000);
    },
    onError: () => {
      requestError.value = 'Something went wrong. Please try again.';
    },
  });
}
</script>

<style scoped>
@import './css/Dashboard.css';

.pending-tag {
  display: block; text-align: center; padding: 0.65rem;
  background: #fef3c7; color: #b45309; border-radius: 0.5rem;
  font-size: 0.875rem; font-weight: 700;
}
.reg-status-badge.pending { background: #fef3c7; color: #b45309; }

/* ── Hamburger ── */
.mobile-menu-btn {
  display: none; flex-direction: column; gap: 5px;
  background: none; border: none; cursor: pointer;
  padding: 0.5rem; border-radius: 0.5rem; transition: background 0.2s;
}
.mobile-menu-btn:hover { background: #f1f5f9; }
.mobile-menu-btn span {
  display: block; width: 22px; height: 2px;
  background: #475569; border-radius: 999px;
}

/* ── Mobile Menu ── */
.mobile-menu {
  border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem;
  display: flex; flex-direction: column; gap: 0.25rem; background: white;
}
.mobile-link {
  padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.9rem;
  font-weight: 500; color: #475569; text-decoration: none; transition: all 0.2s;
}
.mobile-link:hover { background: #f1f5f9; color: #0f172a; }
.mobile-divider { height: 1px; background: #f1f5f9; margin: 0.5rem 0; }
.mobile-lang { display: flex; align-items: center; gap: 0.25rem; padding: 0 0.5rem; }
.mobile-logout {
  padding: 0.75rem 1rem; background: none; border: 1.5px solid #e2e8f0;
  color: #64748b; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 600;
  cursor: pointer; transition: all 0.2s; text-align: left;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.mobile-logout:hover { border-color: #ef4444; color: #ef4444; }
.slide-enter-active, .slide-leave-active { transition: all 0.25s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-8px); }

@media (max-width: 768px) {
  .nav-right { display: none; }
  .mobile-menu-btn { display: flex; }
  .user-name { display: none; }
}

/* ── Become Organizer ── */
.become-wrapper {
  max-width: 600px; margin: 0 auto; padding: 2rem 0;
  display: flex; flex-direction: column; gap: 1.5rem;
}
.alert-success {
  background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d;
  padding: 0.875rem 1rem; border-radius: 0.5rem;
  font-size: 0.9rem; font-weight: 600;
}
.alert-error {
  background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;
  padding: 0.875rem 1rem; border-radius: 0.5rem; font-size: 0.875rem;
}
.become-card {
  background: white; border-radius: 1rem; padding: 2rem;
  border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 1rem;
}
.become-icon { font-size: 2.5rem; }
.become-title { font-size: 1.4rem; font-weight: 800; color: #0f172a; }
.become-sub { font-size: 0.9rem; color: #64748b; line-height: 1.65; }
.become-form { display: flex; flex-direction: column; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.4rem; }
.field label { font-size: 0.875rem; font-weight: 700; color: #374151; }
.become-textarea {
  width: 100%; padding: 0.75rem 1rem;
  border: 1.5px solid #e2e8f0; border-radius: 0.5rem;
  font-size: 0.95rem; color: #0f172a; background: white;
  outline: none; transition: border-color 0.2s; resize: vertical;
  min-height: 140px; font-family: 'Segoe UI', system-ui, sans-serif;
}
.become-textarea:focus { border-color: #4f46e5; }
.become-textarea.input-error { border-color: #f87171; }
.field-error { font-size: 0.78rem; color: #ef4444; font-weight: 600; }
.char-count { font-size: 0.75rem; color: #94a3b8; text-align: right; }
.btn-become {
  width: 100%; padding: 0.8rem; background: #4f46e5; color: white;
  border: none; border-radius: 0.5rem; font-size: 1rem; font-weight: 700;
  cursor: pointer; transition: background 0.2s;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.btn-become:hover:not(:disabled) { background: #4338ca; }
.btn-become:disabled { opacity: 0.7; cursor: not-allowed; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.status-msg {
  font-size: 0.75rem; font-weight: 500;
  margin-top: 0.3rem; opacity: 0.85;
}
</style>
