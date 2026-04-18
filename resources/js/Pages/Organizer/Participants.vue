<template>
  <div class="org-page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-inner">
        <a href="/organizer/events" class="logo">⚡ EventHub</a>
        <div class="nav-right">
          <a href="/organizer/events" class="btn-outline">← {{ t('org.myEvents') }}</a>
          <span class="role-chip">{{ t('org.organizer') }}</span>
          <div class="lang-switcher">
            <button class="lang-btn" :class="{ active: locale === 'en' }" @click="switchLang('en')">EN</button>
            <span class="lang-sep">|</span>
            <button class="lang-btn" :class="{ active: locale === 'fr' }" @click="switchLang('fr')">FR</button>
          </div>
          <button @click="logout" class="btn-logout">{{ t('org.logout') }}</button>
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
          <span>👥 {{ event.capacity }} {{ t('org.capacity') }}</span>
        </div>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="content-wrapper">

      <div class="stats-row">
        <div class="mini-stat">
          <span class="mini-num">{{ totalRegistered }}</span>
          <span class="mini-label">{{ t('org.registered') }}</span>
        </div>
        <div class="mini-stat">
          <span class="mini-num">{{ totalCancelled }}</span>
          <span class="mini-label">{{ t('org.cancelled') }}</span>
        </div>
        <div class="mini-stat">
          <span class="mini-num">{{ spotsLeft }}</span>
          <span class="mini-label">{{ t('org.spotsLeft') }}</span>
        </div>
        <div class="mini-stat">
          <span class="mini-num">{{ capacityPct }}%</span>
          <span class="mini-label">{{ t('org.filled') }}</span>
        </div>
      </div>

      <div class="capacity-card">
        <div class="capacity-info">
          <span>{{ totalRegistered }} {{ t('org.registeredOutOf') }} {{ event.capacity }}</span>
          <span>{{ capacityPct }}% {{ t('org.full') }}</span>
        </div>
        <div class="capacity-bar">
          <div class="capacity-fill" :style="{ width: capacityPct + '%' }" :class="getCapacityFillClass()"></div>
        </div>
      </div>

      <div class="table-card">
        <div class="table-header">
          <h3>{{ t('org.participantsList') }}</h3>
          <div class="header-right">
            <div class="search-wrap">
              <span class="search-icon">🔍</span>
              <input v-model="search" type="text" :placeholder="t('org.searchParticipants')" class="search-input" />
            </div>
            <select v-model="filterStatus" class="sort-select">
              <option value="all">{{ t('org.all') }}</option>
              <option value="registered">{{ t('org.registered') }}</option>
              <option value="cancelled">{{ t('org.cancelled') }}</option>
            </select>
          </div>
        </div>

        <div v-if="filteredParticipants.length === 0" class="empty-state">
          <div class="empty-icon">👥</div>
          <h3>{{ t('org.noParticipantsFound') }}</h3>
          <p>{{ search ? t('org.tryDifferent') : t('org.noOneRegistered') }}</p>
        </div>

        <table v-else class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>{{ t('org.participant') }}</th>
              <th>{{ t('org.emailLabel') }}</th>
              <th>{{ t('org.status') }}</th>
              <th>{{ t('org.registeredOn') }}</th>
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
              <td><span class="reg-badge" :class="p.status">{{ p.status }}</span></td>
              <td class="cell-muted">{{ p.registered_at }}</td>
            </tr>
          </tbody>
        </table>

        <div class="table-footer" v-if="filteredParticipants.length > 0">
          {{ t('org.showing') }} {{ filteredParticipants.length }} {{ t('org.of') }} {{ participants.length }} {{ t('org.participantsCount') }}
        </div>
      </div>
    </div>

    <footer class="footer">
      <p>© 2026 EventHub · {{ t('org.builtWith') }}</p>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps({
  event:        { type: Object, required: true },
  participants: { type: Array,  default: () => [] },
});

const search       = ref('');
const filterStatus = ref('all');

function switchLang(lang) {
  locale.value = lang;
  localStorage.setItem('locale', lang);
}

const totalRegistered = computed(() => props.participants.filter(p => p.status === 'registered').length);
const totalCancelled  = computed(() => props.participants.filter(p => p.status === 'cancelled').length);
const spotsLeft       = computed(() => props.event.capacity - totalRegistered.value);
const capacityPct     = computed(() => Math.min(100, Math.round((totalRegistered.value / props.event.capacity) * 100)));

const filteredParticipants = computed(() =>
  props.participants.filter(p => {
    const matchSearch = p.name.toLowerCase().includes(search.value.toLowerCase()) ||
                        p.email.toLowerCase().includes(search.value.toLowerCase());
    const matchStatus = filterStatus.value === 'all' || p.status === filterStatus.value;
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
function logout() { router.post('/logout'); }
</script>

<style scoped>
@import './css/Participants.css';


</style>
