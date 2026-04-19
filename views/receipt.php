<?php
// views/receipt.php
$storeName = $_sModel->get('store_name', 'Restoran KITA');
$storeAddress = $_sModel->get('store_address', 'Alamanda Putrajaya');
$storePhone = $_sModel->get('store_phone', '0123456789');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resit - <?= htmlspecialchars($order['no_pesanan']) ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f4f4f4;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        .receipt-container {
            max-width: 320px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 15px; }
        .mb-3 { margin-bottom: 20px; }
        .title { font-size: 1.4rem; font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        table { width: 100%; font-size: 0.9rem; border-collapse: collapse; }
        th, td { text-align: left; vertical-align: top; padding: 2px 0; }
        .text-right { text-align: right; }
        
        .print-btn-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print {
            background-color: #000;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: bold;
        }
        .btn-print:hover { background-color: #333; }
        
        @media print {
            body { background: transparent; padding: 0; }
            .receipt-container { box-shadow: none; max-width: 100%; border-radius: 0; }
            .print-btn-container { display: none; }
        }
    </style>
</head>
<body>

    <div class="print-btn-container">
        <button class="btn-print" onclick="window.print()">Muat Turun / Cetak Resit</button>
    </div>

    <div class="receipt-container">
        <div class="text-center mb-3">
            <div class="title mb-1"><?= htmlspecialchars($storeName) ?></div>
            <div style="font-size: 0.85rem;"><?= nl2br(htmlspecialchars($storeAddress)) ?></div>
            <?php if ($storePhone): ?>
            <div style="font-size: 0.85rem;">Tel: <?= htmlspecialchars($storePhone) ?></div>
            <?php endif; ?>
        </div>

        <div style="font-size: 0.85rem; margin-bottom: 10px;">
            <div><span class="font-bold">No. Pesanan:</span> <?= htmlspecialchars($order['no_pesanan']) ?></div>
            <div><span class="font-bold">Tarikh:</span> <?= date('d/m/Y h:i A', strtotime($order['created_at'])) ?></div>
            <div><span class="font-bold">Pelanggan:</span> <?= htmlspecialchars($order['nama_pelanggan']) ?></div>
            <?php if (!empty($order['no_meja'])): ?>
            <div><span class="font-bold">No. Meja:</span> <?= htmlspecialchars($order['no_meja']) ?></div>
            <?php endif; ?>
        </div>

        <div class="divider"></div>

        <table class="mb-2">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Amaun</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($item['nama_item']) ?><br>
                        <small><?= $item['kuantiti'] ?> x RM <?= number_format($item['harga_seunit'], 2) ?></small>
                    </td>
                    <td class="text-right">RM <?= number_format($item['jumlah'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="divider"></div>

        <table class="mb-3">
            <tr>
                <td class="font-bold" style="font-size: 1.1rem;">JUMLAH</td>
                <td class="text-right font-bold" style="font-size: 1.1rem;">RM <?= number_format($order['jumlah_harga'], 2) ?></td>
            </tr>
        </table>

        <div class="text-center text-muted" style="font-size: 0.8rem;">
            Terima kasih!<br>Sila datang lagi.
        </div>
    </div>

    <script>
        // Auto trigger print dialogue after 1 second
        setTimeout(() => {
            window.print();
        }, 1000);
    </script>
</body>
</html>
