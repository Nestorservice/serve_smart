<?php
/**
 * SIGR Kitchen Login - FoodDesk Style
 */

use Core\Helpers;

$pageTitle = 'Kitchen Login';
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <title><?= e($pageTitle) ?> - SIGR Restaurant</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="shortcut icon" type="image/png" sizes="16x16" href="<?= url('public/assets/images/favicon.png') ?>">
    <link href="<?= url('public/assets/vendor/swiper/css/swiper-bundle.min.css') ?>" rel="stylesheet">
    <link href="<?= url('public/assets/css/style.css') ?>" rel="stylesheet">
</head>

<body class="body">
    <div class="container mt-0">
        <div class="row align-items-center justify-contain-center">
            <div class="col-xl-12 mt-5">
                <div class="card border-0">
                    <div class="card-body login-bx">
                        <div class="row mt-5">
                            <div class="col-xl-8 col-md-6 sign text-center">
                                <div>
                                    <img src="<?= url('public/assets/images/login-img/pic-5.jpg') ?>" class="food-img" alt="">
                                </div>	
                            </div>
                            <div class="col-xl-4 col-md-6 pe-0">
                                <div class="sign-in-your">
                                    <div class="text-center mb-3">
                                        <img src="<?= url('public/assets/images/logo-full.png') ?>" class="mb-3" alt="">
                                        <h4 class="fs-20 font-w800 text-black">
                                            <i class="bi bi-display me-2"></i>Kitchen Screen
                                        </h4>
                                        <span class="dlab-sign-up">Login</span>
                                    </div>
                                    
                                    <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                        <strong>Error!</strong> <?= e(is_array($error) ? ($error[0] ?? 'Incorrect credentials') : $error) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <form action="<?= url('kitchen/login') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <div class="mb-3">
                                            <label class="mb-1"><strong>Username</strong></label>
                                            <input type="text" name="username" class="form-control" placeholder="Enter your identifier" required autofocus>
                                        </div>
                                        <div class="mb-3">
                                            <label class="mb-1"><strong>Password</strong></label>
                                            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                                        </div>
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary btn-block shadow">Login</button>
                                        </div>
                                    </form>
                                    
                                    <div class="text-center mt-4">
                                        <a href="<?= url('/') ?>" class="text-primary">
                                            <i class="bi bi-arrow-left me-2"></i>Back to Home
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= url('public/assets/vendor/global/global.min.js') ?>"></script>
    <script src="<?= url('public/assets/vendor/swiper/js/swiper-bundle.min.js') ?>"></script>
    <script src="<?= url('public/assets/js/dlabnav-init.js') ?>"></script>
</body>
</html>
