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
@import './css/Events.css';
</style>
