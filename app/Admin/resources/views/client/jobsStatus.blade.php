{{-- pushing to vendor/laravelrus/sleepingowl/resources/views/default/_layout/inner.blade.php --}}
@push('content.top')
    <div class="recalculation-progress">
        <p>Next Recalculation jobs check update in: <b id="js-check-refresh">0</b></p>
    </div>
    <div class="alert alert-warning" id="calculation-status" role="alert" style="display: none"></div>
@endpush

@push('footer-scripts')
    <script src="{{ mix('js/admin/client/jobsStatus.js') }}"></script>
@endpush
