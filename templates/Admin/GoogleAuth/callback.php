<div class="container-fluid">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fab fa-google mr-2"></i> Google Calendar Authentication</h5>
                </div>
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-primary mb-4" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <h4 class="mb-3">Processing Authentication...</h4>
                    <p class="mb-0 text-muted">Please wait while we complete your Google Calendar connection.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Automatically redirect to index page after a short delay
    setTimeout(function() {
        window.location.href = "/admin/google-auth";
    }, 2000);
</script>
