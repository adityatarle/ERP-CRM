<!-- Footer Start -->
<footer class="site-footer"> <!-- Added a class for easier targeting -->
    <div class="container-fluid">
        <div class="bg-light rounded-top p-4">
            <div class="row">
                <div class="col-12 col-sm-6 text-center text-sm-start">
                    © <a href="#">Mauli Solutions</a>, All Right Reserved.
                </div>
                <div class="col-12 col-sm-6 text-center text-sm-end">
                    Designed By <a href="https://htmlcodex.com">Heuristic Technopark</a>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Footer End -->


<!-- Back to Top -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
</div>
</div>

<!-- Unlock Request Modal -->
<div class="modal fade" id="requestUnlockModal" tabindex="-1" aria-labelledby="requestUnlockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestUnlockModalLabel">Request Edit Unlock for Invoice #<span id="modalInvoiceNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="requestUnlockForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="invoice_id" id="modalInvoiceId">
                    <div class="mb-3">
                        <label for="unlock_reason" class="form-label">Reason for Requesting Unlock:</label>
                        <textarea class="form-control" id="unlock_reason" name="unlock_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const requestUnlockModal = new bootstrap.Modal(document.getElementById('requestUnlockModal'));
    document.querySelectorAll('.request-unlock-btn').forEach(button => {
        button.addEventListener('click', function () {
            const invoiceId = this.dataset.invoiceId;
            const invoiceNumber = this.dataset.invoiceNumber;
            document.getElementById('modalInvoiceId').value = invoiceId;
            document.getElementById('modalInvoiceNumber').textContent = invoiceNumber;
            document.getElementById('requestUnlockForm').action = `/invoices/${invoiceId}/request-unlock`; // Set form action
            requestUnlockModal.show();
        });
    });
});
</script>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script> {{-- CDN links remain unchanged --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script> {{-- CDN links remain unchanged --}}
<script src="{{ asset('assets/lib/chart/chart.min.js') }}"></script>
<script src="{{ asset('assets/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('assets/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('assets/lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/lib/tempusdominus/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
<script src="{{ asset('assets/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select a Purchase Order...",
            allowClear: true,
            width: '100%' // Ensures proper width
        });
    });
</script>



  <!-- ADD SELECT2 JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Template Javascript -->
<script src="{{ asset('assets/js/main.js') }}"></script> {{-- Assuming main.js is also under assets/js --}}
