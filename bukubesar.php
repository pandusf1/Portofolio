<?php
include '../Database/database.php';
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: ../index.php");
  exit();
}

// ✅ Ambil semua akun dari COA dan urut berdasarkan no_akun
$akun_query = mysqli_query($conn, "
  SELECT * FROM coa 
  ORDER BY CAST(SUBSTRING_INDEX(no_akun, '-', 1) AS UNSIGNED), no_akun ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Buku Besar</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .card { border-radius: 15px; margin-bottom: 30px; }
    th { background-color: #007bff !important; color: white; text-align: center; }
    td { text-align: center; vertical-align: middle; }
    h5 { background: #e9ecef; padding: 10px; border-radius: 10px; }
    .periode { color: #6c757d; font-size: 14px; margin-bottom: 10px; }
    .saldo-awal { background-color: #d1ecf1 !important; font-weight: bold; }
    .saldo-akhir { background-color: #fff3cd !important; font-weight: bold; }
  </style>
</head>
<body>

<div class="container mt-4">
  <h3 class="mb-4 text-center">📖 Buku Besar</h3>

  <?php 
  if (mysqli_num_rows($akun_query) == 0) {
      echo "<p class='text-center text-danger fw-bold'>⚠️ Tidak ada akun di COA.</p>";
  }

  while ($akun = mysqli_fetch_assoc($akun_query)):
      $no_akun = $akun['no_akun'];
      $nama_akun = $akun['nama_akun'];
      $tipe = strtolower($akun['tipe']);
      
      // ✅ Ambil saldo awal dari COA
      $saldo_awal = $akun['debit'] - $akun['kredit'];
      
      // ✅ Ambil transaksi dari buku_besar yang sudah diisi
      $transaksi = mysqli_query($conn, "
        SELECT * FROM buku_besar
        WHERE no_akun = '$no_akun'
        ORDER BY tgl ASC, ref ASC
      ");

      // Skip kalau tidak ada transaksi dan saldo awal nol
      if (mysqli_num_rows($transaksi) == 0 && $saldo_awal == 0) continue;
      
      // Hitung saldo akhir
      $saldo = $saldo_awal;
  ?>
      <div class="card shadow-sm">
        <div class="card-body">
          <h5><?= $no_akun ?> - <?= $nama_akun ?> (<?= ucfirst($tipe) ?>)</h5>
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Ref</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Saldo</th>
              </tr>
            </thead>
            <tbody>
              <!-- ✅ Baris Saldo Awal -->
              <tr class="saldo-awal">
                <td colspan="5" class="text-start">Saldo Awal</td>
                <td><?= 'Rp ' . number_format($saldo_awal, 0, ',', '.') ?></td>
              </tr>

  <?php 
      // Loop transaksi dari buku_besar
      while ($row = mysqli_fetch_assoc($transaksi)):
          $debit = $row['debit'];
          $kredit = $row['kredit'];
          
          // ✅ Hitung saldo berdasarkan tipe akun
          // Untuk akun DEBIT (Aset, Beban): Debit menambah, Kredit mengurangi
          // Untuk akun KREDIT (Kewajiban, Modal, Pendapatan): Kredit menambah, Debit mengurangi
          if ($tipe == 'debit') {
              $saldo = $saldo + $debit - $kredit;
          } else {
              $saldo = $saldo + $kredit - $debit;
          }
  ?>
              <tr>
                <td><?= date('d M Y', strtotime($row['tgl'])) ?></td>
                <td class="text-start"><?= $row['keterangan'] ?></td>
                <td><?= $row['ref'] ?></td>
                <td><?= ($debit > 0) ? 'Rp ' . number_format($debit, 0, ',', '.') : '-' ?></td>
                <td><?= ($kredit > 0) ? 'Rp ' . number_format($kredit, 0, ',', '.') : '-' ?></td>
                <td class="fw-bold <?= ($saldo < 0) ? 'text-danger' : 'text-success' ?>">
                  <?= 'Rp ' . number_format($saldo, 0, ',', '.') ?>
                </td>
              </tr>
  <?php endwhile; ?>

              <!-- ✅ Baris Saldo Akhir -->
              <tr class="saldo-akhir">
                <td colspan="5" class="text-start">Saldo Akhir</td>
                <td class="fw-bold"><?= 'Rp ' . number_format($saldo, 0, ',', '.') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
  <?php endwhile; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
