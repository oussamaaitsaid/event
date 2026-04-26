<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; margin: 0; padding: 2rem; }
    .card { max-width: 580px; margin: 0 auto; background: white; border-radius: 1rem; overflow: hidden; }
    .header { background: linear-gradient(135deg, #0f172a, #4f46e5); padding: 2rem; color: white; text-align: center; }
    .header h1 { margin: 0; font-size: 1.4rem; }
    .header p  { margin: 0.5rem 0 0; opacity: 0.8; font-size: 0.9rem; }
    .body { padding: 2rem; }
    .label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.3rem; }
    .value { font-size: 0.95rem; color: #0f172a; background: #f8fafc; padding: 0.75rem 1rem; border-radius: 0.5rem; border-left: 3px solid #4f46e5; margin-bottom: 1.25rem; }
    .footer { text-align: center; padding: 1.5rem; background: #f8fafc; color: #94a3b8; font-size: 0.82rem; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">
      <h1>⚡ EventHub</h1>
      <p>New Organizer Request</p>
    </div>
    <div class="body">
      <p style="color:#475569;margin-bottom:1.5rem;">A user wants to become an organizer on EventHub.</p>
      <div class="label">Name</div>
      <div class="value">{{ $userName }}</div>
      <div class="label">Email</div>
      <div class="value">{{ $userEmail }}</div>
      <div class="label">Message</div>
      <!-- ✅ After -->
<div class="value">{{ $userMessage }}</div>
      <p style="color:#64748b;font-size:0.875rem;margin-top:1rem;">
        You can promote this user from the admin dashboard.
      </p>
    </div>
    <div class="footer">© 2026 EventHub</div>
  </div>
</body>
</html>
