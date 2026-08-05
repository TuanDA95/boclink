<header>
    <nav class="navbar navbar-expand-md navbar-light shadow-sm align-middle" style="background-image: url('https://mir-s3-cdn-cf.behance.net/project_modules/1400/e5210952533973.5913d568d0053.jpg')">
        <div class="container px-3">
            <a class="navbar-brand" href="<?= site_url() ?>"><i class="bi bi-star text-danger px-2"></i><?= BASE_NAME ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <?php if (session()->has('userid')) : ?>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link text-primary" href="<?= site_url('keys') ?>">Keys</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-primary" href="<?= site_url('keys/generate') ?>">Generate</a>
                        </li>
                          <?php if ($user && (int)$user->level === 1): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-primary" href="<?= site_url('apishorten') ?>">
                                        API Shorten
                                    </a>
                                </li>
                            <?php endif; ?>

                        
                       
                    </ul>
                    <div class="float-right">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-primary" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle pe-2"></i><?= getName($user) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start" aria-labelledby="navbarDropdown">
                                    <li>
                                        <a class="dropdown-item text-danger" href="<?= site_url('settings') ?>">
                                            <i class="bi bi-gear"></i> Settings
                                        </a>
                                    </li>
                                   
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <?php if ($user->level == 1) : ?>
                                        <li class="dropdown-item text-info">Admin</li>
                                        
                                          <li>
                                        <a class="dropdown-item text-danger" href="<?= site_url('Server') ?>">
                                            <i class="bi-controller"></i> Online System
                                        </a>
                                    </li>
                                        
                                        <li>
                                            <a class="dropdown-item text-danger" href="<?= site_url('admin/manage-users') ?>">
                                                <i class="bi bi-person-check"></i> manage Users
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="<?= site_url('admin/create-referral') ?>">
                                                <i class="bi bi-person-plus"></i> Create Referral
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item text-danger" href="<?= site_url('logout') ?>">
                                            <i class="bi bi-box-arrow-in-left"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
            </div>
        <?php endif; ?>

        </div>
    </nav>
</header>