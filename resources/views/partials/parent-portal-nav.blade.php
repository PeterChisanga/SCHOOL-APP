{{-- resources/views/partials/parent-portal-nav.blade.php --}}
{{-- Include after partials.payment-theme, inside a pp-wrap. Requires $pupil in scope. --}}
<div class="pp-portal-nav">
  <a href="{{ route('parent.payments', $pupil->id) }}"
     class="pp-portal-link {{ request()->routeIs('parent.payments') ? 'active' : '' }}">
    <i class="fas fa-wallet"></i> Fees &amp; Payments
  </a>
  <a href="{{ route('parent.results', $pupil->id) }}"
     class="pp-portal-link {{ request()->routeIs('parent.results') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i> Results
  </a>
</div>
