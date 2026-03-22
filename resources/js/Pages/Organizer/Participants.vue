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
* { box-sizing: border-box; margin: 0; padding: 0; }

.org-page {
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
  max-width: 1100px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between;
}
.logo { font-size: 1.3rem; font-weight: 800; color: #4f46e5; text-decoration: none; }
.nav-right { display: flex; align-items: center; gap: 0.875rem; }
.role-chip {
  background: #eff6ff; color: #1d4ed8; padding: 0.25rem 0.75rem;
  border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
}
.btn-outline {
  border: 1.5px solid #e2e8f0; color: #475569; padding: 0.45rem 1rem;
  border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600;
  text-decoration: none; transition: all 0.2s;
}
.btn-outline:hover { border-color: #4f46e5; color: #4f46e5; }
.btn-logout {
  background: none; border: 1.5px solid #e2e8f0; color: #64748b;
  padding: 0.45rem 1rem; border-radius: 0.5rem; font-size: 0.875rem;
  font-weight: 600; cursor: pointer; transition: all 0.2s;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.btn-logout:hover { border-color: #ef4444; color: #ef4444; }

/* ── PAGE HEADER ── */
.page-header {
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #312e81 100%);
  padding: 2.5rem 2rem;
}
.page-header-inner {
  max-width: 1100px; margin: 0 auto;
  display: flex; flex-direction: column; gap: 0.6rem;
}
.page-title { font-size: 1.75rem; font-weight: 800; color: white; }
.event-meta {
  display: flex; flex-wrap: wrap; gap: 1.25rem;
  font-size: 0.875rem; color: #c7d2fe;
}

/* ── STATUS BADGE ── */
.status-badge {
  display: inline-block; padding: 0.3rem 0.85rem; border-radius: 999px;
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase; align-self: flex-start;
}
.status-badge.open     { background: #dcfce7; color: #15803d; }
.status-badge.closed   { background: #fef3c7; color: #b45309; }
.status-badge.finished { background: #f1f5f9; color: #64748b; }

/* ── CONTENT ── */
.content-wrapper {
  max-width: 1100px; margin: 2rem auto;
  padding: 0 2rem; width: 100%; flex: 1;
  display: flex; flex-direction: column; gap: 1.5rem;
}

/* ── STATS ROW ── */
.stats-row {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;
}
.mini-stat {
  background: white; border-radius: 0.875rem; padding: 1.25rem;
  border: 1px solid #e2e8f0; text-align: center;
  transition: box-shadow 0.2s;
}
.mini-stat:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); }
.mini-num { display: block; font-size: 1.75rem; font-weight: 900; color: #4f46e5; line-height: 1; }
.mini-label { font-size: 0.78rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem; display: block; }

/* ── CAPACITY CARD ── */
.capacity-card {
  background: white; border-radius: 0.875rem; padding: 1.25rem;
  border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 0.5rem;
}
.capacity-info {
  display: flex; justify-content: space-between;
  font-size: 0.85rem; color: #64748b; font-weight: 600;
}
.capacity-bar { height: 8px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
.capacity-fill { height: 100%; border-radius: 999px; transition: width 0.5s ease; }
.fill-green { background: #22c55e; }
.fill-amber { background: #f59e0b; }
.fill-red   { background: #ef4444; }

/* ── TABLE CARD ── */
.table-card {
  background: white; border-radius: 1rem;
  border: 1px solid #e2e8f0; overflow: hidden;
}
.table-header {
  padding: 1.25rem 1.5rem; display: flex; align-items: center;
  justify-content: space-between; border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap; gap: 1rem;
}
.table-header h3 { font-size: 1rem; font-weight: 700; color: #0f172a; }
.header-right { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.search-wrap { position: relative; display: flex; align-items: center; }
.search-icon { position: absolute; left: 0.85rem; font-size: 0.85rem; pointer-events: none; }
.search-input {
  padding: 0.55rem 1rem 0.55rem 2.2rem; border: 1.5px solid #e2e8f0;
  border-radius: 0.5rem; font-size: 0.875rem; background: white; color: #0f172a;
  outline: none; transition: border-color 0.2s; width: 200px;
  font-family: 'Segoe UI', system-ui, sans-serif;
}
.search-input:focus { border-color: #4f46e5; }
.sort-select {
  padding: 0.55rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 0.5rem;
  font-size: 0.875rem; background: white; color: #0f172a;
  outline: none; cursor: pointer; font-family: 'Segoe UI', system-ui, sans-serif;
}

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

/* ── CELLS ── */
.user-cell { display: flex; align-items: center; gap: 0.75rem; }
.mini-avatar {
  width: 32px; height: 32px; border-radius: 50%; background: #eef2ff;
  color: #4f46e5; display: flex; align-items: center; justify-content: center;
  font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
}
.cell-name  { font-weight: 600; color: #0f172a; }
.cell-email { color: #64748b; font-size: 0.85rem; }
.cell-muted { color: #94a3b8; font-size: 0.82rem; }

/* ── REG BADGE ── */
.reg-badge {
  padding: 0.25rem 0.65rem; border-radius: 999px;
  font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
}
.reg-badge.registered { background: #dcfce7; color: #15803d; }
.reg-badge.cancelled  { background: #fee2e2; color: #b91c1c; }

/* ── TABLE FOOTER ── */
.table-footer {
  padding: 0.875rem 1.5rem; font-size: 0.82rem; color: #94a3b8;
  border-top: 1px solid #f1f5f9; background: #f8fafc;
}

/* ── EMPTY STATE ── */
.empty-state {
  padding: 4rem 2rem; text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: 0.75rem;
}
.empty-icon { font-size: 2.5rem; }
.empty-state h3 { font-size: 1.1rem; font-weight: 700; color: #0f172a; }
.empty-state p { font-size: 0.9rem; color: #64748b; }

/* ── FOOTER ── */
.footer {
  text-align: center; padding: 1.5rem; color: #94a3b8;
  font-size: 0.85rem; border-top: 1px solid #e2e8f0;
  background: white; margin-top: 2rem;
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
  .stats-row { grid-template-columns: 1fr 1fr; }
  .table-header { flex-direction: column; align-items: flex-start; }
  .header-right { width: 100%; }
  .search-input { width: 100%; }
}
@media (max-width: 480px) {
  .stats-row { grid-template-columns: 1fr 1fr; }
  .event-meta { flex-direction: column; gap: 0.4rem; }
}
</style>

