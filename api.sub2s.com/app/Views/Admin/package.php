<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Package Manager</title>
    <style>
        body { font-family: Arial; padding: 20px }
        input, button { padding: 8px; margin: 5px 0; width: 300px }
        table { border-collapse: collapse; margin-top: 20px; width: 100% }
        th, td { border: 1px solid #ccc; padding: 8px }
    </style>
</head>
<body>

<h2>➕ Thêm Package</h2>
<form method="post" action="<?= site_url('admin/ios/package/create') ?>">
     <?= csrf_field() ?>
    <input name="name" placeholder="Tên Package" required><br>
    <input name="version" placeholder="Version" required><br>
    <button>Tạo Package</button>
</form>


<hr>

<h2>📦 Danh sách Package</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Tên</th>
        <th>Token</th>
        <th>Version</th>
    </tr>

    <?php
    $packages = (new \App\Models\PackageModel())->findAll();
    foreach ($packages as $p):
    ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= esc($p['name']) ?></td>
        <td style="font-size:12px;word-break:break-all">
    <?= esc($p['token']) ?>
</td>

        <td><?= esc($p['version']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
