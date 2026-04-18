<template>
  <div class="page">

    <!-- NAVBAR -->
    <nav class="navbar">
      <div class="nav-inner">

        <!-- Logo -->
        <a href="/" class="logo">
          <div class="logo-badge">⚡</div>
          <span class="logo-text">Event<span class="logo-accent">Hub</span></span>
        </a>

        <!-- Center Links -->
        <div class="nav-center">
          <a href="#events" class="nav-link">
            <span class="nav-dot"></span>
            {{ events.length }} {{ t('nav.openNow') }}
          </a>
        </div>

        <!-- Right Actions -->
        <div class="nav-links">
          <!-- Language Switcher -->
          <div class="lang-switcher">
            <button
              class="lang-btn"
              :class="{ active: locale === 'en' }"
              @click="switchLang('en')"
            >EN</button>
            <span class="lang-sep">|</span>
            <button
              class="lang-btn"
              :class="{ active: locale === 'fr' }"
              @click="switchLang('fr')"
            >FR</button>
          </div>
          <div class="nav-divider"></div>
          <a href="/login" class="btn-ghost">{{ t('nav.signIn') }}</a>
          <a href="/register" class="btn-primary">
            {{ t('nav.getStarted') }}
            <span class="btn-arrow">→</span>
          </a>
        </div>

        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" @click="mobileOpen = !mobileOpen">
          <span></span><span></span><span></span>
        </button>

      </div>

      <!-- Mobile Menu -->
      <transition name="slide">
        <div v-if="mobileOpen" class="mobile-menu">
          <div class="mobile-divider"></div>
          <!-- Mobile Language Switcher -->
          <div class="mobile-lang">
            <button class="lang-btn" :class="{ active: locale === 'en' }" @click="switchLang('en')">EN</button>
            <span class="lang-sep">|</span>
            <button class="lang-btn" :class="{ active: locale === 'fr' }" @click="switchLang('fr')">FR</button>
          </div>
          <div class="mobile-divider"></div>
          <a href="/login" class="mobile-link">{{ t('nav.signIn') }}</a>
          <a href="/register" class="mobile-btn">{{ t('nav.getStarted') }} →</a>
        </div>
      </transition>
    </nav>

    <!-- HERO -->
    <section class="hero">
      <div class="hero-content">
        <div class="hero-badge">{{ t('hero.badge') }}</div>
        <h1 class="hero-title">
          {{ t('hero.title1') }}<br />
          <span class="accent">{{ t('hero.title2') }}</span>
        </h1>
        <p class="hero-sub">{{ t('hero.sub') }}</p>
        <div class="hero-actions">
          <a href="#events" class="btn-hero">{{ t('hero.browse') }}</a>
          <a href="/register" class="btn-hero-ghost">{{ t('hero.organizer') }}</a>
        </div>
      </div>
      <div class="hero-orb orb-1"></div>
      <div class="hero-orb orb-2"></div>
      <div class="hero-orb orb-3"></div>
    </section>

    <!-- STATS BAR -->
    <section class="stats-bar">
      <div class="stat">
        <span class="stat-num">{{ events.length }}</span>
        <span class="stat-label">{{ t('stats.openEvents') }}</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat">
        <span class="stat-num">{{ totalSpots }}</span>
        <span class="stat-label">{{ t('stats.spotsAvailable') }}</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat">
        <span class="stat-num">100%</span>
        <span class="stat-label">{{ t('stats.freeToJoin') }}</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat">
        <span class="stat-num">∞</span>
        <span class="stat-label">{{ t('stats.possibilities') }}</span>
      </div>
    </section>

    <!-- HOW IT WORKS -->
    <section id="how" class="how-section">
      <div class="how-inner">
        <p class="section-eyebrow">{{ t('how.eyebrow') }}</p>
        <h2 class="section-title">{{ t('how.title') }}</h2>
        <div class="steps-grid">
          <div class="step-card">
            <div class="step-icon">🔍</div>
            <div class="step-num">01</div>
            <h3>{{ t('how.step1Title') }}</h3>
            <p>{{ t('how.step1Desc') }}</p>
          </div>
          <div class="step-card">
            <div class="step-icon">📝</div>
            <div class="step-num">02</div>
            <h3>{{ t('how.step2Title') }}</h3>
            <p>{{ t('how.step2Desc') }}</p>
          </div>
          <div class="step-card">
            <div class="step-icon">🎉</div>
            <div class="step-num">03</div>
            <h3>{{ t('how.step3Title') }}</h3>
            <p>{{ t('how.step3Desc') }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- EVENTS SECTION -->
    <section id="events" class="events-section">
      <div class="events-header">
        <div>
          <p class="section-eyebrow">{{ t('events.eyebrow') }}</p>
          <h2 class="section-title">{{ t('events.title') }}</h2>
        </div>
        <div class="filter-bar">
          <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input
              v-model="search"
              type="text"
              :placeholder="t('events.searchPlaceholder')"
              class="search-input"
            />
          </div>
          <select v-model="sortBy" class="sort-select">
            <option value="date">{{ t('events.sortByDate') }}</option>
            <option value="spots">{{ t('events.mostSpots') }}</option>
            <option value="title">{{ t('events.aToZ') }}</option>
          </select>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="filteredEvents.length === 0" class="empty-state">
        <div class="empty-icon">🗓</div>
        <h3>{{ t('events.noEventsFound') }}</h3>
        <p>{{ search ? t('events.tryDifferent') : t('events.noOpenEvents') }}</p>
        <button v-if="search" @click="search = ''" class="btn-outline">
          {{ t('events.clearSearch') }}
        </button>
      </div>

      <!-- Grid -->
      <div v-else class="events-grid">
        <div
          v-for="event in filteredEvents"
          :key="event.id"
          class="event-card"
          @click="openModal(event)"
        >
          <div class="card-top">
            <div class="card-badge" :class="getBadgeClass(event.spots_left)">
              {{ event.spots_left > 0 ? event.spots_left + ' ' + t('events.spotsLeft') : t('events.full') }}
            </div>
            <div class="card-date-chip">{{ formatShortDate(event.date) }}</div>
          </div>
          <h3 class="card-title">{{ event.title }}</h3>
          <p class="card-desc">{{ truncate(event.description, 110) }}</p>
          <div class="card-meta">
            <span class="meta-item">📍 {{ event.location }}</span>
            <span class="meta-item">👤 {{ event.organizer }}</span>
            <span class="meta-item">👥 {{ event.capacity }} {{ t('events.capacity') }}</span>
          </div>
          <div class="card-footer">
            <div class="capacity-bar">
              <div
                class="capacity-fill"
                :style="{ width: getCapacityPct(event) + '%' }"
                :class="getCapacityFillClass(event)"
              ></div>
            </div>
            <button class="card-btn">{{ t('events.viewDetails') }}</button>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA BANNER -->
    <section class="cta-banner">
      <div class="cta-inner">
        <h2>{{ t('cta.title') }}</h2>
        <p>{{ t('cta.sub') }}</p>
        <a href="/register" class="btn-cta">{{ t('cta.btn') }}</a>
      </div>
    </section>

    <!-- MODAL -->
    <transition name="fade">
      <div v-if="selectedEvent" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <button class="modal-close" @click="closeModal" title="Close">✕</button>
          <div class="modal-header">
            <div class="modal-badge" :class="getBadgeClass(selectedEvent.spots_left)">
              {{ selectedEvent.spots_left > 0 ? selectedEvent.spots_left + ' ' + t('modal.spotsRemaining') : t('modal.eventIsFull') }}
            </div>
            <h2 class="modal-title">{{ selectedEvent.title }}</h2>
          </div>
          <div class="modal-meta-grid">
            <div class="modal-meta-item">
              <span class="meta-icon">📅</span>
              <div>
                <span class="meta-label">{{ t('modal.dateTime') }}</span>
                <span class="meta-value">{{ formatDate(selectedEvent.date) }}</span>
              </div>
            </div>
            <div class="modal-meta-item">
              <span class="meta-icon">📍</span>
              <div>
                <span class="meta-label">{{ t('modal.location') }}</span>
                <span class="meta-value">{{ selectedEvent.location }}</span>
              </div>
            </div>
            <div class="modal-meta-item">
              <span class="meta-icon">👤</span>
              <div>
                <span class="meta-label">{{ t('modal.organizer') }}</span>
                <span class="meta-value">{{ selectedEvent.organizer }}</span>
              </div>
            </div>
            <div class="modal-meta-item">
              <span class="meta-icon">👥</span>
              <div>
                <span class="meta-label">{{ t('modal.capacity') }}</span>
                <span class="meta-value">{{ selectedEvent.capacity }} {{ t('modal.participantsMax') }}</span>
              </div>
            </div>
          </div>
          <div class="modal-body">
            <h4>{{ t('modal.aboutEvent') }}</h4>
            <p>{{ selectedEvent.description }}</p>
          </div>
          <div class="modal-capacity">
            <div class="capacity-info">
              <span>{{ selectedEvent.capacity - selectedEvent.spots_left }} {{ t('modal.registered') }}</span>
              <span>{{ selectedEvent.capacity }} {{ t('modal.total') }}</span>
            </div>
            <div class="capacity-bar large">
              <div
                class="capacity-fill"
                :style="{ width: getCapacityPct(selectedEvent) + '%' }"
                :class="getCapacityFillClass(selectedEvent)"
              ></div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-outline" @click="closeModal">{{ t('modal.close') }}</button>
            <a v-if="selectedEvent.spots_left > 0" href="/register" class="btn-primary large">
              {{ t('modal.registerToJoin') }}
            </a>
            <span v-else class="btn-disabled">{{ t('modal.registrationClosed') }}</span>
          </div>
        </div>
      </div>
    </transition>

    <!-- FOOTER -->
    <footer class="footer">
      <div class="footer-inner">
        <span class="logo-text">⚡ Event<span class="logo-accent">Hub</span></span>
        <p>© 2026 EventHub · {{ t('footer.built') }}</p>
        <div class="footer-links">
          <a href="/login">{{ t('footer.login') }}</a>
          <a href="/register">{{ t('footer.register') }}</a>
        </div>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps({
  events: { type: Array, default: () => [] },
});

const selectedEvent = ref(null);
const search        = ref('');
const sortBy        = ref('date');
const mobileOpen    = ref(false);

// Switch language and save to localStorage
function switchLang(lang) {
  locale.value = lang;
  localStorage.setItem('locale', lang);
  mobileOpen.value = false;
}

const totalSpots = computed(() =>
  props.events.reduce((sum, e) => sum + e.spots_left, 0)
);

const filteredEvents = computed(() => {
  let list = props.events.filter(e =>
    e.title.toLowerCase().includes(search.value.toLowerCase()) ||
    e.location.toLowerCase().includes(search.value.toLowerCase()) ||
    e.organizer.toLowerCase().includes(search.value.toLowerCase())
  );
  if (sortBy.value === 'date')  list = [...list].sort((a, b) => new Date(a.date) - new Date(b.date));
  if (sortBy.value === 'spots') list = [...list].sort((a, b) => b.spots_left - a.spots_left);
  if (sortBy.value === 'title') list = [...list].sort((a, b) => a.title.localeCompare(b.title));
  return list;
});

function openModal(event) {
  selectedEvent.value = event;
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  selectedEvent.value = null;
  document.body.style.overflow = '';
}
function truncate(text, length) {
  return text && text.length > length ? text.slice(0, length) + '…' : text;
}
function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
    weekday: 'long', year: 'numeric', month: 'long',
    day: 'numeric', hour: '2-digit', minute: '2-digit',
  });
}
function formatShortDate(dateStr) {
  return new Date(dateStr).toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
  });
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
</script>

<style scoped>
@import './css/Landing.css';
</style>
