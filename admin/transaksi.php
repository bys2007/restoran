<?php require_once('../Connections/koneksi.php'); ?>
<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "../login.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../waiter/dashboard.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$colname_Ruser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_Ruser = $_SESSION['MM_Username'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Ruser = sprintf("SELECT * FROM `user` WHERE username = %s", GetSQLValueString($colname_Ruser, "text"));
$Ruser = mysql_query($query_Ruser, $koneksi) or die(mysql_error());
$row_Ruser = mysql_fetch_assoc($Ruser);
$totalRows_Ruser = mysql_num_rows($Ruser);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$currentUserId = ($totalRows_Ruser > 0 && isset($row_Ruser['id_user'])) ? intval($row_Ruser['id_user']) : 0;

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formTransaksi")) {
  $orderId = isset($_POST['id_order']) ? intval($_POST['id_order']) : 0;
  $tanggalInput = (isset($_POST['tanggal']) && $_POST['tanggal'] !== "") ? $_POST['tanggal'] : date('Y-m-d');
  $totalBayarInput = isset($_POST['total_bayar']) ? intval($_POST['total_bayar']) : 0;

  if ($orderId <= 0) {
    header("Location: transaksi.php?status=missing_order");
    exit;
  }

  mysql_select_db($database_koneksi, $koneksi);
  $query_CheckTransaksi = sprintf("SELECT 1 FROM transaksi WHERE id_order = %s LIMIT 1", GetSQLValueString($orderId, "int"));
  $CheckTransaksi = mysql_query($query_CheckTransaksi, $koneksi) or die(mysql_error());
  $isExists = mysql_num_rows($CheckTransaksi) > 0;
  mysql_free_result($CheckTransaksi);

  if ($isExists) {
    header("Location: transaksi.php?status=duplicate");
    exit;
  }

  mysql_select_db($database_koneksi, $koneksi);
  $query_OrderSummary = sprintf("SELECT SUM(m.harga) AS total_bayar, SUM(CASE WHEN d.status_detail_order <> 'selesai' THEN 1 ELSE 0 END) AS item_belum_selesai FROM detail_order d JOIN masakan m ON m.id_masakan = d.id_masakan WHERE d.id_order = %s", GetSQLValueString($orderId, "int"));
  $OrderSummary = mysql_query($query_OrderSummary, $koneksi) or die(mysql_error());
  $row_OrderSummary = mysql_fetch_assoc($OrderSummary);
  $jumlahBelumSelesai = isset($row_OrderSummary['item_belum_selesai']) ? intval($row_OrderSummary['item_belum_selesai']) : 0;
  $totalBayarComputed = isset($row_OrderSummary['total_bayar']) ? intval($row_OrderSummary['total_bayar']) : 0;
  mysql_free_result($OrderSummary);

  if ($totalBayarComputed <= 0) {
    header("Location: transaksi.php?status=order_empty");
    exit;
  }

  if ($jumlahBelumSelesai > 0) {
    header("Location: transaksi.php?status=not_ready");
    exit;
  }

  if ($totalBayarInput <= 0) {
    $totalBayarInput = $totalBayarComputed;
  }

  $insertSQL = sprintf("INSERT INTO transaksi (id_user, id_order, tanggal, total_bayar) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($currentUserId, "int"),
                       GetSQLValueString($orderId, "int"),
                       GetSQLValueString($tanggalInput, "date"),
                       GetSQLValueString($totalBayarInput, "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  header("Location: transaksi.php?status=success");
  exit;
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formEditTransaksi")) {
  $transaksiId = isset($_POST['id_transaksi']) ? intval($_POST['id_transaksi']) : 0;
  $tanggalInput = (isset($_POST['tanggal']) && $_POST['tanggal'] !== "") ? $_POST['tanggal'] : "";
  $totalBayarInput = isset($_POST['total_bayar']) ? intval($_POST['total_bayar']) : 0;

  if ($transaksiId <= 0) {
    header("Location: transaksi.php?status=missing_transaksi");
    exit;
  }

  mysql_select_db($database_koneksi, $koneksi);
  $query_FindTransaksi = sprintf("SELECT id_order, tanggal, total_bayar FROM transaksi WHERE id_transaksi = %s LIMIT 1", GetSQLValueString($transaksiId, "int"));
  $FindTransaksi = mysql_query($query_FindTransaksi, $koneksi) or die(mysql_error());

  if (mysql_num_rows($FindTransaksi) === 0) {
    mysql_free_result($FindTransaksi);
    header("Location: transaksi.php?status=notfound");
    exit;
  }

  $rowTransaksi = mysql_fetch_assoc($FindTransaksi);
  mysql_free_result($FindTransaksi);

  if ($tanggalInput === "") {
    $tanggalInput = $rowTransaksi['tanggal'];
  }

  if ($totalBayarInput <= 0) {
    header("Location: transaksi.php?status=invalid_total");
    exit;
  }

  $updateSQL = sprintf("UPDATE transaksi SET tanggal=%s, total_bayar=%s, id_user=%s WHERE id_transaksi=%s",
                       GetSQLValueString($tanggalInput, "date"),
                       GetSQLValueString($totalBayarInput, "int"),
                       GetSQLValueString($currentUserId, "int"),
                       GetSQLValueString($transaksiId, "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $ResultUpdate = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  header("Location: transaksi.php?status=updated");
  exit;
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rpending = "SELECT o.id_order, o.id_meja, o.tanggal, o.id_user, u.nama_user, u.username, SUM(m.harga) AS total_bayar, COUNT(d.id_detail_order) AS jumlah_item, SUM(CASE WHEN d.status_detail_order <> 'selesai' THEN 1 ELSE 0 END) AS item_belum_selesai FROM `order` o JOIN detail_order d ON d.id_order = o.id_order JOIN masakan m ON m.id_masakan = d.id_masakan LEFT JOIN transaksi t ON t.id_order = o.id_order LEFT JOIN `user` u ON u.id_user = o.id_user WHERE o.status_order = 'selesai' AND t.id_order IS NULL GROUP BY o.id_order, o.id_meja, o.tanggal, o.id_user, u.nama_user, u.username HAVING item_belum_selesai = 0 ORDER BY o.tanggal ASC, o.id_order ASC";
$Rpending = mysql_query($query_Rpending, $koneksi) or die(mysql_error());
$row_Rpending = mysql_fetch_assoc($Rpending);
$totalRows_Rpending = mysql_num_rows($Rpending);

mysql_select_db($database_koneksi, $koneksi);
$query_Rhistory = "SELECT t.id_transaksi, t.id_order, t.tanggal, t.total_bayar, u.nama_user, u.username, o.id_meja FROM transaksi t LEFT JOIN `user` u ON u.id_user = t.id_user LEFT JOIN `order` o ON o.id_order = t.id_order ORDER BY t.tanggal DESC, t.id_transaksi DESC";
$Rhistory = mysql_query($query_Rhistory, $koneksi) or die(mysql_error());
$row_Rhistory = mysql_fetch_assoc($Rhistory);
$totalRows_Rhistory = mysql_num_rows($Rhistory);

$statusMessage = "";
$statusType = "";
if (isset($_GET['status'])) {
  switch ($_GET['status']) {
    case 'success':
      $statusMessage = "Transaksi berhasil dicatat.";
      $statusType = "success";
      break;
    case 'updated':
      $statusMessage = "Transaksi berhasil diperbarui.";
      $statusType = "success";
      break;
    case 'deleted':
      $statusMessage = "Transaksi berhasil dihapus.";
      $statusType = "success";
      break;
    case 'duplicate':
      $statusMessage = "Order ini sudah memiliki transaksi.";
      $statusType = "error";
      break;
    case 'missing_order':
      $statusMessage = "Order tidak ditemukan. Silakan pilih ulang.";
      $statusType = "error";
      break;
    case 'not_ready':
      $statusMessage = "Order belum selesai diproses di dapur.";
      $statusType = "error";
      break;
    case 'order_empty':
      $statusMessage = "Order belum memiliki detail masakan.";
      $statusType = "error";
      break;
    case 'missing_transaksi':
      $statusMessage = "Transaksi tidak ditemukan. Silakan pilih data yang valid.";
      $statusType = "error";
      break;
    case 'invalid_total':
      $statusMessage = "Total bayar harus lebih besar dari 0.";
      $statusType = "error";
      break;
    case 'notfound':
      $statusMessage = "Data transaksi tidak tersedia.";
      $statusType = "error";
      break;
  }
}

$pendingOrderIds = array();
$pendingOrdersData = array();
if ($totalRows_Rpending > 0) {
  do {
    $pendingOrderIds[] = intval($row_Rpending['id_order']);
    $pendingOrdersData[] = array(
      'id_order' => intval($row_Rpending['id_order']),
      'id_meja' => intval($row_Rpending['id_meja']),
      'tanggal' => $row_Rpending['tanggal'],
      'nama_user' => $row_Rpending['nama_user'],
      'username' => $row_Rpending['username'],
      'total_bayar' => intval($row_Rpending['total_bayar']),
      'jumlah_item' => intval($row_Rpending['jumlah_item'])
    );
  } while ($row_Rpending = mysql_fetch_assoc($Rpending));
  mysql_data_seek($Rpending, 0);
  $row_Rpending = mysql_fetch_assoc($Rpending);
}

$pendingDetailData = array();
if (count($pendingOrderIds) > 0) {
  $idList = implode(',', $pendingOrderIds);
  mysql_select_db($database_koneksi, $koneksi);
  $query_RpendingDetail = "SELECT d.id_order, d.id_detail_order, m.nama_masakan, m.harga, d.keterangan, d.status_detail_order FROM detail_order d JOIN masakan m ON m.id_masakan = d.id_masakan WHERE d.id_order IN (" . $idList . ") ORDER BY d.id_order, d.id_detail_order";
  $RpendingDetail = mysql_query($query_RpendingDetail, $koneksi) or die(mysql_error());
  while ($row = mysql_fetch_assoc($RpendingDetail)) {
    $pendingDetailData[] = $row;
  }
  mysql_free_result($RpendingDetail);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Dashboard Admin | Restoran bys</title>
  <link rel="icon" type="image/png" href="../assets/favicon.png" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@600&amp;family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet"/>
</head>
<body>
  <div class="top-bar">
      Selamat datang admin, <span><?php echo $row_Ruser['username']; ?></span>
  </div>
  <div class="page-wrapper">
    <div aria-hidden="true" class="background-blur">
      <img alt="Blurred background image" src="https://storage.googleapis.com/a1aa/image/53f88344-6f93-4104-a77e-b097980e4a86.jpg" width="1920" height="1080"/>
    </div>
    <div class="content-container" role="main">
      <aside class="sidebar">
        <div class="logo">
          BYS<small>RESTORAN</small>
        </div>
        <nav>
          <a href="dashboard.php">Dashboard</a>
          <a href="registrasi.php">Registrasi</a>
          <a href="referensi.php">Entri Referensi</a>
          <a href="order.php">Entri Order</a>
          <a class="home" aria-current="page" href="transaksi.php">Entri Transaksi</a>
          <a href="laporan.php">Generate Laporan</a>
      <a href="<?php echo $logoutAction ?>">Logout</a>
        </nav>
        <div class="social-icons">
          <a aria-label="Facebook" href="#"><i class="fab fa-facebook-f"></i></a>
          <a aria-label="Tiktok" href="#"><i class="fab fa-tiktok"></i></a>
          <a aria-label="Instagram" href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </aside>
      <section class="main-panels">
        <?php if ($statusMessage !== "") { ?>
        <div class="notification-box" style="width:100%;max-width:900px;padding:12px 18px;border-radius:10px;margin-bottom:18px;border:1px solid <?php echo ($statusType === "success") ? "#27ae60" : "#e74c3c"; ?>;background:<?php echo ($statusType === "success") ? "#ecf9f1" : "#fdecea"; ?>;color:<?php echo ($statusType === "success") ? "#1e8449" : "#922b21"; ?>;">
          <?php echo htmlspecialchars($statusMessage, ENT_QUOTES); ?>
        </div>
        <?php } ?>

        <article class="panel" style="width:100%;max-width:900px;">
          <h2>Order Siap Pembayaran</h2>
          <p style="margin-top:6px;color:#666;">Daftar order yang semua detailnya sudah selesai dimasak dan belum tercatat di tabel transaksi.</p>
          <table class="table-user" style="margin-top:18px;">
            <thead>
              <tr>
                <th>ID Order</th>
                <th>No Meja</th>
                <th>Tanggal</th>
                <th>Pemesan</th>
                <th>Item</th>
                <th>Total</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($totalRows_Rpending > 0) { ?>
                <?php do { ?>
                  <?php
                    $namaPemesan = $row_Rpending['nama_user'] != "" ? $row_Rpending['nama_user'] : $row_Rpending['username'];
                  ?>
                  <tr>
                    <td><?php echo $row_Rpending['id_order']; ?></td>
                    <td><?php echo $row_Rpending['id_meja']; ?></td>
                    <td><?php echo $row_Rpending['tanggal']; ?></td>
                    <td><?php echo htmlspecialchars($namaPemesan, ENT_QUOTES); ?></td>
                    <td><?php echo $row_Rpending['jumlah_item']; ?></td>
                    <td>Rp <?php echo number_format($row_Rpending['total_bayar'], 0, ',', '.'); ?></td>
                    <td>
                      <button type="button" class="btn" style="background:#27ae60; color:#fff;"
                        onclick="openModalTransaksi('<?php echo $row_Rpending['id_order']; ?>')">
                        <i class='bx bx-money'></i> Proses Pembayaran
                      </button>
                    </td>
                  </tr>
                <?php } while ($row_Rpending = mysql_fetch_assoc($Rpending)); ?>
              <?php } else { ?>
                <tr>
                  <td colspan="7" style="text-align:center; padding:18px;">Belum ada order yang siap dibayar.</td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </article>

        <article class="panel" style="width:100%;max-width:900px; margin-top:24px;">
          <h2>Riwayat Transaksi</h2>
          <p style="margin-top:6px;color:#666;">Catatan transaksi terbaru ditampilkan dengan waktu paling akhir di bagian atas.</p>
          <table class="table-user" style="margin-top:18px;">
           <thead>
              <tr>
                <th>ID Transaksi</th>
                <th>Tanggal</th>
                <th>ID Order</th>
                <th>No Meja</th>
                <th>Kasir</th>
                <th>Total Bayar</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($totalRows_Rhistory > 0) { ?>
                <?php do { ?>
                  <?php
                    $namaKasir = $row_Rhistory['nama_user'] != "" ? $row_Rhistory['nama_user'] : $row_Rhistory['username'];
                     $nomorMeja = isset($row_Rhistory['id_meja']) && $row_Rhistory['id_meja'] !== "" ? $row_Rhistory['id_meja'] : "-";
                  ?>
                  <tr>
                    <td><?php echo $row_Rhistory['id_transaksi']; ?></td>
                    <td><?php echo $row_Rhistory['tanggal']; ?></td>
                    <td><?php echo $row_Rhistory['id_order']; ?></td>
                    <td><?php echo $nomorMeja; ?></td>
                    <td><?php echo htmlspecialchars($namaKasir, ENT_QUOTES); ?></td>
                    <td>Rp <?php echo number_format($row_Rhistory['total_bayar'], 0, ',', '.'); ?></td>
                    <td>
                      <button type="button" class="btn" style="background:#3498db; color:#fff;"
                        onclick="openModalEditTransaksi(<?php echo intval($row_Rhistory['id_transaksi']); ?>, <?php echo intval($row_Rhistory['id_order']); ?>, '<?php echo htmlspecialchars($row_Rhistory['tanggal'], ENT_QUOTES); ?>', <?php echo intval($row_Rhistory['total_bayar']); ?>)">
                        <i class='bx bx-edit'></i> Edit
                      </button>
                      <a href="delete_transaksi.php?id_transaksi=<?php echo $row_Rhistory['id_transaksi']; ?>"
                         onclick="return confirm('Yakin ingin menghapus transaksi ini?');"
                         class="btn" style="background:#e74c3c; color:#fff;">
                        <i class='bx bx-trash'></i> Delete
                      </a>
                    </td>
                  </tr>
                <?php } while ($row_Rhistory = mysql_fetch_assoc($Rhistory)); ?>
              <?php } else { ?>
                <tr>
                  <td colspan="7" style="text-align:center; padding:18px;">Belum ada transaksi yang tercatat.</td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </article>

        <div id="modalEditTransaksi" class="modal-update" style="display:none;">
          <div class="modal-content" style="max-width:480px;">
            <span class="close-modal" onclick="closeModalEditTransaksi()">&times;</span>
            <form method="post" action="<?php echo $editFormAction; ?>" class="form-register" id="formEditTransaksi">
              <div class="title" style="margin-bottom:18px;">Edit Transaksi</div>
              <div class="order-summary" style="margin-bottom:16px; line-height:1.6;">
                <div><strong>ID Transaksi:</strong> <span id="edit_info_transaksi">-</span></div>
                <div><strong>ID Order:</strong> <span id="edit_info_order">-</span></div>
                <div><strong>Total Saat Ini:</strong> <span id="edit_info_total">Rp 0</span></div>
              </div>
              <div class="input-group">
                <i class='bx bx-calendar'></i>
                <input type="date" name="tanggal" id="edit_transaksi_tanggal" required />
              </div>
              <div class="input-group">
                <i class='bx bx-money'></i>
                <input type="number" name="total_bayar" id="edit_transaksi_total_bayar" min="0" placeholder="Total Bayar" required />
              </div>
              <p style="font-size:12px; color:#666; margin-bottom:18px;">Pastikan nominal sesuai dengan pembayaran yang diterima.</p>
              <input type="hidden" name="id_transaksi" id="edit_transaksi_id" />
              <input type="hidden" name="MM_update" value="formEditTransaksi" />
              <button type="submit" class="btn" style="background:#3498db; color:#fff; width:100%;">Simpan Perubahan</button>
            </form>
          </div>
        </div>

        <div id="modalTransaksi" class="modal-update" style="display:none;">
          <div class="modal-content" style="max-width:540px;">
            <span class="close-modal" onclick="closeModalTransaksi()">&times;</span>
            <form method="post" action="<?php echo $editFormAction; ?>" class="form-register" id="formTransaksi">
              <div class="title" style="margin-bottom:18px;">Catat Pembayaran</div>
              <div class="order-summary" style="margin-bottom:16px; line-height:1.6;">
                <div><strong>ID Order:</strong> <span id="info_order_id">-</span></div>
                <div><strong>No Meja:</strong> <span id="info_order_meja">-</span></div>
                <div><strong>Pemesan:</strong> <span id="info_order_pemesan">-</span></div>
                <div><strong>Jumlah Item:</strong> <span id="info_order_jumlah_item">0</span></div>
                <div><strong>Total Detail:</strong> <span id="info_order_total">Rp 0</span></div>
              </div>
              <div style="margin-bottom:12px;">
                <strong>Detail Masakan:</strong>
                <ul id="transaksi_detail_list" style="list-style:none;padding-left:0;margin:8px 0 0;"></ul>
              </div>
              <div class="input-group">
                <i class='bx bx-calendar'></i>
                <input type="date" name="tanggal" id="transaksi_tanggal" required />
              </div>
              <div class="input-group">
                <i class='bx bx-money'></i>
                <input type="number" name="total_bayar" id="transaksi_total_bayar" min="0" placeholder="Total Bayar" required />
              </div>
              <p style="font-size:12px; color:#666; margin-bottom:18px;">Nominal dapat disesuaikan apabila ada diskon atau pembulatan.</p>
              <input type="hidden" name="id_order" id="transaksi_id_order" />
              <input type="hidden" name="MM_insert" value="formTransaksi" />
              <button type="submit" class="btn" style="background:#27ae60; color:#fff; width:100%;">Simpan Transaksi</button>
            </form>
          </div>
        </div>
      </section>
    </div>
  </div>
  <script src="../assets/script.js"></script>
  <script>
var pendingOrdersData = <?php echo json_encode($pendingOrdersData); ?>;
var pendingDetailData = <?php echo json_encode($pendingDetailData); ?>;
var modalTransaksi = document.getElementById('modalTransaksi');
var modalEditTransaksi = document.getElementById('modalEditTransaksi');

function openModalTransaksi(orderId) {
  if (!modalTransaksi) {
    return;
  }
  var target = null;
  for (var i = 0; i < pendingOrdersData.length; i++) {
    if (String(pendingOrdersData[i].id_order) === String(orderId)) {
      target = pendingOrdersData[i];
      break;
    }
  }
  if (!target) {
    alert('Data order tidak ditemukan.');
    return;
  }
  var today = new Date().toISOString().split('T')[0];
  var defaultDate = (target.tanggal && target.tanggal !== '0000-00-00') ? target.tanggal : today;
  var tanggalInput = document.getElementById('transaksi_tanggal');
  if (tanggalInput) {
    tanggalInput.value = defaultDate;
  }
  document.getElementById('transaksi_id_order').value = target.id_order;
  document.getElementById('info_order_id').innerText = target.id_order;
  document.getElementById('info_order_meja').innerText = target.id_meja || '-';
  document.getElementById('info_order_pemesan').innerText = target.nama_user ? target.nama_user : (target.username || '-');
  document.getElementById('info_order_jumlah_item').innerText = target.jumlah_item || 0;
  document.getElementById('transaksi_total_bayar').value = target.total_bayar || 0;
  document.getElementById('info_order_total').innerText = formatRupiah(target.total_bayar || 0);

  var detailList = document.getElementById('transaksi_detail_list');
  if (detailList) {
    detailList.innerHTML = '';
    var detailItems = [];
    for (var j = 0; j < pendingDetailData.length; j++) {
      if (String(pendingDetailData[j].id_order) === String(orderId)) {
        detailItems.push(pendingDetailData[j]);
      }
    }
    if (detailItems.length === 0) {
      var emptyItem = document.createElement('li');
      emptyItem.textContent = 'Belum ada detail masakan.';
      detailList.appendChild(emptyItem);
    } else {
      for (var k = 0; k < detailItems.length; k++) {
        var item = detailItems[k];
        var li = document.createElement('li');
        var harga = parseInt(item.harga, 10) || 0;
        var teks = (item.nama_masakan || '') + ' - ' + formatRupiah(harga);
        if (item.keterangan) {
          teks += ' (' + item.keterangan + ')';
        }
        li.textContent = teks;
        detailList.appendChild(li);
      }
    }
  }

  modalTransaksi.style.display = 'flex';
}

function closeModalTransaksi() {
  if (modalTransaksi) {
    modalTransaksi.style.display = 'none';
  }
}

function openModalEditTransaksi(transaksiId, orderId, tanggal, totalBayar) {
  if (!modalEditTransaksi) {
    return;
  }
  var targetTanggal = tanggal && tanggal !== '0000-00-00' ? tanggal : new Date().toISOString().split('T')[0];
  var tanggalField = document.getElementById('edit_transaksi_tanggal');
  if (tanggalField) {
    tanggalField.value = targetTanggal;
  }
  var totalValue = parseInt(totalBayar, 10);
  if (isNaN(totalValue) || totalValue < 0) {
    totalValue = 0;
  }
  var totalField = document.getElementById('edit_transaksi_total_bayar');
  if (totalField) {
    totalField.value = totalValue;
  }
  var infoTransaksi = document.getElementById('edit_info_transaksi');
  if (infoTransaksi) {
    infoTransaksi.innerText = transaksiId || '-';
  }
  var infoOrder = document.getElementById('edit_info_order');
  if (infoOrder) {
    infoOrder.innerText = orderId || '-';
  }
  var infoTotal = document.getElementById('edit_info_total');
  if (infoTotal) {
    infoTotal.innerText = formatRupiah(totalValue);
  }
  var hiddenId = document.getElementById('edit_transaksi_id');
  if (hiddenId) {
    hiddenId.value = transaksiId || '';
  }
  modalEditTransaksi.style.display = 'flex';
}

function closeModalEditTransaksi() {
  if (modalEditTransaksi) {
    modalEditTransaksi.style.display = 'none';
  }
}

function formatRupiah(value) {
  var number = parseInt(value, 10) || 0;
  return 'Rp ' + number.toLocaleString('id-ID');
}

window.addEventListener('click', function(event) {
  if (event.target === modalTransaksi) {
    closeModalTransaksi();
  }
  if (event.target === modalEditTransaksi) {
    closeModalEditTransaksi();
  }
});
  </script>
</body>
</html>
<?php
mysql_free_result($Rhistory);
mysql_free_result($Rpending);
mysql_free_result($Ruser);
?>
