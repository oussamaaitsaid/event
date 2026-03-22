<template>
  <div class="admin-page">

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-logo">⚡ EventHub</div>
      <nav class="sidebar-nav">
      <!-- ✅ After -->
        <a class="nav-item" @click="activeTab = 'overview'" :class="{ active: activeTab === 'overview' }">
        <span class="nav-icon">📊</span> Dashboard
        </a>
        <a class="nav-item" @click="activeTab = 'users'" :class="{ active: activeTab === 'users' }">
        <span class="nav-icon">👥</span> Users
        </a>
        <a class="nav-item" @click="activeTab = 'events'" :class="{ active: activeTab === 'events' }">
        <span class="nav-icon">📅</span> Events
        </a>
        <a class="nav-item" href="/" >
          <span class="nav-icon">🌐</span> Public Page
        </a>
      </nav>
      <div class="sidebar-footer">
        <div class="admin-info">
          <div class="admin-avatar">{{ userInitials }}</div>
          <div>
            <p class="admin-name">{{ user.name }}</p>
            <p class="admin-role">Administrator</p>
          </div>
        </div>
        <button @click="logout" class="btn-logout">Logout</button>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main">

      <!-- TOP BAR -->
      <header class="topbar">
        <div>
          <h1 class="page-title">
            {{ activeTab === 'overview' ? 'Dashboard Overview' :
               activeTab === 'users'    ? 'User Management' : 'Event Management' }}
          </h1>
          <p class="page-sub">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
        </div>
        <div class="topbar-right">
          <span class="admin-badge">Admin</span>
        </div>
      </header>

      <!-- ══ OVERVIEW TAB ══ -->
      <div v-if="activeTab === 'overview'" class="tab-content">

        <!-- Stat Cards -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon blue">👥</div>
            <div class="stat-info">
              <span class="stat-num">{{ stats.totalUsers }}</span>
              <span class="stat-label">Total Users</span>
            </div>
            <div class="stat-trend">+{{ stats.totalOrganizers }} organizers</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon purple">📅</div>
            <div class="stat-info">
              <span class="stat-num">{{ stats.totalEvents }}</span>
              <span class="stat-label">Total Events</span>
            </div>
            <div class="stat-trend">{{ stats.openEvents }} open</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green">📝</div>
            <div class="stat-info">
              <span class="stat-num">{{ stats.totalRegistrations }}</span>
              <span class="stat-label">Registrations</span>
            </div>
            <div class="stat-trend">{{ stats.registeredCount }} active</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon red">🚫</div>
            <div class="stat-info">
              <span class="stat-num">{{ stats.blockedUsers }}</span>
              <span class="stat-label">Blocked Users</span>
            </div>
            <div class="stat-trend">{{ stats.cancelledCount }} cancellations</div>
          </div>
        </div>

        <!-- Quick Tables Row -->
        <div class="tables-row">

          <!-- Recent Users -->
          <div class="table-card">
            <div class="table-header">
              <h3>Recent Users</h3>
              <button class="btn-view-all" @click="activeTab = 'users'">View All →</button>
            </div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Joined</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="u in recentUsers" :key="u.id">
                  <td>
                    <div class="user-cell">
                      <div class="mini-avatar">{{ initials(u.name) }}</div>
                      <div>
                        <p class="cell-name">{{ u.name }}</p>
                        <p class="cell-email">{{ u.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td><span class="role-badge" :class="u.role">{{ u.role }}</span></td>
                  <td><span class="status-badge" :class="u.status">{{ u.status }}</span></td>
                  <td class="cell-muted">{{ u.created_at }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Recent Events -->
          <div class="table-card">
            <div class="table-header">
              <h3>Recent Events</h3>
              <button class="btn-view-all" @click="activeTab = 'events'">View All →</button>
            </div>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Status</th>
                  <th>Registrations</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="e in events" :key="e.id">
                  <td>
                    <p class="cell-name">{{ e.title }}</p>
                    <p class="cell-email">{{ e.date }} · {{ e.location }}</p>
                  </td>
                  <td><span class="event-badge" :class="e.status">{{ e.status }}</span></td>
                  <td class="cell-center">{{ e.registrations_count }} / {{ e.capacity }}</td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>

      <!-- ══ USERS TAB ══ -->
      <div v-if="activeTab === 'users'" class="tab-content">
        <div class="table-card full">
          <div class="table-header">
            <h3>All Users</h3>
            <input v-model="userSearch" placeholder="🔍 Search users..." class="search-input" />
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>User</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in filteredUsers" :key="u.id">
                <td class="cell-muted">{{ u.id }}</td>
                <td>
                  <div class="user-cell">
                    <div class="mini-avatar">{{ initials(u.name) }}</div>
                    <div>
                      <p class="cell-name">{{ u.name }}</p>
                      <p class="cell-email">{{ u.email }}</p>
                    </div>
                  </div>
                </td>
                <td><span class="role-badge" :class="u.role">{{ u.role }}</span></td>
                <td><span class="status-badge" :class="u.status">{{ u.status }}</span></td>
                <td class="cell-muted">{{ u.created_at }}</td>
                <td>
                  <div class="action-btns" v-if="u.role !== 'admin'">
                    <button
                      class="btn-action"
                      :class="u.status === 'active' ? 'danger' : 'success'"
                      @click="toggleStatus(u)"
                    >
                      {{ u.status === 'active' ? 'Block' : 'Unblock' }}
                    </button>
                    <button class="btn-action info" @click="changeRole(u)">
                      → {{ u.role === 'user' ? 'Organizer' : 'User' }}
                    </button>
                  </div>
                  <span v-else class="cell-muted">—</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ══ EVENTS TAB ══ -->
      <div v-if="activeTab === 'events'" class="tab-content">
        <div class="table-card full">
          <div class="table-header">
            <h3>All Events</h3>
            <input v-model="eventSearch" placeholder="🔍 Search events..." class="search-input" />
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Event</th>
                <th>Organizer</th>
                <th>Date</th>
                <th>Status</th>
                <th>Registrations</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in filteredEvents" :key="e.id">
                <td class="cell-muted">{{ e.id }}</td>
                <td>
                  <p class="cell-name">{{ e.title }}</p>
                  <p class="cell-email">📍 {{ e.location }}</p>
                </td>
                <td class="cell-muted">{{ e.organizer }}</td>
                <td class="cell-muted">{{ e.date }}</td>
                <td><span class="event-badge" :class="e.status">{{ e.status }}</span></td>
                <td class="cell-center">{{ e.registrations_count }} / {{ e.capacity }}</td>
                <td>
                  <button class="btn-action danger" @click="deleteEvent(e)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps({
  stats:       { type: Object, default: () => ({}) },
  recentUsers: { type: Array,  default: () => [] },
  events:      { type: Array,  default: () => [] },
});

const page        = usePage();
const user        = page.props.auth.user;
const activeTab   = ref('overview');
const userSearch  = ref('');
const eventSearch = ref('');

const userInitials = computed(() => initials(user.name));

function initials(name) {
  return name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) ?? '??';
}

const filteredUsers = computed(() =>
  props.recentUsers.filter(u =>
    u.name.toLowerCase().includes(userSearch.value.toLowerCase()) ||
    u.email.toLowerCase().includes(userSearch.value.toLowerCase())
  )
);

const filteredEvents = computed(() =>
  props.events.filter(e =>
    e.title.toLowerCase().includes(eventSearch.value.toLowerCase()) ||
    e.location.toLowerCase().includes(eventSearch.value.toLowerCase())
  )
);

function toggleStatus(u) {
  router.patch(`/admin/users/${u.id}/toggle`);
}
function changeRole(u) {
  router.patch(`/admin/users/${u.id}/role`);
}
function deleteEvent(e) {
  if (confirm(`Delete "${e.title}"? This cannot be undone.`)) {
    router.delete(`/admin/events/${e.id}`);
  }
}
function logout() {
  router.post('/logout');
}
</script>

<style scoped>
@import './css/Dashboard.css';
</style>
