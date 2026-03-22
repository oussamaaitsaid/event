<template>
  <div class="user-page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-inner">
        <a href="/" class="logo">⚡ EventHub</a>
        <div class="nav-right">
          <span class="role-chip" :class="user.role">{{ user.role }}</span>
          <span class="user-name">{{ user.name }}</span>
          <!-- Organizer only button -->
          <a v-if="user.role === 'organizer'" href="/organizer/events/create" class="btn-create">
            + Create Event
          </a>
          <button @click="logout" class="btn-logout">Logout</button>
        </div>
      </div>
    </nav>

    <!-- HERO WELCOME -->
    <section class="welcome-banner">
      <div class="welcome-inner">
        <div class="welcome-avatar">{{ userInitials }}</div>
        <div>
          <h1 class="welcome-title">Welcome back, {{ user.name }}! 👋</h1>
          <p class="welcome-sub">
            <span v-if="user.role === 'organizer'">
              You are logged in as an <strong>Organizer</strong> — you can browse events and create your own.
            </span>
            <span v-else>
              You are logged in as a <strong>Participant</strong> — browse and register for events below.
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
          🔍 Browse Events
          <span class="tab-count">{{ events.length }}</span>
        </button>
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'mine' }"
          @click="activeTab = 'mine'"
        >
          📋 My Registrations
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
              placeholder="Search events by title or location..."
              class="search-input"
            />
          </div>
          <select v-model="sortBy" class="sort-select">
            <option value="date">Sort by Date</option>
            <option value="spots">Most Spots</option>
            <option value="title">A → Z</option>
          </select>
        </div>

        <!-- Empty -->
        <div v-if="filteredEvents.length === 0" class="empty-state">
          <div class="empty-icon">🗓</div>
          <h3>No events found</h3>
          <p>{{ search ? 'Try a different search term.' : 'No open events at the moment.' }}</p>
          <button v-if="search" @click="search = ''" class="btn-outline">Clear Search</button>
        </div>

        <!-- Events Grid -->
        <div v-else class="events-grid">
          <div v-for="event in filteredEvents" :key="event.id" class="event-card">

            <div class="card-top">
              <span class="card-badge" :class="getBadgeClass(event.spots_left)">
                {{ event.spots_left > 0 ? event.spots_left + ' spots left' : 'Full' }}
              </span>
              <span class="card-date">{{ event.date }}</span>
            </div>

            <h3 class="card-title">{{ event.title }}</h3>
            <p class="card-desc">{{ truncate(event.description, 100) }}</p>

            <div class="card-meta">
              <span>📍 {{ event.location }}</span>
              <span>👤 {{ event.organizer }}</span>
              <span>👥 {{ event.capacity }} capacity</span>
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
                ✅ Registered
              </span>
              <button
                v-else-if="event.spots_left > 0"
                class="btn-register"
                @click="registerEvent(event)"
              >
                Register Now →
              </button>
              <span v-else class="full-tag">Event Full</span>
            </div>

          </div>
        </div>
      </div>

      <!-- ══ MY REGISTRATIONS TAB ══ -->
      <div v-if="activeTab === 'mine'">

        <!-- Empty -->
        <div v-if="myRegistrations.length === 0" class="empty-state">
          <div class="empty-icon">📋</div>
          <h3>No registrations yet</h3>
          <p>Browse events and register to see them here.</p>
          <button class="btn-primary" @click="activeTab = 'browse'">Browse Events →</button>
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
                Cancel
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
      <p>© 2026 EventHub · Built with Laravel &amp; Vue.js</p>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps({
  events:          { type: Array, default: () => [] },
  myRegistrations: { type: Array, default: () => [] },
});

const page      = usePage();
const user      = page.props.auth.user;
const activeTab = ref('browse');
const search    = ref('');
const sortBy    = ref('date');

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
  if (confirm('Cancel your registration for this event?')) {
    router.patch(`/registrations/${reg.id}/cancel`);
  }
}
function logout() {
  router.post('/logout');
}
</script>

<style scoped>
* { box-sizing: border-box; margin: 0; padding: 0; }

.user-page {
  font-family: 'Segoe UI', system-ui, sans-serif;
  background: #f1f5f9; min-height: 100vh;
  display: flex; flex-direction: column;
}

/* ── NAVBAR ── */
.navbar {
  background: white; border-bottom: 1px solid #e2e8f0;
  padding: 1rem 2rem; position: sticky; top: 0; z-index: 100;
}
.nav-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between;
}
.logo { font-size: 1.3rem; font-weight: 800; color: #4f46e5; text-decoration: none; }
.nav-right { display: flex; align-items: center; gap: 0.875rem; }
.role-chip {
  padding: 0.25rem 0.75rem; border-radius: 999px;
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
}
.role-chip.organizer { background: #eff6ff; color: #1d4ed8; }
.role-chip.user      { background: #f0fdf4; color: #15803d; }
.user-name { font-size: 0.9rem; font-weight: 600; color: #0f172a; }
.btn-create {
  background: #4f46e5; color: white;
  padding: 0.5rem 1.1rem; border-radius: 0.5rem;
  font-size: 0.875rem; font-weight: 700;
  text-decoration: none; transition: background 0.2s;
}
.btn-create:hover { background: #4338ca; }
.btn-logout {
  background: none; border: 1.5px solid #e2e8f0;
  color: #64748b; padding: 0.45rem 1rem;
  border-radius: 0.5rem; font-size: 0.875rem;
  font-weight: 600; cursor: pointer; transition: all 0.2s;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.btn-logout:hover { border-color: #ef4444; color: #ef4444; }

/* ── WELCOME BANNER ── */
.welcome-banner {
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #312e81 100%);
  padding: 2.5rem 2rem;
}
.welcome-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; gap: 1.25rem;
}
.welcome-avatar {
  width: 56px; height: 56px; border-radius: 50%;
  background: #4f46e5; color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; font-weight: 800; flex-shrink: 0;
}
.welcome-title { font-size: 1.5rem; font-weight: 800; color: white; margin-bottom: 0.3rem; }
.welcome-sub { font-size: 0.9rem; color: #c7d2fe; }
.welcome-sub strong { color: #fbbf24; }

/* ── TABS ── */
.tabs-bar { background: white; border-bottom: 1px solid #e2e8f0; }
.tabs-inner {
  max-width: 1200px; margin: 0 auto;
  padding: 0 2rem; display: flex; gap: 0;
}
.tab-btn {
  padding: 1rem 1.5rem; background: none; border: none;
  font-size: 0.9rem; font-weight: 600; color: #64748b;
  cursor: pointer; border-bottom: 2px solid transparent;
  transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.tab-btn:hover { color: #4f46e5; }
.tab-btn.active { color: #4f46e5; border-bottom-color: #4f46e5; }
.tab-count {
  background: #f1f5f9; color: #64748b;
  padding: 0.15rem 0.5rem; border-radius: 999px;
  font-size: 0.72rem; font-weight: 700;
}
.tab-btn.active .tab-count { background: #eef2ff; color: #4f46e5; }

/* ── CONTENT ── */
.content { max-width: 1200px; margin: 0 auto; padding: 2rem; flex: 1; width: 100%; }

/* ── SEARCH ── */
.search-bar {
  display: flex; gap: 0.75rem; margin-bottom: 2rem; flex-wrap: wrap;
}
.search-wrap { position: relative; display: flex; align-items: center; flex: 1; }
.search-icon { position: absolute; left: 0.85rem; font-size: 0.9rem; pointer-events: none; }
.search-input {
  width: 100%; padding: 0.7rem 1rem 0.7rem 2.4rem;
  border: 1.5px solid #e2e8f0; border-radius: 0.5rem;
  font-size: 0.9rem; background: white; color: #0f172a;
  outline: none; transition: border-color 0.2s;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.search-input:focus { border-color: #4f46e5; }
.sort-select {
  padding: 0.7rem 1rem; border: 1.5px solid #e2e8f0;
  border-radius: 0.5rem; font-size: 0.9rem;
  background: white; color: #0f172a; outline: none; cursor: pointer;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.sort-select:focus { border-color: #4f46e5; }

/* ── EVENTS GRID ── */
.events-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}
.event-card {
  background: white; border-radius: 1rem; padding: 1.5rem;
  border: 1px solid #e2e8f0; display: flex; flex-direction: column;
  gap: 0.85rem; transition: box-shadow 0.2s, transform 0.2s;
}
.event-card:hover { box-shadow: 0 10px 32px rgba(0,0,0,0.09); transform: translateY(-3px); }
.card-top { display: flex; align-items: center; justify-content: space-between; }
.card-badge {
  padding: 0.25rem 0.75rem; border-radius: 999px;
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
}
.badge-open { background: #dcfce7; color: #15803d; }
.badge-low  { background: #fef3c7; color: #b45309; }
.badge-full { background: #fee2e2; color: #b91c1c; }
.card-date { font-size: 0.78rem; color: #64748b; font-weight: 600; }
.card-title { font-size: 1.05rem; font-weight: 700; color: #0f172a; line-height: 1.3; }
.card-desc { font-size: 0.875rem; color: #64748b; line-height: 1.65; flex: 1; }
.card-meta { display: flex; flex-direction: column; gap: 0.3rem; font-size: 0.8rem; color: #475569; }
.capacity-bar { height: 5px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
.capacity-fill { height: 100%; border-radius: 999px; transition: width 0.4s; }
.fill-green { background: #22c55e; }
.fill-amber { background: #f59e0b; }
.fill-red   { background: #ef4444; }
.card-action { margin-top: auto; }
.btn-register {
  width: 100%; padding: 0.65rem; background: #4f46e5;
  color: white; border: none; border-radius: 0.5rem;
  font-size: 0.9rem; font-weight: 700; cursor: pointer;
  transition: background 0.2s;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.btn-register:hover { background: #4338ca; }
.registered-tag {
  display: block; text-align: center; padding: 0.65rem;
  background: #f0fdf4; color: #15803d; border-radius: 0.5rem;
  font-size: 0.875rem; font-weight: 700;
}
.full-tag {
  display: block; text-align: center; padding: 0.65rem;
  background: #f8fafc; color: #94a3b8; border-radius: 0.5rem;
  font-size: 0.875rem; font-weight: 600;
}

/* ── REGISTRATIONS LIST ── */
.registrations-list { display: flex; flex-direction: column; gap: 1rem; }
.reg-card {
  background: white; border-radius: 1rem; padding: 1.5rem;
  border: 1px solid #e2e8f0;
  display: flex; align-items: center; justify-content: space-between;
  gap: 1rem; flex-wrap: wrap; transition: box-shadow 0.2s;
}
.reg-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
.reg-card.cancelled { opacity: 0.6; }
.reg-info { display: flex; align-items: center; gap: 1rem; flex: 1; }
.reg-icon { font-size: 2rem; flex-shrink: 0; }
.reg-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: 0.3rem; }
.reg-meta { display: flex; gap: 1rem; font-size: 0.8rem; color: #64748b; flex-wrap: wrap; }
.reg-right { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }

.reg-status-badge {
  padding: 0.25rem 0.75rem; border-radius: 999px;
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
}
.reg-status-badge.registered { background: #dcfce7; color: #15803d; }
.reg-status-badge.cancelled  { background: #fee2e2; color: #b91c1c; }

.event-status-badge {
  padding: 0.25rem 0.75rem; border-radius: 999px;
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
}
.event-status-badge.open     { background: #dcfce7; color: #15803d; }
.event-status-badge.closed   { background: #fef3c7; color: #b45309; }
.event-status-badge.finished { background: #f1f5f9; color: #64748b; }

.btn-cancel {
  padding: 0.4rem 1rem; background: #fee2e2; color: #b91c1c;
  border: none; border-radius: 0.5rem; font-size: 0.82rem;
  font-weight: 600; cursor: pointer; transition: all 0.2s;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.btn-cancel:hover { background: #ef4444; color: white; }

/* ── EMPTY STATE ── */
.empty-state {
  text-align: center; padding: 5rem 2rem;
  display: flex; flex-direction: column; align-items: center; gap: 1rem;
}
.empty-icon { font-size: 3rem; }
.empty-state h3 { font-size: 1.25rem; font-weight: 700; color: #0f172a; }
.empty-state p { font-size: 0.95rem; color: #64748b; }
.btn-primary {
  background: #4f46e5; color: white; padding: 0.65rem 1.5rem;
  border: none; border-radius: 0.5rem; font-size: 0.9rem;
  font-weight: 700; cursor: pointer; transition: background 0.2s;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.btn-primary:hover { background: #4338ca; }
.btn-outline {
  background: white; color: #4f46e5;
  border: 1.5px solid #4f46e5; padding: 0.65rem 1.5rem;
  border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700;
  cursor: pointer; transition: all 0.2s;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.btn-outline:hover { background: #4f46e5; color: white; }

/* ── FOOTER ── */
.footer {
  text-align: center; padding: 1.5rem;
  color: #94a3b8; font-size: 0.85rem;
  border-top: 1px solid #e2e8f0; background: white;
  margin-top: auto;
}

/* ── RESPONSIVE ── */
@media (max-width: 640px) {
  .nav-right { gap: 0.5rem; }
  .user-name { display: none; }
  .welcome-inner { flex-direction: column; text-align: center; }
  .reg-card { flex-direction: column; align-items: flex-start; }
  .reg-right { width: 100%; justify-content: flex-start; }
}
</style>
