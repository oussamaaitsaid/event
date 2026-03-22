<template>
  <div class="dash-page">
    <nav class="navbar">
      <div class="nav-inner">
        <a href="/" class="logo">⚡ EventHub</a>
        <div class="nav-right">
          <span class="user-badge">{{ user.role }}</span>
          <span class="user-name">{{ user.name }}</span>
          <button @click="logout" class="btn-logout">Logout</button>
        </div>
      </div>
    </nav>
    <div class="dash-content">
      <h1>Welcome back, {{ user.name }}! 👋</h1>
      <p>You are logged in as <strong>{{ user.role }}</strong>.</p>
      <a href="/" class="btn-primary">← Back to Events</a>
    </div>
  </div>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3';

const { auth } = usePage().props;
const user = auth.user;

function logout() {
  router.post('/logout');
}
</script>

<style scoped>
* { box-sizing: border-box; margin: 0; padding: 0; }
.dash-page { font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; background: #f1f5f9; }
.navbar {
  background: white; border-bottom: 1px solid #e2e8f0;
  padding: 1rem 2rem;
}
.nav-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between;
}
.logo { font-size: 1.3rem; font-weight: 800; color: #4f46e5; text-decoration: none; }
.nav-right { display: flex; align-items: center; gap: 1rem; }
.user-badge {
  background: #eef2ff; color: #4f46e5;
  padding: 0.25rem 0.75rem; border-radius: 999px;
  font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
}
.user-name { font-size: 0.9rem; font-weight: 600; color: #0f172a; }
.btn-logout {
  background: none; border: 1.5px solid #e2e8f0;
  color: #64748b; padding: 0.4rem 1rem;
  border-radius: 0.5rem; font-size: 0.875rem;
  font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-logout:hover { border-color: #ef4444; color: #ef4444; }
.dash-content {
  max-width: 800px; margin: 4rem auto;
  padding: 2rem; background: white;
  border-radius: 1rem; border: 1px solid #e2e8f0;
  display: flex; flex-direction: column; gap: 1rem;
}
.dash-content h1 { font-size: 1.75rem; font-weight: 800; color: #0f172a; }
.dash-content p { color: #64748b; font-size: 1rem; }
.btn-primary {
  display: inline-block; background: #4f46e5; color: white;
  padding: 0.65rem 1.5rem; border-radius: 0.5rem;
  font-weight: 600; text-decoration: none; font-size: 0.9rem;
  transition: background 0.2s; align-self: flex-start;
}
.btn-primary:hover { background: #4338ca; }
</style>
