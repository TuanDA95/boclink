<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center pt-3">
    <div class="col-lg-8">
        <?= $this->include('Layout/msgStatus') ?>
    </div>
    <div class="col-lg-8 mb-3">
        <div class="card mb-5">
            <h4 class="mb-3">⚡ API Rút Gọn Link</h4>

<form method="post" action="<?= site_url('apishorten/save') ?>">

    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label">Link API rút gọn</label>
        <input type="text"
               name="short_api_base"
               class="form-control"
               value="<?= esc($user->short_api_base ?? '') ?>"
               placeholder="https://bbmkts.com/dapi?token=XXX&longurl=">
    </div>

    <div class="mb-3">
        <label class="form-label">Kiểu phản hồi</label>
        <select name="short_response_type" class="form-select">
            <option value="json" <?= ($user->short_response_type ?? '') === 'json' ? 'selected' : '' ?>>JSON</option>
            <option value="text" <?= ($user->short_response_type ?? '') === 'text' ? 'selected' : '' ?>>TEXT</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Tên biến URL trả về</label>
        <input type="text"
               name="short_response_key"
               class="form-control"
               value="<?= esc($user->short_response_key ?? '') ?>"
               placeholder="bbmktsUrl | url | shortenedUrl">
    </div>

    <button class="btn btn-primary">
        💾 Lưu cấu hình
    </button>

</form>
<?php if (!empty($user->username)) : ?>
<div class="mb-3">
    <label class="form-label">Link GETKEY của bạn</label>
    <div class="input-group">
        <input type="text"
               class="form-control"
               value="<?= esc(site_url($user->username.'/getkey')) ?>"
               id="getkeyLink"
               readonly>
        <button class="btn btn-outline-secondary" type="button" onclick="copyGetKey()">Copy</button>
    </div>
</div>

<script>
function copyGetKey() {
    const copyText = document.getElementById("getkeyLink");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // cho thiết bị di động
    navigator.clipboard.writeText(copyText.value)
        .then(() => alert("Copied: " + copyText.value))
        .catch(() => alert("Copy failed!"));
}
</script>
<?php endif; ?>
<hr>

<h6>📌 Ví dụ nhanh</h6>
<ul class="text-muted small">
    <li><b>BBMKTS</b>: bbmktsUrl</li>
    <li><b>FunLink</b>: url</li>
    <li><b>Link4m</b>: shortenedUrl</li>
</ul>
        </div>

        <p class="text-muted text-center">
            <a href="<?= site_url('/dashboard') ?>" class="py-1 px-2  text-danger"><small><i class="bi bi-arrow-left"></i> Back to dashboard</small></a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>