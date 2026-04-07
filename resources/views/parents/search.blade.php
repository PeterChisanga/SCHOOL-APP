@extends('layouts.app')

@section('content')
<style>
  /* ── reset for this page ── */
  .content-wrapper { background: #f0f4f8 !important; }

  .ps-wrap {
    min-height: 85vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
  }

  .ps-card {
    width: 100%;
    max-width: 480px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,.12);
    overflow: hidden;
  }

  /* card header */
  .ps-card-head {
    background: linear-gradient(135deg, #0f5132 0%, #1e8449 100%);
    padding: 36px 40px 30px;
    color: #fff;
    position: relative;
    overflow: hidden;
  }
  .ps-card-head::after {
    content: '';
    position: absolute;
    right: -50px; top: -50px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
  }
  .ps-card-head .ps-logo {
    width: 56px; height: 56px;
    background: rgba(255,255,255,.15);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 18px;
    position: relative; z-index: 1;
  }
  .ps-card-head h2 {
    font-size: 1.45rem;
    font-weight: 700;
    margin: 0 0 6px;
    position: relative; z-index: 1;
  }
  .ps-card-head p {
    font-size: .86rem;
    opacity: .8;
    margin: 0;
    position: relative; z-index: 1;
  }

  /* card body */
  .ps-card-body { padding: 36px 40px; }

  /* alerts */
  .ps-alert {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 13px 16px; border-radius: 10px;
    font-size: .86rem; margin-bottom: 24px;
    line-height: 1.5;
  }
  .ps-alert i { margin-top: 1px; flex-shrink: 0; }
  .ps-alert-danger  { background: #fff5f5; border: 1px solid #fcc; color: #c0392b; }
  .ps-alert-success { background: #f0faf4; border: 1px solid #a8e0bc; color: #145a32; }

  /* form */
  .ps-field { margin-bottom: 24px; }
  .ps-field label {
    display: block;
    font-size: .82rem; font-weight: 600;
    color: #374151; margin-bottom: 8px;
    letter-spacing: .02em;
  }
  .ps-input-group {
    display: flex;
    border: 1.5px solid #d1d5db;
    border-radius: 10px;
    overflow: hidden;
    transition: border-color .2s, box-shadow .2s;
    background: #fff;
  }
  .ps-input-group:focus-within {
    border-color: #0f5132;
    box-shadow: 0 0 0 3px rgba(15,81,50,.12);
  }
  .ps-input-group .ps-prefix {
    background: #f9fafb;
    border-right: 1.5px solid #d1d5db;
    padding: 0 14px;
    display: flex; align-items: center;
    color: #6b7280; font-size: .9rem;
    flex-shrink: 0;
  }
  .ps-input-group input {
    flex: 1;
    border: none; outline: none;
    padding: 13px 14px;
    font-size: .93rem;
    color: #111827;
    background: transparent;
    font-family: inherit;
  }
  .ps-input-group input::placeholder { color: #9ca3af; }
  .ps-hint { font-size: .76rem; color: #9ca3af; margin-top: 7px; }

  /* submit */
  .ps-submit {
    width: 100%;
    padding: 14px;
    background: #0f5132;
    color: #fff;
    border: none; border-radius: 10px;
    font-size: .94rem; font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background .2s, transform .15s, box-shadow .2s;
    font-family: inherit;
    box-shadow: 0 4px 14px rgba(15,81,50,.3);
  }
  .ps-submit:hover  { background: #1a7a4a; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(15,81,50,.35); }
  .ps-submit:active { transform: translateY(0); }

  /* back */
  .ps-back {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    margin-top: 20px;
    font-size: .83rem; color: #9ca3af;
    text-decoration: none;
    transition: color .2s;
  }
  .ps-back:hover { color: #0f5132; }

  /* features */
  .ps-features {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 10px; margin-top: 28px;
  }
  .ps-feature {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 12px;
    text-align: center;
  }
  .ps-feature i { color: #0f5132; font-size: 1.15rem; margin-bottom: 7px; display: block; }
  .ps-feature span { font-size: .76rem; color: #6b7280; line-height: 1.45; display: block; }
</style>

<div class="ps-wrap">
  <div class="ps-card">

    <div class="ps-card-head">
      <div class="ps-logo"><i class="fas fa-users"></i></div>
      <h2>Parent Portal</h2>
      <p>View Children Results and pay your child's school fees securely</p>
    </div>

    <div class="ps-card-body">

      @if(session('error'))
        <div class="ps-alert ps-alert-danger">
          <i class="fas fa-exclamation-circle"></i>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      @if(session('success'))
        <div class="ps-alert ps-alert-success">
          <i class="fas fa-check-circle"></i>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if($errors->any())
        <div class="ps-alert ps-alert-danger">
          <i class="fas fa-exclamation-circle"></i>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      <form method="POST" action="{{ route('parent.search') }}">
        @csrf

        <div class="ps-field">
          <label for="phone">Registered Phone Number</label>
          <div class="ps-input-group">
            <span class="ps-prefix"><i class="fas fa-phone"></i></span>
            <input
              type="tel"
              id="phone"
              name="phone"
              value="{{ old('phone') }}"
              placeholder="e.g. 0971234567"
              autocomplete="tel"
              required
            />
          </div>
          <p class="ps-hint">Enter the phone number registered with the school</p>
        </div>

        <button type="submit" class="ps-submit">
          <i class="fas fa-search"></i> Find My Account
        </button>
      </form>

      <a href="/" class="ps-back">
        <i class="fas fa-arrow-left"></i> Back to Home
      </a>

      {{-- <div class="ps-features">
        <div class="ps-feature">
          <i class="fas fa-shield-alt"></i>
          <span>Secure &amp; private access</span>
        </div>
        <div class="ps-feature">
          <i class="fas fa-mobile-alt"></i>
          <span>Pay via Airtel or MTN Money</span>
        </div>
        <div class="ps-feature">
          <i class="fas fa-clock"></i>
          <span>Instant payment confirmation</span>
        </div>
        <div class="ps-feature">
          <i class="fas fa-receipt"></i>
          <span>Full payment history</span>
        </div>
      </div> --}}

    </div>
  </div>
</div>
@endsection
