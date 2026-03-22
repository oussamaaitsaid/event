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
        <span class="status-badge" :class="event.status">{{ event.status }}</span>
        <h1 class="page-title">{{ event.title }}</h1>
        <div class="event-meta">
          <span>📅 {{ event.date }}</span>
          <span>📍 {{ event.location }}</span>
          <span>👥 {{ event.capacity }} capacity</span>
        </div>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="content-wrapper">

      <!-- Stats Row -->
      <div class="stats-row">
        <div class="mini-stat">
          <span class="mini-num">{{ totalRegistered }}</span>
          <span class="mini-label">Registered</span>
        </div>
        <div class="mini-stat">
          <span class="mini-num">{{ totalCancelled }}</span>
          <span class="mini-label">Cancelled</span>
        </div>
        <div class="mini-stat">
          <span class="mini-num">{{ spotsLeft }}</span>
          <span class="mini-label">Spots Left</span>
        </div>
        <div class="mini-stat">
          <span class="mini-num">{{ capacityPct }}%</span>
          <span class="mini-label">Filled</span>
        </div>
      </div>

      <!-- Capacity Bar -->
      <div class="capacity-card">
        <div class="capacity-info">
          <span>{{ totalRegistered }} registered out of {{ event.capacity }}</span>
          <span>{{ capacityPct }}% full</span>
        </div>
        <div class="capacity-bar">
          <div
            class="capacity-fill"
            :style="{ width: capacityPct + '%' }"
            :class="getCapacityFillClass()"
          ></div>
        </div>
      </div>

      <!-- Table Card -->
      <div class="table-card">
        <div class="table-header">
          <h3>Participants List</h3>
          <div class="header-right">
            <!-- Search -->
            <div class="search-wrap">
              <span class="search-icon">🔍</span>
              <input
                v-model="search"
                type="text"
                placeholder="Search participants..."
                class="search-input"
              />
            </div>
            <!-- Filter -->
            <select v-model="filterStatus" class="sort-select">
              <option value="all">All</option>
              <option value="registered">Registered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>

        <!-- Empty -->
        <div v-if="filteredParticipants.length === 0" class="empty-state">
          <div class="empty-icon">👥</div>
          <h3>No participants found</h3>
          <p>{{ search ? 'Try a different search term.' : 'No one has registered yet.' }}</p>
        </div>

        <!-- Table -->
        <table v-else class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Participant</th>
              <th>Email</th>
              <th>Status</th>
              <th>Registered On</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, index) in filteredParticipants" :key="p.id">
              <td class="cell-muted">{{ index + 1 }}</td>
              <td>
                <div class="user-cell">
                  <div class="mini-avatar">{{ initials(p.name) }}</div>
                  <span class="cell-name">{{ p.name }}</span>
                </div>
              </td>
              <td class="cell-email">{{ p.email }}</td>
              <td>
                <span class="reg-badge" :class="p.status">{{ p.status }}</span>
              </td>
              <td class="cell-muted">{{ p.registered_at }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Footer count -->
        <div class="table-footer" v-if="filteredParticipants.length > 0">
          Showing {{ filteredParticipants.length }} of {{ participants.length }} participants
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
import { router } from '@inertiajs/vue3';

const props = defineProps({
  event:        { type: Object, required: true },
  participants: { type: Array,  default: () => [] },
});

const search       = ref('');
const filterStatus = ref('all');

const totalRegistered = computed(() =>
  props.participants.filter(p => p.status === 'registered').length
);
const totalCancelled = computed(() =>
  props.participants.filter(p => p.status === 'cancelled').length
);
const spotsLeft = computed(() =>
  props.event.capacity - totalRegistered.value
);
const capacityPct = computed(() =>
  Math.min(100, Math.round((totalRegistered.value / props.event.capacity) * 100))
);

const filteredParticipants = computed(() =>
  props.participants.filter(p => {
    const matchSearch =
      p.name.toLowerCase().includes(search.value.toLowerCase()) ||
      p.email.toLowerCase().includes(search.value.toLowerCase());
    const matchStatus =
      filterStatus.value === 'all' || p.status === filterStatus.value;
    return matchSearch && matchStatus;
  })
);

function initials(name) {
  return name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) ?? '??';
}

function getCapacityFillClass() {
  const pct = capacityPct.value;
  if (pct >= 90) return 'fill-red';
  if (pct >= 60) return 'fill-amber';
  return 'fill-green';
}

function logout() {
  router.post('/logout');
}
</script>

<style scoped>
@import './css/Participants.css';
</style>

