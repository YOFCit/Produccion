@if (session()->has('message'))
<div
  x-data="{ show: true }"
  x-init="
    setTimeout(() => {
      show = false;
    }, 2000);
  "
  x-show="show"
  x-transition
  class="position-fixed bottom-0 end-0 m-4 z-50"
  style="min-width: 250px;"
  wire:ignore>
  <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow rounded">
    <svg xmlns="http://www.w3.org/2000/svg" class="bi bi-check-circle-fill flex-shrink-0" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
      <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM6.97 11.03a.75.75 0 0 0 1.07 0l3.992-3.992a.75.75 0 1 0-1.06-1.06L7.5 9.439 6.03 7.97a.75.75 0 1 0-1.06 1.06l1.998 2z" />
    </svg>
    <span class="fw-semibold">{{ session('message') }}</span>
  </div>
</div>
@endif