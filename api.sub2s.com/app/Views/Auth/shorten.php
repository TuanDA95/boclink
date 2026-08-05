<?= $this->extend('Auth/layout') ?>
<?= $this->section('content') ?>

<h4 class="mb-3">⚡ API Rút Gọn Link</h4>

<form method="post" action="<?= site_url('shorten/save') ?>">

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

<hr>

<h6>📌 Ví dụ nhanh</h6>
<ul class="text-muted small">
    <li><b>BBMKTS</b>: bbmktsUrl</li>
    <li><b>FunLink</b>: url</li>
    <li><b>Link4m</b>: shortenedUrl</li>
</ul>

<?= $this->endSection() ?>
