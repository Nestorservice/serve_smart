<?php
/**
 * SIGR Client Receipt/Ticket
 */

use Core\Helpers;

$pageTitle = 'Receipt - Order #' . e($order['order_number'] ?? '');

$taxRate = $order['tax_rate'] ?? 0;
ob_start();
?>
<div class="row m-0 p-0 justify-content-center align-items-center" style="min-height: calc(100vh - 100px);">
    <div class="col-12 col-md-8 col-lg-5">
        
        <div class="ticket-slip mx-auto shadow-lg">
            
            <div class="text-center mb-4">
                <i class="bi bi-fire fs-1 d-block mb-2" style="color: #444;"></i>
                <h3 class="mb-0 text-uppercase fw-bold" style="letter-spacing: 2px;">African Flavors</h3>
                <p class="text-muted small mb-0">Restaurant . Bar . Lounge</p>
                <p class="text-muted small mb-0">Douala, Cameroon</p>
            </div>
            
            <div class="ticket-divider"></div>
            
            <div class="d-flex justify-content-between my-3 small">
                <div>
                    <div><strong>Receipt No:</strong> <?= e($order['order_number'] ?? '') ?></div>
                    <div><strong>Date:</strong> <?= Helpers::formatDate($order['created_at'] ?? 'now', 'm/d/Y H:i') ?></div>
                </div>
                <div class="text-end">
                    <div><strong>Table:</strong> <?= e($order['table_number'] ?? 'General') ?></div>
                    <?php if (!empty($order['payment_status']) && $order['payment_status'] === 'paid'): ?>
                    <span class="badge bg-success text-white mt-1">PAID (<?= strtoupper(e($order['payment_method'] ?? 'cash')) ?>)</span>
                    <?php else: ?>
                    <span class="badge bg-warning text-dark mt-1">UNPAID</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="ticket-divider"></div>
            
            <table class="w-100 my-4 ticket-table">
                <thead>
                    <tr>
                        <th class="text-start">QTY/ITEM</th>
                        <th class="text-end">PRICE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] ?? [] as $item): ?>
                    <tr>
                        <td class="text-start py-2">
                            <?= $item['quantity'] ?>x <?= e(mb_substr((!empty($item['name_en']) ? $item['name_en'] : ($item['name_fr'] ?? 'Item')), 0, 20)) ?>
                        </td>
                        <td class="text-end py-2">
                            <?= Helpers::formatPrice($item['total_price'] ?? 0) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="ticket-divider"></div>
            
            <div class="my-3">
                <div class="d-flex justify-content-between text-muted small mb-1">
                    <span>Subtotal:</span>
                    <span><?= Helpers::formatPrice($order['subtotal'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between text-muted small mb-2">
                    <span>VAT (<?= $taxRate ?>%):</span>
                    <span><?= Helpers::formatPrice($order['tax_amount'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between fw-bold fs-5 mt-2 pt-2 border-top border-dark border-1 border-dashed">
                    <span>TOTAL:</span>
                    <span><?= Helpers::formatPrice($order['total_amount'] ?? 0) ?></span>
                </div>
            </div>
            
            <div class="ticket-divider"></div>
            
            <div class="text-center mt-4 mb-2">
                <p class="fw-bold mb-1">Thank you for your visit!</p>
                <div class="barcode mt-3 mx-auto"></div>
                <small class="text-muted d-block mt-2"><?= e($order['order_number'] ?? '') ?></small>
            </div>
            
        </div>
        
        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4 d-print-none">
            <button onclick="window.print()" class="btn btn-light px-4 py-2 border">
                <i class="bi bi-printer me-2"></i> Print
            </button>
            <button onclick="downloadPDF()" class="btn px-4 py-2 text-white" style="background: var(--primary-orange, #ff6b35);">
                <i class="bi bi-file-pdf me-2"></i> Download PDF
            </button>
            <a href="<?= url('client/order-tracking') ?>" class="btn btn-outline-light px-4 py-2">
                <i class="bi bi-arrow-left me-2"></i> Back to Tracking
            </a>
        </div>
        
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF() {
    const element = document.querySelector('.ticket-slip');
    const opt = {
        margin:       [10, 5, 10, 5],
        filename:     'Receipt_Order_<?= e($order['order_number'] ?? '') ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, 
            useCORS: true,
            letterRendering: true,
            backgroundColor: '#ffffff'
        },
        jsPDF:        { unit: 'mm', format: 'a5', orientation: 'portrait' }
    };
    
    // Temporarily remove jagged edges for better PDF capture
    element.classList.add('pdf-rendering');
    
    html2pdf().from(element).set(opt).save().then(() => {
        element.classList.remove('pdf-rendering');
    }).catch(err => {
        console.error('PDF Generation Error:', err);
        element.classList.remove('pdf-rendering');
        alert('Could not generate PDF. Please use the Print button instead.');
    });
}
</script>

<style>
/* PDF rendering enhancements */
.ticket-slip.pdf-rendering::before,
.ticket-slip.pdf-rendering::after {
    display: none !important;
}
.ticket-slip.pdf-rendering {
    box-shadow: none !important;
    border: 1px solid #eee !important;
}


<style>
/* Thermique Ticket Receipt Style */
.ticket-slip {
    background: #fff;
    color: #111;
    padding: 30px 20px;
    font-family: 'Courier New', Courier, monospace;
    width: 100%;
    max-width: 380px;
    position: relative;
}

/* jagged top/bottom edges */
.ticket-slip::before, .ticket-slip::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    height: 10px;
    background-size: 20px 20px;
}
.ticket-slip::before {
    top: -10px;
    background-image: radial-gradient(circle at 10px 0, transparent 10px, #fff 11px);
}
.ticket-slip::after {
    bottom: -10px;
    background-image: radial-gradient(circle at 10px 20px, transparent 10px, #fff 11px);
}

.ticket-divider {
    border-top: 2px dashed #999;
    width: 100%;
    margin: 15px 0;
}

.ticket-table th {
    border-bottom: 2px dashed #999;
    padding-bottom: 10px;
    font-size: 0.9em;
}
.ticket-table td { font-size: 0.95em; }

.barcode {
    height: 40px;
    width: 80%;
    background: repeating-linear-gradient(
        90deg,
        #111,
        #111 2px,
        transparent 2px,
        transparent 4px,
        #111 4px,
        #111 5px,
        transparent 5px,
        transparent 8px
    );
}

@media print {
    body * { visibility: hidden; }
    .ticket-slip, .ticket-slip * {
        visibility: visible;
    }
    .ticket-slip {
        position: absolute;
        left: 0;
        top: 0;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0;
        width: 100%;
    }
    .ticket-slip::before, .ticket-slip::after { display: none; }
}
</style>

<?php
$content = ob_get_clean();
// We use client layout because it manages the scripts, but we overwrite bg
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title><?= e($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style> body { background: #1a1c23; padding: 20px; } </style>
</head>
<body>
    <?= $content ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
