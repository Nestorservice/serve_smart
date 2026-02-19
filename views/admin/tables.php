<?php
/**
 * SIGR Admin Tables Management - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Gestion des Tables';
$currentPage = 'tables';
$tables = $tables ?? [];

ob_start();
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Gestion des Tables</h4>
        <p class="text-muted mb-0"><?= count($tables) ?> table(s) configurée(s)</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTableModal">
        <i class="bi bi-plus-circle me-1"></i>Ajouter une table
    </button>
</div>

<!-- Grille des tables -->
<div class="row g-4">
    <?php if (empty($tables)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-bounding-box display-1 text-muted mb-3"></i>
                <h4 class="text-muted">Aucune table configurée</h4>
                <p class="text-muted mb-4">Ajoutez vos premières tables de restaurant</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTableModal">
                    <i class="bi bi-plus-circle me-1"></i>Ajouter une table
                </button>
            </div>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($tables as $table): ?>
    <?php
    $hasActiveOrder = !empty($table['active_order']);
    $statusClass = $hasActiveOrder ? 'border-warning' : 'border-success';
    $statusLabel = $hasActiveOrder ? 'Occupée' : 'Libre';
    $statusBg = $hasActiveOrder ? 'warning' : 'success';
    ?>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card h-100 <?= $statusClass ?>" style="border-width: 3px;">
            <div class="card-body text-center">
                <!-- Numéro de table -->
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                     style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <span class="text-white fs-2 fw-bold"><?= e($table['table_number']) ?></span>
                </div>
                
                <h5 class="card-title mb-2">Table <?= e($table['table_number']) ?></h5>
                
                <div class="mb-3">
                    <span class="badge bg-<?= $statusBg ?> mb-2">
                        <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i><?= $statusLabel ?>
                    </span>
                    <?php if (!empty($table['capacity'])): ?>
                    <br><small class="text-muted">
                        <i class="bi bi-people me-1"></i><?= $table['capacity'] ?> places
                    </small>
                    <?php endif; ?>
                </div>
                
                <?php if ($hasActiveOrder): ?>
                <div class="alert alert-warning py-2 mb-3">
                    <small>
                        <i class="bi bi-receipt me-1"></i>
                        Commande #<?= e($table['active_order']['order_number'] ?? $table['active_order']) ?>
                    </small>
                </div>
                <?php endif; ?>
                
                <!-- QR Code link -->
                <div class="mb-3">
                    <a href="<?= url('client/menu?table=' . $table['table_number']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-qr-code me-1"></i>Lien menu
                    </a>
                </div>
            </div>
            
            <div class="card-footer bg-transparent border-0 pt-0">
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-sm btn-outline-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editTableModal"
                            data-id="<?= $table['id'] ?>"
                            data-number="<?= e($table['table_number']) ?>"
                            data-capacity="<?= $table['capacity'] ?? 4 ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info" 
                            data-bs-toggle="modal" 
                            data-bs-target="#qrModal"
                            data-number="<?= e($table['table_number']) ?>"
                            data-url="<?= url('client/menu?table=' . $table['table_number']) ?>">
                        <i class="bi bi-qr-code"></i>
                    </button>
                    <?php if (!$hasActiveOrder): ?>
                    <form action="<?= url('admin/tables/delete/' . $table['id']) ?>" method="POST" class="d-inline"
                          onsubmit="return confirm('Supprimer cette table ?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Ajouter Table -->
<div class="modal fade" id="addTableModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= url('admin/tables/add') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Nouvelle Table</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Numéro de table</label>
                        <input type="text" name="table_number" class="form-control form-control-lg text-center" required 
                               placeholder="Ex: 1, 2, A1...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacité (places)</label>
                        <input type="number" name="capacity" class="form-control" value="4" min="1" max="20">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier Table -->
<div class="modal fade" id="editTableModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= url('admin/tables/update') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editTableId">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2 text-primary"></i>Modifier Table</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Numéro de table</label>
                        <input type="text" name="table_number" id="editTableNumber" class="form-control form-control-lg text-center" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacité (places)</label>
                        <input type="number" name="capacity" id="editTableCapacity" class="form-control" min="1" max="20">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-qr-code me-2 text-primary"></i>QR Code - Table <span id="qrTableNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrCodeContainer" class="mb-3 p-4 bg-white d-inline-block rounded shadow">
                    <!-- QR Code sera généré ici -->
                    <div id="qrcode"></div>
                </div>
                <p class="text-muted small mb-3">Scannez ce code pour accéder au menu</p>
                <div class="input-group mb-3">
                    <input type="text" id="qrUrl" class="form-control" readonly>
                    <button class="btn btn-outline-primary" onclick="copyUrl()">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
                <button class="btn btn-primary" onclick="printQR()">
                    <i class="bi bi-printer me-1"></i>Imprimer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- QRCode.js -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
// Edit modal
document.getElementById('editTableModal')?.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editTableId').value = btn.dataset.id;
    document.getElementById('editTableNumber').value = btn.dataset.number;
    document.getElementById('editTableCapacity').value = btn.dataset.capacity;
});

// QR modal
let qrcode = null;
document.getElementById('qrModal')?.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    const tableNumber = btn.dataset.number;
    const url = btn.dataset.url;
    
    document.getElementById('qrTableNumber').textContent = tableNumber;
    document.getElementById('qrUrl').value = url;
    
    // Clear previous QR
    const container = document.getElementById('qrcode');
    container.innerHTML = '';
    
    // Generate new QR
    qrcode = new QRCode(container, {
        text: url,
        width: 200,
        height: 200,
        colorDark: "#667eea",
        colorLight: "#ffffff"
    });
});

function copyUrl() {
    const input = document.getElementById('qrUrl');
    input.select();
    document.execCommand('copy');
    alert('Lien copié !');
}

function printQR() {
    const content = document.getElementById('qrCodeContainer').innerHTML;
    const tableNum = document.getElementById('qrTableNumber').textContent;
    const win = window.open('', '', 'width=400,height=500');
    win.document.write(`
        <html>
        <head><title>QR Code Table ${tableNum}</title></head>
        <body style="text-align: center; padding: 20px; font-family: Arial;">
            <h2>Table ${tableNum}</h2>
            ${content}
            <p>Scannez pour commander</p>
        </body>
        </html>
    `);
    win.document.close();
    win.print();
}
</script>

<?php
$content = ob_get_clean();

// Inclure le layout
include VIEWS_PATH . '/layouts/admin.php';
?>
