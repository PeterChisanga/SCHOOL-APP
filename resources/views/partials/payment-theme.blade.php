{{-- resources/views/partials/payment-theme.blade.php --}}
{{-- Included with @include('partials.payment-theme') at the top of each page's @section('content') --}}
<style>
  .content-wrapper { background: #f0f4f8 !important; }

  .pp-wrap { padding: 30px 20px 60px; max-width: 560px; margin: 0 auto; }
  .pp-wrap.pp-wide { max-width: 880px; }

  .pp-title { font-size: 1.3rem; font-weight: 700; color: #0f5132; margin: 0 0 4px; }
  .pp-sub   { font-size: .85rem; color: #6b7280; margin: 0 0 24px; }

  .pp-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border: 1px solid #e5e7eb;
    overflow: hidden;
    margin-bottom: 18px;
  }
  .pp-card-body { padding: 28px 28px 26px; }

  /* Badges / info pills */
  .pp-badge {
    display: flex; align-items: center; gap: 12px;
    background: #f0faf4; border: 1px solid #a8e0bc;
    border-radius: 10px; padding: 14px 16px;
    margin-bottom: 22px;
  }
  .pp-badge-icon {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, #0f5132, #1e8449);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .9rem; flex-shrink: 0;
  }
  .pp-badge-label { font-size: .75rem; color: #6b7280; margin: 0 0 2px; }
  .pp-badge-value { font-size: .95rem; font-weight: 700; color: #0f5132; margin: 0; }

  /* Alerts */
  .pp-alert {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 13px 16px; border-radius: 10px;
    font-size: .86rem; margin-bottom: 20px;
  }
  .pp-alert i { margin-top: 1px; flex-shrink: 0; }
  .pp-alert-danger  { background: #fff5f5; border: 1px solid #fcc; color: #c0392b; }
  .pp-alert-success { background: #f0faf4; border: 1px solid #a8e0bc; color: #145a32; }

  /* Form basics */
  .pp-label { display: block; font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 8px; }
  .pp-field { margin-bottom: 18px; }
  .pp-input, .pp-select, .pp-textarea {
    width: 100%; padding: 12px 14px;
    border: 1.5px solid #d1d5db; border-radius: 10px;
    font-size: .92rem; color: #1f2937;
    font-family: inherit; outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .pp-input:focus, .pp-select:focus, .pp-textarea:focus {
    border-color: #0f5132; box-shadow: 0 0 0 4px rgba(15,81,50,.10);
  }
  .pp-textarea { resize: vertical; min-height: 90px; }
  .pp-error { font-size: .78rem; color: #dc2626; margin-top: 6px; }
  .pp-hint { font-size: .78rem; color: #9ca3af; margin: -8px 0 18px; }

  /* Buttons */
  .pp-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 13px; border: none; border-radius: 10px;
    font-size: .92rem; font-weight: 600; cursor: pointer;
    font-family: inherit; transition: background .2s, transform .15s;
    text-decoration: none;
  }
  .pp-btn-primary {
    background: #0f5132; color: #fff;
    box-shadow: 0 3px 10px rgba(15,81,50,.25);
  }
  .pp-btn-primary:hover { background: #1a7a4a; transform: translateY(-1px); color: #fff; }
  .pp-btn-secondary {
    background: #f0faf4; color: #0f5132; border: 1.5px solid #a8e0bc;
  }
  .pp-btn-secondary:hover { background: #e3f5e9; }
  .pp-btn-ghost {
    background: #f9fafb; color: #374151; border: 1.5px solid #e5e7eb;
  }
  .pp-btn-ghost:hover { background: #f0faf4; border-color: #a8e0bc; color: #0f5132; }
  .pp-btn-danger { background: #fff; color: #c0392b; border: 1.5px solid #f3b8b0; }
  .pp-btn-danger:hover { background: #fff5f5; }
  .pp-btn-row { display: flex; gap: 10px; flex-wrap: wrap; }
  .pp-btn-row .pp-btn { flex: 1; min-width: 150px; }

  .pp-divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; color: #d1d5db; font-size: .8rem; }
  .pp-divider::before, .pp-divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }

  .pp-back {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    margin-top: 18px; font-size: .82rem; color: #6b7280; text-decoration: none;
  }
  .pp-back:hover { color: #0f5132; }

  /* Tabs (bank / mobile money) */
  .pp-tabs { display: flex; gap: 6px; margin-bottom: 18px; }
  .pp-tab-btn {
    flex: 1; border: 1.5px solid #e5e7eb; background: #f9fafb; color: #6b7280;
    font-weight: 600; font-size: .82rem; padding: 10px; border-radius: 9px; cursor: pointer;
  }
  .pp-tab-btn.active { background: #0f5132; border-color: #0f5132; color: #fff; }
  .pp-tab-panel { display: none; }
  .pp-tab-panel.active { display: block; }

  .pp-detail-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 0; border-bottom: 1px solid #f0f0f0; gap: 12px;
  }
  .pp-detail-row:last-child { border-bottom: none; }
  .pp-detail-row .k { font-size: .8rem; color: #6b7280; }
  .pp-detail-row .v { font-weight: 700; font-size: .88rem; color: #1f2937; display: flex; align-items: center; gap: 8px; }
  .pp-copy-btn {
    border: 1px solid #d1d5db; background: #fff; border-radius: 6px;
    font-size: .7rem; padding: 3px 8px; cursor: pointer; color: #6b7280;
  }
  .pp-copy-btn:hover { border-color: #0f5132; color: #0f5132; }

  .pp-instructions {
    font-size: .8rem; color: #6b7280; line-height: 1.6;
    background: #f9fafb; border-radius: 10px; padding: 14px 16px; margin-top: 14px;
  }

  .pp-dropzone {
    border: 2px dashed #d1d5db; border-radius: 10px; padding: 20px;
    text-align: center; cursor: pointer; font-size: .82rem; color: #6b7280;
    background: #f9fafb; display: block;
  }
  .pp-dropzone.has-file { border-color: #1e8449; color: #0f5132; background: #f0faf4; }
  .pp-dropzone input { display: none; }

  /* Balance / amount rows (payments list) */
  .pp-item-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 14px; }
  .pp-item-desc { font-weight: 700; font-size: .95rem; color: #1f2937; }
  .pp-item-term { font-size: .78rem; color: #9ca3af; }
  .pp-amount { font-weight: 700; font-size: 1.3rem; color: #0f5132; text-align: right; }
  .pp-amount-label { font-size: .72rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .04em; text-align: right; }
  .pp-paid-pill {
    display: inline-flex; align-items: center; gap: 6px; font-size: .78rem; color: #145a32;
    background: #f0faf4; border-radius: 20px; padding: 4px 12px;
  }

  /* Stamp (confirmation page) */
  .pp-stamp {
    display: inline-block; border: 3px solid #0f5132; color: #0f5132;
    font-weight: 700; font-size: .85rem; letter-spacing: .06em;
    padding: 10px 18px; border-radius: 8px; transform: rotate(-5deg);
    margin-bottom: 20px;
  }
  .pp-ref-box {
    background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 10px;
    padding: 14px; margin-bottom: 22px; text-align: center;
  }
  .pp-ref-box .k { font-size: .7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
  .pp-ref-box .v { font-weight: 700; font-size: 1rem; color: #0f5132; font-family: monospace; }

  /* Empty state */
  .pp-empty { text-align: center; padding: 50px 20px; color: #9ca3af; border: 1.5px dashed #e5e7eb; border-radius: 14px; }
  .pp-empty .big { font-weight: 700; font-size: 1rem; color: #374151; margin-bottom: 6px; }

  /* Admin table/list */
  .pp-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px; }
  .pp-count-pill {
    font-size: .78rem; font-weight: 600; color: #0f5132; background: #f0faf4;
    border: 1px solid #a8e0bc; padding: 5px 12px; border-radius: 20px;
  }
  .pp-case { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
  .pp-case-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
  .pp-who { display: flex; flex-direction: column; gap: 2px; }
  .pp-pupil-name { font-weight: 700; font-size: .98rem; color: #1f2937; }
  .pp-meta { font-size: .76rem; color: #9ca3af; }
  .pp-school-tag {
    display: inline-block; background: #f0faf4; border: 1px solid #a8e0bc; color: #0f5132;
    border-radius: 5px; padding: 1px 8px; margin-right: 6px; font-size: .72rem; font-weight: 600;
  }
  .pp-details-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px 20px;
    margin: 14px 0; padding: 14px 16px; background: #f9fafb; border-radius: 8px;
  }
  .pp-details-grid .item .k { font-size: .7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
  .pp-details-grid .item .v { font-size: .82rem; font-weight: 600; color: #1f2937; }
  .pp-details-grid .item .v a { color: #0f5132; text-decoration: none; }
  .pp-details-grid .item .v a:hover { text-decoration: underline; }

  /* Modal */
  .pp-modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(15,81,50,.35);
    align-items: center; justify-content: center; z-index: 30; padding: 20px;
  }
  .pp-modal-overlay.open { display: flex; }
  .pp-modal { background: #fff; border-radius: 14px; padding: 24px; max-width: 380px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,.15); }
  .pp-modal h3 { font-size: 1rem; font-weight: 700; color: #0f5132; margin: 0 0 16px; }
  .pp-modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 6px; }

  /* Fieldsets (admin payment details form) */
  .pp-fieldset { border: 1.5px solid #e5e7eb; border-radius: 12px; padding: 20px 20px 6px; margin-bottom: 18px; }
  .pp-legend { font-weight: 700; font-size: .85rem; color: #0f5132; padding: 0 6px; }
  .pp-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  @media (max-width: 520px) { .pp-row2 { grid-template-columns: 1fr; } }
</style>