<?php
/**
 * SIGR Client Payment - Premium Dribbble POS Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Payment';
$currentPage = 'payment';
$order = $order ?? null;
$tableNumber = $tableNumber ?? null;

ob_start();
?>

<div class="row m-0 p-0 justify-content-center">
    <div class="col-12 col-md-8 col-lg-5 mt-4">
        
        <div class="card bg-transparent border-0">
            <div class="card-body p-0">
                <div class="text-center mb-4">
                    <h5 class="text-muted text-uppercase mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Order Summary</h5>
                    <h2 class="text-white">#<?= e($order['order_number'] ?? $order['id']) ?></h2>
                    <div class="fs-1 fw-bold text-orange mt-2"><?= Helpers::formatPrice($order['total_amount'] ?? 0) ?></div>
                </div>

                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 25px;">
                    <h5 class="mb-4 text-white">Payment Method</h5>

                    <form id="paymentForm" action="<?= url('client/payment/process') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        
                        <!-- Option 1: Cash -->
                        <div class="payment-option mb-3">
                            <input type="radio" name="method" id="method_cash" value="cash" checked class="btn-check">
                            <label class="payment-label" for="method_cash">
                                <div class="payment-icon bg-orange text-white me-3">
                                    <i class="bi bi-cash-stack fs-4"></i>
                                </div>
                                <div class="payment-info">
                                    <h6 class="mb-0 fw-bold text-white">Pay with Cash</h6>
                                    <p class="mb-0 small text-muted">To the server or at the counter</p>
                                </div>
                                <div class="check-icon ms-auto"><i class="bi bi-circle"></i></div>
                            </label>
                        </div>

                        <!-- Option 2: Mobile Money SIMULATION -->
                        <div class="payment-option mb-4">
                            <input type="radio" name="method" id="method_momo" value="momo" class="btn-check">
                            <label class="payment-label" for="method_momo">
                                <div class="payment-icon bg-warning text-dark me-3">
                                    <i class="bi bi-phone fs-4"></i>
                                </div>
                                <div class="payment-info">
                                    <h6 class="mb-0 fw-bold text-white">Mobile Money</h6>
                                    <p class="mb-0 small text-muted">Orange / MTN / Moov</p>
                                </div>
                                <div class="check-icon ms-auto"><i class="bi bi-circle"></i></div>
                            </label>
                        </div>

                        <!-- Phone field -->
                        <div id="phoneField" class="mb-4 d-none" style="animation: dropDown 0.3s ease;">
                            <label class="form-label small text-muted">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--border-color); color: var(--text-muted);">+237</span>
                                <input type="tel" name="phone" id="phoneInput" class="form-control bg-transparent border-start-0" placeholder="6XX XXX XXX" style="border-color: var(--border-color); color: var(--text-main);">
                            </div>
                        </div>

                        <button type="submit" id="payBtn" class="btn w-100 py-3 mt-2 fw-bold" style="background: var(--primary-orange); color: var(--bg-color); font-size: 1.1rem; transition: 0.3s; border-radius: var(--radius-md);">
                            Confirm Payment <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                        
                        <div class="text-center mt-4">
                            <a href="<?= url('client/order-tracking') ?>" class="text-muted small text-decoration-none hover-white">
                                <i class="bi bi-clock-history me-1"></i> Pay later (Track Order)
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes dropDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.hover-white:hover { color: white !important; }

.payment-label {
    display: flex;
    align-items: center;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 15px;
    background: var(--bg-color);
}
.payment-label:hover { border-color: rgba(255,159,28,0.5); }

.payment-option input:checked + .payment-label {
    border-color: var(--primary-orange);
    background: rgba(255, 159, 28, 0.05);
}
.payment-option input:checked + .payment-label .check-icon {
    color: var(--primary-orange);
}
.payment-option input:checked + .payment-label .check-icon i::before {
    content: "\F26B"; /* bi-check-circle-fill */
}
.payment-icon {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}
.check-icon { color: var(--text-muted); font-size: 1.2rem; }

.input-group-text, .form-control:focus {
    box-shadow: none;
    border-color: var(--primary-orange) !important;
}
</style>

<script>
document.querySelectorAll('input[name="method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const phoneField = document.getElementById('phoneField');
        const phoneInput = document.getElementById('phoneInput');
        if (this.value === 'momo') {
            phoneField.classList.remove('d-none');
            phoneInput.required = true;
        } else {
            phoneField.classList.add('d-none');
            phoneInput.required = false;
        }
    });
});

document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('payBtn');
    const originalText = btn.innerHTML;
    
    // Simulate Mobile Money processing look
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing transaction...';
    btn.disabled = true;
    btn.style.opacity = '0.8';

    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="bi bi-check-lg me-2"></i> ' + (data.message || 'Payment confirmed!');
            btn.style.background = 'var(--success)';
            btn.style.color = 'white';
            
            // Redirect to ticket after 1.5s
            setTimeout(() => {
                window.location.href = data.redirect || '<?= url('client/order-tracking') ?>';
            }, 1500);
        } else {
            alert(data.message || 'Error during processing');
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    })
    .catch(err => {
        alert('Server connection error.');
        btn.innerHTML = originalText;
        btn.disabled = false;
        btn.style.opacity = '1';
    });
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/client.php';
?>
