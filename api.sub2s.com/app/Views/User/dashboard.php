<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-12">
        <?= $this->include('Layout/msgStatus') ?>
    </div> 
                        
    
       
    

    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header text-white bg-primary">
                Registration History
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover text-center">
                        <tbody>
                           <?php foreach ($history as $h) : ?>
    <?php 
        // Tách chuỗi và chuẩn bị mảng mặc định để tránh lỗi Undefined Index
        $in = explode("|", $h->info);
        $game    = isset($in[0]) ? $in[0] : 'N/A';
        $key     = isset($in[1]) ? $in[1] : 'N/A';
        $hours   = isset($in[2]) ? $in[2] : '0';
        $devices = isset($in[3]) ? $in[3] : '0';
    ?>
    <tr>
        <td><span class="align-middle badge text-success">#3812<?= $h->id_history ?></span></td>
        <td><?= $game ?></td>
        <td><span class="align-middle badge text-info"><?= $key ?>**</span></td>
        <td><span class="align-middle badge text-warning"><?= $hours ?> Hours</span></td>
        <td><span class="align-middle badge text-primary"><?= $devices ?> Devices</span></td>
        <td>
            <i class="align-middle badge text-danger">
                <?= $time::parse($h->created_at)->humanize() ?>
            </i>
        </td>
    </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header text-center text-white bg-primary">
                Information
            </div>
            <div class="card-body">
                <ul class="list-group list-hover mb-3">
                    <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Roles
                        <span class="text-danger btn btn-outline-warning">
                            <?= getLevel($user->level) ?>
                        </span>
                    </li>
                    <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Saldo
                        <span class="text-danger btn btn-outline-warning">
                            ₹ <?= $user->saldo ?>
                        </span>
                    </li>
                </ul>
                <ul class="list-group">
                    <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Login Time
                        <span class="text-danger btn btn-outline-warning">
                            <?= $time::parse(session()->time_since)->humanize() ?>
                        </span>
                    </li>
                    <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Auto Logout
                        <span class="text-danger btn btn-outline-warning">
                            <?= $time::now()->difference($time::parse(session()->time_login))->humanize() ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
  
</div>
<?= $this->endSection() ?>