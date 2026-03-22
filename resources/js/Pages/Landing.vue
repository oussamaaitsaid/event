<template>
    <div class="page">
        <!-- NAVBAR -->
        <nav class="navbar">
            <div class="nav-inner">
                <span class="logo">
                    <span class="logo-icon">⚡</span>
                    EventHub
                </span>
                <div class="nav-links">
                    <a href="/login" class="btn-outline">Login</a>
                    <a href="/register" class="btn-primary">Get Started →</a>
                </div>
            </div>
        </nav>

        <!-- HERO -->
        <section class="hero">
            <div class="hero-content">
                <div class="hero-badge">🎯 Open registrations available</div>
                <h1 class="hero-title">
                    Find &amp; Join<br />
                    <span class="accent">Unforgettable Events</span>
                </h1>
                <p class="hero-sub">
                    Conferences, workshops, seminars, and competitions — curated
                    and organized in one place. Your next great experience
                    starts here.
                </p>
                <div class="hero-actions">
                    <a href="#events" class="btn-hero">Browse Events ↓</a>
                    <a href="/register" class="btn-hero-ghost"
                        >Become an Organizer</a
                    >
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
                <span class="stat-label">Open Events</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num">{{ totalSpots }}</span>
                <span class="stat-label">Spots Available</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num">100%</span>
                <span class="stat-label">Free to Join</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num">∞</span>
                <span class="stat-label">Possibilities</span>
            </div>
        </section>

        <!-- HOW IT WORKS -->
        <section class="how-section">
            <div class="how-inner">
                <p class="section-eyebrow">How it works</p>
                <h2 class="section-title">Three steps to your next event</h2>
                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-icon">🔍</div>
                        <div class="step-num">01</div>
                        <h3>Browse Events</h3>
                        <p>
                            Explore a curated list of open events. Filter by
                            date, location, or capacity to find your perfect
                            match.
                        </p>
                    </div>
                    <div class="step-card">
                        <div class="step-icon">📝</div>
                        <div class="step-num">02</div>
                        <h3>Create an Account</h3>
                        <p>
                            Sign up in seconds. Your personal dashboard lets you
                            track registrations and manage your participation.
                        </p>
                    </div>
                    <div class="step-card">
                        <div class="step-icon">🎉</div>
                        <div class="step-num">03</div>
                        <h3>Register &amp; Attend</h3>
                        <p>
                            Secure your spot with one click. Get confirmation
                            instantly and manage everything from your profile.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- EVENTS SECTION -->
        <section id="events" class="events-section">
            <div class="events-header">
                <div>
                    <p class="section-eyebrow">What's on</p>
                    <h2 class="section-title">Upcoming Events</h2>
                </div>
                <!-- Search & Filter -->
                <div class="filter-bar">
                    <div class="search-wrap">
                        <span class="search-icon">🔍</span>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search events..."
                            class="search-input"
                        />
                    </div>
                    <select v-model="sortBy" class="sort-select">
                        <option value="date">Sort by Date</option>
                        <option value="spots">Most Spots</option>
                        <option value="title">A → Z</option>
                    </select>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="filteredEvents.length === 0" class="empty-state">
                <div class="empty-icon">🗓</div>
                <h3>No events found</h3>
                <p>
                    {{
                        search
                            ? "Try a different search term."
                            : "No open events at the moment. Check back soon!"
                    }}
                </p>
                <button v-if="search" @click="search = ''" class="btn-outline">
                    Clear Search
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
                        <div
                            class="card-badge"
                            :class="getBadgeClass(event.spots_left)"
                        >
                            {{
                                event.spots_left > 0
                                    ? event.spots_left + " spots left"
                                    : "Full"
                            }}
                        </div>
                        <div class="card-date-chip">
                            {{ formatShortDate(event.date) }}
                        </div>
                    </div>
                    <h3 class="card-title">{{ event.title }}</h3>
                    <p class="card-desc">
                        {{ truncate(event.description, 110) }}
                    </p>
                    <div class="card-meta">
                        <span class="meta-item">📍 {{ event.location }}</span>
                        <span class="meta-item">👤 {{ event.organizer }}</span>
                        <span class="meta-item"
                            >👥 {{ event.capacity }} capacity</span
                        >
                    </div>
                    <div class="card-footer">
                        <div class="capacity-bar">
                            <div
                                class="capacity-fill"
                                :style="{ width: getCapacityPct(event) + '%' }"
                                :class="getCapacityFillClass(event)"
                            ></div>
                        </div>
                        <button class="card-btn">View Details →</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA BANNER -->
        <section class="cta-banner">
            <div class="cta-inner">
                <h2>Ready to host your own event?</h2>
                <p>
                    Join hundreds of organizers who trust EventHub to manage
                    their events end-to-end.
                </p>
                <a href="/register" class="btn-cta">Start Organizing Today →</a>
            </div>
        </section>

        <!-- MODAL -->
        <transition name="fade">
            <div
                v-if="selectedEvent"
                class="modal-overlay"
                @click.self="closeModal"
            >
                <div class="modal">
                    <button
                        class="modal-close"
                        @click="closeModal"
                        title="Close"
                    >
                        ✕
                    </button>

                    <div class="modal-header">
                        <div
                            class="modal-badge"
                            :class="getBadgeClass(selectedEvent.spots_left)"
                        >
                            {{
                                selectedEvent.spots_left > 0
                                    ? selectedEvent.spots_left +
                                      " spots remaining"
                                    : "Event is Full"
                            }}
                        </div>
                        <h2 class="modal-title">{{ selectedEvent.title }}</h2>
                    </div>

                    <div class="modal-meta-grid">
                        <div class="modal-meta-item">
                            <span class="meta-icon">📅</span>
                            <div>
                                <span class="meta-label">Date &amp; Time</span>
                                <span class="meta-value">{{
                                    formatDate(selectedEvent.date)
                                }}</span>
                            </div>
                        </div>
                        <div class="modal-meta-item">
                            <span class="meta-icon">📍</span>
                            <div>
                                <span class="meta-label">Location</span>
                                <span class="meta-value">{{
                                    selectedEvent.location
                                }}</span>
                            </div>
                        </div>
                        <div class="modal-meta-item">
                            <span class="meta-icon">👤</span>
                            <div>
                                <span class="meta-label">Organizer</span>
                                <span class="meta-value">{{
                                    selectedEvent.organizer
                                }}</span>
                            </div>
                        </div>
                        <div class="modal-meta-item">
                            <span class="meta-icon">👥</span>
                            <div>
                                <span class="meta-label">Capacity</span>
                                <span class="meta-value"
                                    >{{ selectedEvent.capacity }} participants
                                    max</span
                                >
                            </div>
                        </div>
                    </div>

                    <div class="modal-body">
                        <h4>About this event</h4>
                        <p>{{ selectedEvent.description }}</p>
                    </div>

                    <div class="modal-capacity">
                        <div class="capacity-info">
                            <span
                                >{{
                                    selectedEvent.capacity -
                                    selectedEvent.spots_left
                                }}
                                registered</span
                            >
                            <span>{{ selectedEvent.capacity }} total</span>
                        </div>
                        <div class="capacity-bar large">
                            <div
                                class="capacity-fill"
                                :style="{
                                    width: getCapacityPct(selectedEvent) + '%',
                                }"
                                :class="getCapacityFillClass(selectedEvent)"
                            ></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn-outline" @click="closeModal">
                            Close
                        </button>
                        <a
                            v-if="selectedEvent.spots_left > 0"
                            href="/register"
                            class="btn-primary large"
                        >
                            Register to Join →
                        </a>
                        <span v-else class="btn-disabled"
                            >Registration Closed</span
                        >
                    </div>
                </div>
            </div>
        </transition>

        <!-- FOOTER -->
        <footer class="footer">
            <div class="footer-inner">
                <span class="logo">⚡ EventHub</span>
                <p>© 2026 EventHub · Built with Laravel &amp; Vue.js</p>
                <div class="footer-links">
                    <a href="/login">Login</a>
                    <a href="/register">Register</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    events: { type: Array, default: () => [] },
});

const selectedEvent = ref(null);
const search = ref("");
const sortBy = ref("date");

const totalSpots = computed(() =>
    props.events.reduce((sum, e) => sum + e.spots_left, 0),
);

const filteredEvents = computed(() => {
    let list = props.events.filter(
        (e) =>
            e.title.toLowerCase().includes(search.value.toLowerCase()) ||
            e.location.toLowerCase().includes(search.value.toLowerCase()) ||
            e.organizer.toLowerCase().includes(search.value.toLowerCase()),
    );
    if (sortBy.value === "date")
        list = [...list].sort((a, b) => new Date(a.date) - new Date(b.date));
    if (sortBy.value === "spots")
        list = [...list].sort((a, b) => b.spots_left - a.spots_left);
    if (sortBy.value === "title")
        list = [...list].sort((a, b) => a.title.localeCompare(b.title));
    return list;
});

function openModal(event) {
    selectedEvent.value = event;
    document.body.style.overflow = "hidden";
}
function closeModal() {
    selectedEvent.value = null;
    document.body.style.overflow = "";
}

function truncate(text, length) {
    return text && text.length > length ? text.slice(0, length) + "…" : text;
}
function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString("en-US", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}
function formatShortDate(dateStr) {
    return new Date(dateStr).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}
function getBadgeClass(spotsLeft) {
    if (spotsLeft <= 0) return "badge-full";
    if (spotsLeft <= 5) return "badge-low";
    return "badge-open";
}
function getCapacityPct(event) {
    const used = event.capacity - event.spots_left;
    return Math.min(100, Math.round((used / event.capacity) * 100));
}
function getCapacityFillClass(event) {
    const pct = getCapacityPct(event);
    if (pct >= 90) return "fill-red";
    if (pct >= 60) return "fill-amber";
    return "fill-green";
}
</script>

<style scoped>
@import './css/Landing.css';
</style>
