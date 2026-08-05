<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center pt-3">
    <div class="col-lg-8">
        <?= $this->include('Layout/msgStatus') ?>
    </div>
   <div class="card mb-5">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">⚡ Cấu hình Bọc Link & Thời hạn Key</h5>
    </div>
    <div class="card-body">
        <form method="post" action="<?= site_url('apishorten/save') ?>">
            <?= csrf_field() ?>
             <!-- <div class="col-md-3">
                            <label class="form-label fw-bold text-primary">Link Discord</label>
                            <input type="text" name="discord_link" class="form-control" value="<?= esc($user->discord_link ?? 'https://discord.com/') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-warning">Link Mua Key</label>
                            <input type="text" name="buy_key_link" class="form-control" value="<?= esc($user->buy_key_link ?? 'https://zalo.me/') ?>">
                        </div> -->
                        
            <div class="mb-3">
                <label class="form-label font-weight-bold">Thời gian hiệu lực của Key (Giờ)</label>
                <input type="number" name="key_duration" class="form-control" 
                       value="<?= esc($user->key_duration ?? 12) ?>" min="1" max="8760">
                <small class="text-muted">Mặc định là 12 giờ nếu không thiết lập.</small>
            </div>

            <div class="mb-3">
                <label class="form-label font-weight-bold">Danh sách API Rút Gọn (Mỗi dòng 1 API)</label>
                <textarea name="short_api_list" class="form-control" rows="6" 
                    placeholder="https://domain.com/st?apikey=XXX&url="><?= esc($user->short_api_list ?? '') ?></textarea>
            </div>

            <button class="btn btn-primary w-100">💾 Lưu cấu hình</button>
        </form>
    </div>
</div>
</div>
<?php if (!empty($user->username)) : ?>
<div class="mb-3">
    <label class="form-label">Link GETKEY của bạn</label>
    <div class="input-group">
        <input type="text"
               class="form-control"
               value="<?= esc(site_url($user->username.'/getkeyauto')) ?>"
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

<h6>📌 Code tích hợp dylib login key ios.</h6><a href="https://github.com/binhbun/project/raw/refs/heads/main/GMV.zip">
<button class="btn btn-primary">
        💾
 Download 
    </button></a>

        </div>

    </div>
</div>
<?= $this->endSection() ?>