<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>API Key Manager</title>
    <style>
        body { font-family: Arial; padding: 20px }
        input, select, button { padding: 8px; margin: 5px 0; width: 300px }
        table { border-collapse: collapse; margin-top: 20px; width: 100% }
        th, td { border: 1px solid #ccc; padding: 8px }
    </style>
</head>
<body>

<h2>🔑 Tạo API Key</h2>

<form method="post" action="<?= site_url('admin/ios/key/create') ?>">
     <?= csrf_field() ?>

    <select name="package_id" required>
        <option value="">-- Chọn Package --</option>
        <?php
        $packages = (new \App\Models\PackageModel())->findAll();
        foreach ($packages as $p):
        ?>
            <option value="<?= $p['id'] ?>">
                <?= esc($p['name']) ?> (<?= esc($p['version']) ?>)
            </option>
        <?php endforeach; ?>
    </select><br>

    <input name="days" type="number" placeholder="Số ngày sử dụng" required><br>
    <button type="submit">Tạo Key</button>
</form>

<?php if (session()->getFlashdata('key')): ?>
    <p style="color: green">
        API KEY: <b><?= session()->getFlashdata('key') ?></b>
    </p>
<?php endif; ?>

<hr>

<h2>📋 Danh sách API Key</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Key</th>
        <th>Package</th>
        <th>UDID</th>
        <th>Hết hạn</th>
        <th>IP</th>
        <th>Trạng thái</th>
    </tr>

<?php
$keys = (new \App\Models\ApiKeyModel())->findAll();
foreach ($keys as $k):
?>
<tr>
    <td><?= $k['id'] ?></td>
    <td><?= esc($k['api_key']) ?></td>
    <td><?= $k['package_id'] ?></td>
    <td><?= esc($k['udid'] ?? '-') ?></td>
    <td><?= esc($k['expiry_date']) ?></td>
    <td><?= esc($k['ip_address'] ?? '-') ?></td>
    <td><?= $k['status'] ? 'Active' : 'Blocked' ?></td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>
