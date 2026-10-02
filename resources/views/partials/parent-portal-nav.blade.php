{{-- resources/views/partials/parent-portal-nav.blade.php --}}
{{-- Include after partials.payment-theme, inside a pp-wrap. Requires $pupil in scope; $children is optional. --}}
@if(isset($children) && $children->count() > 1)
  @php $onResults = request()->routeIs('parent.results'); @endphp
  <div class="pp-children">
    <span class="pp-children-label"><i class="fas fa-users"></i> Your children</span>
    @foreach($children as $child)
      <a href="{{ $onResults ? route('parent.results', $child->id) : route('parent.payments', $child->id) }}"
         class="pp-child-chip {{ $child->id == $pupil->id ? 'active' : '' }}">
        {{ $child->first_name }} {{ $child->last_name }}
      </a>
    @endforeach
  </div>
@endif
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
