<?php
/**
 * SIGR Admin Users Management - FoodDesk Style
 */

use Core\Helpers;

// Variables
$pageTitle = 'Gestion des Utilisateurs';
$currentPage = 'users';
$users = $users ?? [];

$roleLabels = [
    'admin' => ['label' => 'Administrateur', 'class' => 'danger'],
    'manager' => ['label' => 'Manager', 'class' => 'primary'],
    'staff' => ['label' => 'Personnel', 'class' => 'info'],
    'kitchen' => ['label' => 'Cuisine', 'class' => 'warning'],
];

ob_start();
?>

<div class="card">
    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
            <i class="bi bi-people me-2 text-primary"></i>Utilisateurs
        </h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-plus-circle me-1"></i>Ajouter un utilisateur
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Dernière connexion</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-people display-4 d-block mb-3 text-muted"></i>
                            <p class="text-muted">Aucun utilisateur trouvé</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <?php $role = $roleLabels[$user['role'] ?? 'staff'] ?? $roleLabels['staff']; ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-title rounded-circle bg-primary">
                                        <?= strtoupper(substr($user['full_name'] ?? $user['username'] ?? 'U', 0, 1)) ?>
                                    </span>
                                </div>
                                <div>
                                    <strong><?= e($user['full_name'] ?? $user['username']) ?></strong>
                                    <br><small class="text-muted">@<?= e($user['username']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= e($user['email'] ?? '-') ?></td>
                        <td><span class="badge bg-<?= $role['class'] ?>"><?= $role['label'] ?></span></td>
                        <td>
                            <?php if ($user['is_active'] ?? true): ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Actif</span>
                            <?php else: ?>
                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $user['last_login'] ? Helpers::formatDate($user['last_login'], 'd/m/Y H:i') : 'Jamais' ?>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $user['id'] ?>">
                                            <i class="bi bi-pencil me-2 text-primary"></i>Modifier
                                        </a>
                                    </li>
                                    <li>
                                        <form action="<?= url('admin/users/' . $user['id'] . '/toggle') ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-<?= ($user['is_active'] ?? true) ? 'pause' : 'play' ?>-circle me-2"></i>
                                                <?= ($user['is_active'] ?? true) ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="<?= url('admin/users/' . $user['id'] . '/delete') ?>" method="POST"
                                              onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajouter Utilisateur -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('admin/users/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2 text-primary"></i>Nouvel utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select name="role" class="form-select">
                            <?php foreach ($roleLabels as $key => $role): ?>
                            <option value="<?= $key ?>"><?= $role['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar {
    width: 40px;
    height: 40px;
    display: inline-flex;
}
.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
}
</style>

<?php
$content = ob_get_clean();

// Inclure le layout
include VIEWS_PATH . '/layouts/admin.php';
?>
