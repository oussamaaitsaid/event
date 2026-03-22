<template>
  <div class="org-page">

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-logo">⚡ EventHub</div>
      <nav class="sidebar-nav">

        <a  class="nav-item"
          :class="{ active: activeTab === 'overview' }"
          @click="activeTab = 'overview'"
        >
          <span class="nav-icon">📊</span> Overview
        </a>

         <a class="nav-item"
          :class="{ active: activeTab === 'events' }"
          @click="activeTab = 'events'"
        >
          <span class="nav-icon">📅</span> My Events
        </a>
        <a class="nav-item" href="/">
          <span class="nav-icon">🌐</span> Public Page
        </a>
        <a class="nav-item" href="/dashboard">
          <span class="nav-icon">👤</span> My Dashboard
        </a>
      </nav>
      <div class="sidebar-footer">
        <div class="org-info">
          <div class="org-avatar">{{ userInitials }}</div>
          <div>
            <p class="org-name">{{ user.name }}</p>
            <p class="org-role">Organizer</p>
          </div>
        </div>
        <button @click="logout" class="btn-logout">Logout</button>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main">

      <!-- TOPBAR -->
      <header class="topbar">
        <div>
          <h1 class="page-title">
            {{ activeTab === 'overview' ? 'Organizer Overview' : 'My Events' }}
          </h1>
          <p class="page-sub">{{ today }}</p>
        </div>
        <a href="/organizer/events/create" class="btn-create">+ Create Event</a>
      </header>

      <!-- SUCCESS TOAST -->
      <transition name="fade">
        <div v-if="showSuccess" class="toast-success">
          ✅ {{ success }}
        </div>
      </transition>

      <!-- ══ OVERVIEW TAB ══ -->
      <div v-if="activeTab === 'overview'" class="tab-content">

        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon blue">📅</div>
            <div class="stat-info">
              <span class="stat-num">{{ events.length }}</span>
              <span class="stat-label">Total Events</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green">✅</div>
            <div class="stat-info">
              <span class="stat-num">{{ openEvents }}</span>
              <span class="stat-label">Open Events</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon purple">👥</div>
            <div class="stat-info">
              <span class="stat-num">{{ totalRegistrations }}</span>
              <span class="stat-label">Total Registrations</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon amber">💺</div>
            <div class="stat-info">
              <span class="stat-num">{{ totalSpotsLeft }}</span>
              <span class="stat-label">Spots Remaining</span>
            </div>
          </div>
        </div>

        <!-- Recent Events Table -->
        <div class="table-card">
          <div class="table-header">
            <h3>Recent Events</h3>
            <button class="btn-view-all" @click="activeTab = 'events'">View All →</button>
          </div>
          <div v-if="events.length === 0" class="empty-table">
            <p>No events yet. <a href="/organizer/events/create">Create your first event →</a></p>
          </div>
          <table v-else class="data-table">
            <thead>
              <tr>
                <th>Event</th>
                <th>Date</th>
                <th>Status</th>
                <th>Registrations</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="event in events.slice(0, 5)" :key="event.id">
                <td>
                  <p class="cell-name">{{ event.title }}</p>
                  <p class="cell-sub">📍 {{ event.location }}</p>
                </td>
                <td class="cell-muted">{{ event.date }}</td>
                <td>
                  <span class="status-badge" :class="event.status">{{ event.status }}</span>
                </td>
                <td>
                  <div class="reg-cell">
                    <span class="reg-count">{{ event.registrations_count }} / {{ event.capacity }}</span>
                    <div class="mini-bar">
                      <div
                        class="mini-fill"
                        :style="{ width: getCapacityPct(event) + '%' }"
                        :class="getCapacityFillClass(event)"
                      ></div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="action-btns">
                    <a :href="`/organizer/events/${event.id}/edit`" class="btn-action edit">Edit</a>
                    <a :href="`/organizer/events/${event.id}/participants`" class="btn-action view">Participants</a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

      <!-- ══ MY EVENTS TAB ══ -->
      <div v-if="activeTab === 'events'" class="tab-content">

        <!-- Search & Filter -->
        <div class="filter-bar">
          <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input
              v-model="search"
              type="text"
              placeholder="Search your events..."
              class="search-input"
            />
          </div>
          <select v-model="filterStatus" class="sort-select">
            <option value="all">All Status</option>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
            <option value="finished">Finished</option>
          </select>
        </div>

        <!-- Empty -->
        <div v-if="filteredEvents.length === 0" class="empty-state">
          <div class="empty-icon">📅</div>
          <h3>No events found</h3>
          <p>{{ search ? 'Try a different search term.' : 'You have no events yet.' }}</p>
          <a href="/organizer/events/create" class="btn-primary">+ Create Your First Event</a>
        </div>

        <!-- Events Cards -->
        <div v-else class="events-grid">
          <div v-for="event in filteredEvents" :key="event.id" class="event-card">

            <!-- Card Header -->
            <div class="card-header">
              <span class="status-badge" :class="event.status">{{ event.status }}</span>
              <div class="card-actions-top">

                 <a :href="`/organizer/events/${event.id}/edit`"
                  class="icon-btn edit"
                  title="Edit"
                >✏️</a>
                <button
                  class="icon-btn delete"
                  title="Delete"
                  @click="confirmDelete(event)"
                >🗑️</button>
              </div>
            </div>

            <h3 class="card-title">{{ event.title }}</h3>
            <p class="card-desc">{{ truncate(event.description, 90) }}</p>

            <div class="card-meta">
              <span>📅 {{ event.date }}</span>
              <span>📍 {{ event.location }}</span>
            </div>

            <!-- Capacity Bar -->
            <div class="capacity-section">
              <div class="capacity-info">
                <span>{{ event.registrations_count }} registered</span>
                <span>{{ event.spots_left }} spots left</span>
              </div>
              <div class="capacity-bar">
                <div
                  class="capacity-fill"
                  :style="{ width: getCapacityPct(event) + '%' }"
                  :class="getCapacityFillClass(event)"
                ></div>
              </div>
            </div>

            <!-- Card Footer -->
            <div class="card-footer">

                <a :href="`/organizer/events/${event.id}/participants`"
                class="btn-participants"
              >
                👥 View Participants
              </a>

                <a :href="`/organizer/events/${event.id}/edit`"
                class="btn-edit"
              >
                Edit →
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
          <h3 class="modal-title">Delete Event</h3>
          <p class="modal-body">
            Are you sure you want to delete
            <strong>"{{ eventToDelete.title }}"</strong>?
            This will also delete all registrations. This cannot be undone.
          </p>
          <div class="modal-footer">
            <button class="btn-modal-cancel" @click="eventToDelete = null">Cancel</button>
            <button class="btn-modal-delete" @click="deleteEvent">Yes, Delete</button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

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

const today = new Date().toLocaleDateString('en-US', {
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});

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
function confirmDelete(event) {
  eventToDelete.value = event;
}
function deleteEvent() {
  router.delete(`/organizer/events/${eventToDelete.value.id}`, {
    onFinish: () => eventToDelete.value = null,
  });
}
function logout() {
  router.post('/logout');
}
</script>

<style scoped>
* { box-sizing: border-box; margin: 0; padding: 0; }

.org-page {
  display: flex; min-height: 100vh;
  font-family: 'Segoe UI', system-ui, sans-serif;
  background: #f1f5f9;
}

/* ── SIDEBAR ── */
.sidebar {
  width: 260px; min-height: 100vh; background: #0f172a;
  color: white; display: flex; flex-direction: column;
  position: sticky; top: 0; height: 100vh; flex-shrink: 0;
}
.sidebar-logo {
  padding: 1.75rem 1.5rem; font-size: 1.3rem; font-weight: 800;
  color: #6366f1; border-bottom: 1px solid #1e293b; letter-spacing: -0.5px;
}
.sidebar-nav {
  padding: 1rem 0.75rem; display: flex;
  flex-direction: column; gap: 0.25rem; flex: 1;
}
.nav-item {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.75rem 1rem; border-radius: 0.5rem;
  font-size: 0.9rem; font-weight: 500; color: #94a3b8;
  cursor: pointer; transition: all 0.2s; text-decoration: none;
}
.nav-item:hover { background: #1e293b; color: white; }
.nav-item.active { background: #1e293b; color: white; }
.nav-icon { font-size: 1rem; }
.sidebar-footer {
  padding: 1rem 1.25rem; border-top: 1px solid #1e293b;
  display: flex; flex-direction: column; gap: 0.75rem;
}
.org-info { display: flex; align-items: center; gap: 0.75rem; }
.org-avatar {
  width: 36px; height: 36px; border-radius: 50%; background: #4f46e5;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 700; color: white; flex-shrink: 0;
}
.org-name { font-size: 0.875rem; font-weight: 600; color: white; }
.org-role { font-size: 0.75rem; color: #64748b; }
.btn-logout {
  width: 100%; padding: 0.5rem; background: #1e293b; color: #94a3b8;
  border: none; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600;
  cursor: pointer; transition: all 0.2s;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.btn-logout:hover { background: #ef4444; color: white; }

/* ── MAIN ── */
.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

/* ── TOPBAR ── */
.topbar {
  background: white; padding: 1.25rem 2rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex; align-items: center; justify-content: space-between;
  position: sticky; top: 0; z-index: 10;
}
.page-title { font-size: 1.4rem; font-weight: 800; color: #0f172a; }
.page-sub { font-size: 0.8rem; color: #94a3b8; margin-top: 0.2rem; }
.btn-create {
  background: #4f46e5; color: white; padding: 0.6rem 1.25rem;
  border-radius: 0.5rem; font-size: 0.875rem; font-weight: 700;
  text-decoration: none; transition: background 0.2s;
}
.btn-create:hover { background: #4338ca; }

/* ── TOAST ── */
.toast-success {
  margin: 1rem 2rem 0; background: #f0fdf4; border: 1px solid #bbf7d0;
  color: #15803d; padding: 0.875rem 1.25rem; border-radius: 0.5rem;
  font-size: 0.9rem; font-weight: 600;
}

/* ── TAB CONTENT ── */
.tab-content { padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem; }

/* ── STATS ── */
.stats-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;
}
.stat-card {
  background: white; border-radius: 1rem; padding: 1.5rem;
  border: 1px solid #e2e8f0; display: flex; align-items: center;
  gap: 1rem; transition: box-shadow 0.2s;
}
.stat-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.07); }
.stat-icon {
  width: 48px; height: 48px; border-radius: 0.75rem;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem; flex-shrink: 0;
}
.stat-icon.blue   { background: #eff6ff; }
.stat-icon.green  { background: #f0fdf4; }
.stat-icon.purple { background: #f5f3ff; }
.stat-icon.amber  { background: #fffbeb; }
.stat-num { display: block; font-size: 1.8rem; font-weight: 900; color: #0f172a; line-height: 1; }
.stat-label { font-size: 0.78rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem; display: block; }

/* ── TABLE CARD ── */
.table-card { background: white; border-radius: 1rem; border: 1px solid #e2e8f0; overflow: hidden; }
.table-header {
  padding: 1.25rem 1.5rem; display: flex; align-items: center;
  justify-content: space-between; border-bottom: 1px solid #f1f5f9;
}
.table-header h3 { font-size: 1rem; font-weight: 700; color: #0f172a; }
.btn-view-all {
  font-size: 0.82rem; color: #4f46e5; font-weight: 600;
  background: none; border: none; cursor: pointer;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.empty-table { padding: 2rem; text-align: center; color: #64748b; font-size: 0.9rem; }
.empty-table a { color: #4f46e5; font-weight: 600; text-decoration: none; }

/* ── DATA TABLE ── */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
  padding: 0.75rem 1.5rem; text-align: left; font-size: 0.75rem;
  font-weight: 700; color: #94a3b8; text-transform: uppercase;
  letter-spacing: 0.05em; background: #f8fafc; border-bottom: 1px solid #f1f5f9;
}
.data-table td {
  padding: 0.9rem 1.5rem; border-bottom: 1px solid #f8fafc;
  font-size: 0.875rem; color: #334155; vertical-align: middle;
}
.data-table tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: #f8fafc; }
.cell-name { font-weight: 600; color: #0f172a; font-size: 0.875rem; }
.cell-sub  { font-size: 0.78rem; color: #94a3b8; margin-top: 0.1rem; }
.cell-muted { color: #94a3b8; font-size: 0.82rem; }

/* ── BADGES ── */
.status-badge {
  padding: 0.25rem 0.65rem; border-radius: 999px;
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
}
.status-badge.open     { background: #dcfce7; color: #15803d; }
.status-badge.closed   { background: #fef3c7; color: #b45309; }
.status-badge.finished { background: #f1f5f9; color: #64748b; }

/* ── REG CELL ── */
.reg-cell { display: flex; flex-direction: column; gap: 0.3rem; }
.reg-count { font-size: 0.82rem; font-weight: 600; color: #0f172a; }
.mini-bar { height: 4px; background: #f1f5f9; border-radius: 999px; overflow: hidden; width: 80px; }
.mini-fill { height: 100%; border-radius: 999px; }

/* ── ACTION BTNS ── */
.action-btns { display: flex; gap: 0.5rem; }
.btn-action {
  padding: 0.3rem 0.75rem; border-radius: 0.375rem; font-size: 0.78rem;
  font-weight: 600; text-decoration: none; transition: all 0.2s; border: none; cursor: pointer;
}
.btn-action.edit { background: #eff6ff; color: #1d4ed8; }
.btn-action.view { background: #f5f3ff; color: #6d28d9; }
.btn-action.edit:hover { background: #3b82f6; color: white; }
.btn-action.view:hover { background: #7c3aed; color: white; }

/* ── FILTER BAR ── */
.filter-bar { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.search-wrap { position: relative; display: flex; align-items: center; flex: 1; }
.search-icon { position: absolute; left: 0.85rem; font-size: 0.9rem; pointer-events: none; }
.search-input {
  width: 100%; padding: 0.65rem 1rem 0.65rem 2.4rem;
  border: 1.5px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem;
  background: white; color: #0f172a; outline: none; transition: border-color 0.2s;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.search-input:focus { border-color: #4f46e5; }
.sort-select {
  padding: 0.65rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 0.5rem;
  font-size: 0.9rem; background: white; color: #0f172a; outline: none; cursor: pointer;
  font-family: 'Segoe UI', system-ui, sans-serif;
}

/* ── EVENTS GRID ── */
.events-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;
}
.event-card {
  background: white; border-radius: 1rem; padding: 1.5rem;
  border: 1px solid #e2e8f0; display: flex; flex-direction: column;
  gap: 0.85rem; transition: box-shadow 0.2s, transform 0.2s;
}
.event-card:hover { box-shadow: 0 10px 32px rgba(0,0,0,0.09); transform: translateY(-2px); }
.card-header { display: flex; align-items: center; justify-content: space-between; }
.card-actions-top { display: flex; gap: 0.4rem; }
.icon-btn {
  background: none; border: none; cursor: pointer; font-size: 1rem;
  padding: 0.3rem; border-radius: 0.375rem; transition: background 0.2s; text-decoration: none;
}
.icon-btn:hover { background: #f1f5f9; }
.icon-btn.delete:hover { background: #fee2e2; }
.card-title { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
.card-desc { font-size: 0.875rem; color: #64748b; line-height: 1.6; flex: 1; }
.card-meta { display: flex; flex-direction: column; gap: 0.3rem; font-size: 0.8rem; color: #475569; }
.capacity-section { display: flex; flex-direction: column; gap: 0.4rem; }
.capacity-info { display: flex; justify-content: space-between; font-size: 0.78rem; color: #64748b; font-weight: 600; }
.capacity-bar { height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
.capacity-fill { height: 100%; border-radius: 999px; transition: width 0.4s; }
.fill-green { background: #22c55e; }
.fill-amber { background: #f59e0b; }
.fill-red   { background: #ef4444; }
.card-footer {
  display: flex; gap: 0.75rem; padding-top: 0.5rem; border-top: 1px solid #f1f5f9;
}
.btn-participants {
  flex: 1; padding: 0.6rem; background: #f5f3ff; color: #6d28d9;
  border-radius: 0.5rem; font-size: 0.82rem; font-weight: 600;
  text-decoration: none; text-align: center; transition: background 0.2s;
}
.btn-participants:hover { background: #7c3aed; color: white; }
.btn-edit {
  flex: 1; padding: 0.6rem; background: #4f46e5; color: white;
  border-radius: 0.5rem; font-size: 0.82rem; font-weight: 700;
  text-decoration: none; text-align: center; transition: background 0.2s;
}
.btn-edit:hover { background: #4338ca; }

/* ── EMPTY STATE ── */
.empty-state {
  text-align: center; padding: 5rem 2rem;
  display: flex; flex-direction: column; align-items: center; gap: 1rem;
}
.empty-icon { font-size: 3rem; }
.empty-state h3 { font-size: 1.25rem; font-weight: 700; color: #0f172a; }
.empty-state p { font-size: 0.95rem; color: #64748b; }
.btn-primary {
  background: #4f46e5; color: white; padding: 0.7rem 1.5rem;
  border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700;
  text-decoration: none; transition: background 0.2s;
}
.btn-primary:hover { background: #4338ca; }

/* ── DELETE MODAL ── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15,23,42,0.7);
  -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px);
  z-index: 200; display: flex; align-items: center; justify-content: center; padding: 1rem;
}
.modal {
  background: white; border-radius: 1rem; padding: 2rem; max-width: 440px; width: 100%;
  box-shadow: 0 24px 60px rgba(0,0,0,0.2); display: flex; flex-direction: column; gap: 1rem;
}
.modal-title { font-size: 1.2rem; font-weight: 800; color: #0f172a; }
.modal-body { font-size: 0.9rem; color: #475569; line-height: 1.65; }
.modal-body strong { color: #0f172a; }
.modal-footer { display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 0.5rem; }
.btn-modal-cancel {
  padding: 0.65rem 1.25rem; border: 1.5px solid #e2e8f0; color: #64748b;
  border-radius: 0.5rem; font-size: 0.9rem; font-weight: 600; background: white;
  cursor: pointer; transition: all 0.2s; font-family: 'Segoe UI', system-ui, sans-serif;
}
.btn-modal-cancel:hover { border-color: #94a3b8; }
.btn-modal-delete {
  padding: 0.65rem 1.25rem; background: #ef4444; color: white; border: none;
  border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700; cursor: pointer;
  transition: background 0.2s; font-family: 'Segoe UI', system-ui, sans-serif;
}
.btn-modal-delete:hover { background: #dc2626; }

/* ── TRANSITIONS ── */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── RESPONSIVE ── */
@media (max-width: 1024px) { .sidebar { display: none; } }
@media (max-width: 640px) {
  .topbar { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .events-grid { grid-template-columns: 1fr; }
}
</style>
