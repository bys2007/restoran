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

date_default_timezone_set('Asia/Jakarta');

mysql_select_db($database_koneksi, $koneksi);
$query_SummaryOrder = "SELECT COUNT(*) AS total_order, SUM(CASE WHEN DATE(tanggal) = CURDATE() THEN 1 ELSE 0 END) AS order_today FROM `order`";
$SummaryOrder = mysql_query($query_SummaryOrder, $koneksi) or die(mysql_error());
$row_SummaryOrder = mysql_fetch_assoc($SummaryOrder);

mysql_select_db($database_koneksi, $koneksi);
$query_StatusOrder = "SELECT SUM(CASE WHEN status_order = 'proses' THEN 1 ELSE 0 END) AS order_proses, SUM(CASE WHEN status_order = 'selesai' THEN 1 ELSE 0 END) AS order_selesai FROM `order`";
$StatusOrder = mysql_query($query_StatusOrder, $koneksi) or die(mysql_error());
$row_StatusOrder = mysql_fetch_assoc($StatusOrder);

mysql_select_db($database_koneksi, $koneksi);
$query_SummaryRevenue = "SELECT COALESCE(SUM(total_bayar),0) AS revenue_total, COALESCE(SUM(CASE WHEN DATE(tanggal) = CURDATE() THEN total_bayar ELSE 0 END),0) AS revenue_today, COUNT(*) AS transaksi_total, SUM(CASE WHEN DATE(tanggal) = CURDATE() THEN 1 ELSE 0 END) AS transaksi_today FROM transaksi";
$SummaryRevenue = mysql_query($query_SummaryRevenue, $koneksi) or die(mysql_error());
$row_SummaryRevenue = mysql_fetch_assoc($SummaryRevenue);

$averageOrderValue = 0;
if ($row_SummaryRevenue['transaksi_total'] > 0) {
  $averageOrderValue = $row_SummaryRevenue['revenue_total'] / $row_SummaryRevenue['transaksi_total'];
}

mysql_select_db($database_koneksi, $koneksi);
$query_TopMasakan = "SELECT m.nama_masakan, COUNT(*) AS jumlah_dipesan, COALESCE(SUM(m.harga),0) AS total_penjualan FROM detail_order d JOIN masakan m ON m.id_masakan = d.id_masakan GROUP BY m.id_masakan, m.nama_masakan ORDER BY jumlah_dipesan DESC LIMIT 5";
$TopMasakan = mysql_query($query_TopMasakan, $koneksi) or die(mysql_error());
$topMasakanData = array();
while ($row = mysql_fetch_assoc($TopMasakan)) {
  $topMasakanData[] = $row;
}

$dailyOrderData = array();
mysql_select_db($database_koneksi, $koneksi);
$query_DailyOrder = "SELECT DATE(tanggal) AS hari, COUNT(*) AS total_order FROM `order` WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(tanggal)";
$DailyOrder = mysql_query($query_DailyOrder, $koneksi) or die(mysql_error());
while ($row = mysql_fetch_assoc($DailyOrder)) {
  $dailyOrderData[$row['hari']] = (int)$row['total_order'];
}

$dailyRevenueData = array();
mysql_select_db($database_koneksi, $koneksi);
$query_DailyRevenue = "SELECT DATE(tanggal) AS hari, SUM(total_bayar) AS total_revenue FROM transaksi WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(tanggal)";
$DailyRevenue = mysql_query($query_DailyRevenue, $koneksi) or die(mysql_error());
while ($row = mysql_fetch_assoc($DailyRevenue)) {
  $dailyRevenueData[$row['hari']] = (int)$row['total_revenue'];
}

$chartLabels = array();
$chartOrders = array();
$chartRevenue = array();
for ($i = 6; $i >= 0; $i--) {
  $dateKey = date('Y-m-d', strtotime("-$i day"));
  $chartLabels[] = date('d M', strtotime($dateKey));
  $chartOrders[] = isset($dailyOrderData[$dateKey]) ? (int)$dailyOrderData[$dateKey] : 0;
  $chartRevenue[] = isset($dailyRevenueData[$dateKey]) ? (int)$dailyRevenueData[$dateKey] : 0;
}

$totalOrder = isset($row_SummaryOrder['total_order']) ? (int)$row_SummaryOrder['total_order'] : 0;
$orderToday = isset($row_SummaryOrder['order_today']) ? (int)$row_SummaryOrder['order_today'] : 0;
$orderProses = isset($row_StatusOrder['order_proses']) ? (int)$row_StatusOrder['order_proses'] : 0;
$orderSelesai = isset($row_StatusOrder['order_selesai']) ? (int)$row_StatusOrder['order_selesai'] : 0;
$revenueTotal = isset($row_SummaryRevenue['revenue_total']) ? (int)$row_SummaryRevenue['revenue_total'] : 0;
$revenueToday = isset($row_SummaryRevenue['revenue_today']) ? (int)$row_SummaryRevenue['revenue_today'] : 0;
$transaksiTotal = isset($row_SummaryRevenue['transaksi_total']) ? (int)$row_SummaryRevenue['transaksi_total'] : 0;
$transaksiToday = isset($row_SummaryRevenue['transaksi_today']) ? (int)$row_SummaryRevenue['transaksi_today'] : 0;
$averageOrderValueRounded = $averageOrderValue > 0 ? round($averageOrderValue) : 0;
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
          <a class="home" aria-current="page" href="dashboard.php">Dashboard</a>
          <a href="registrasi.php">Registrasi</a>
          <a href="referensi.php">Entri Referensi</a>
          <a href="order.php">Entri Order</a>
          <a href="transaksi.php">Entri Transaksi</a>
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
        <article class="panel" style="width:100%;max-width:1100px;">
          <h2>Ringkasan Operasional</h2>
          <p style="margin-top:6px;color:#5f6b7a;">Statistik singkat aktivitas restoran per hari ini.</p>
          <div style="margin-top:18px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">
            <div style="background:rgba(255,255,255,0.75);border:1px solid rgba(0,0,0,0.05);border-radius:14px;padding:18px;backdrop-filter:blur(6px);">
              <div style="font-size:14px;color:#6c7a89;">Total Order</div>
              <div style="font-size:32px;font-weight:700;color:#2c3e50;margin:4px 0;"><?php echo number_format($totalOrder); ?></div>
              <div style="font-size:13px;color:#7f8c8d;">Hari ini: <?php echo number_format($orderToday); ?></div>
            </div>
            <div style="background:rgba(255,255,255,0.75);border:1px solid rgba(0,0,0,0.05);border-radius:14px;padding:18px;backdrop-filter:blur(6px);">
              <div style="font-size:14px;color:#6c7a89;">Pesanan Diproses</div>
              <div style="font-size:32px;font-weight:700;color:#e67e22;margin:4px 0;"><?php echo number_format($orderProses); ?></div>
              <div style="font-size:13px;color:#7f8c8d;">Selesai: <?php echo number_format($orderSelesai); ?></div>
            </div>
            <div style="background:rgba(255,255,255,0.75);border:1px solid rgba(0,0,0,0.05);border-radius:14px;padding:18px;backdrop-filter:blur(6px);">
              <div style="font-size:14px;color:#6c7a89;">Total Transaksi</div>
              <div style="font-size:32px;font-weight:700;color:#16a085;margin:4px 0;"><?php echo number_format($transaksiTotal); ?></div>
              <div style="font-size:13px;color:#7f8c8d;">Hari ini: <?php echo number_format($transaksiToday); ?></div>
            </div>
            <div style="background:rgba(255,255,255,0.75);border:1px solid rgba(0,0,0,0.05);border-radius:14px;padding:18px;backdrop-filter:blur(6px);">
              <div style="font-size:14px;color:#6c7a89;">Total Pendapatan</div>
              <div style="font-size:28px;font-weight:700;color:#27ae60;margin:4px 0;">Rp <?php echo number_format($revenueTotal, 0, ',', '.'); ?></div>
              <div style="font-size:13px;color:#7f8c8d;">Hari ini: Rp <?php echo number_format($revenueToday, 0, ',', '.'); ?></div>
            </div>
            <div style="background:rgba(255,255,255,0.75);border:1px solid rgba(0,0,0,0.05);border-radius:14px;padding:18px;backdrop-filter:blur(6px);">
              <div style="font-size:14px;color:#6c7a89;">Rata-rata per Transaksi</div>
              <div style="font-size:28px;font-weight:700;color:#34495e;margin:4px 0;">Rp <?php echo number_format($averageOrderValueRounded, 0, ',', '.'); ?></div>
              <div style="font-size:13px;color:#7f8c8d;">Total transaksi: <?php echo number_format($transaksiTotal); ?></div>
            </div>
          </div>
        </article>

        <div style="width:100%;display:flex;flex-wrap:wrap;gap:18px;margin-top:18px;">
          <article class="panel" style="flex:1 1 320px;min-width:280px;">
            <h2>Peringkat Masakan</h2>
            <p style="margin-top:6px;color:#5f6b7a;">Top 5 menu yang paling sering dipesan.</p>
            <?php if (count($topMasakanData) > 0) { ?>
              <ol style="margin:16px 0 0 18px;padding:0;display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($topMasakanData as $index => $masakan) { ?>
                  <li>
                    <div style="font-weight:600;color:#2c3e50;"><?php echo htmlspecialchars($masakan['nama_masakan'], ENT_QUOTES); ?></div>
                    <div style="font-size:13px;color:#7f8c8d;">Dipesan <?php echo number_format($masakan['jumlah_dipesan']); ?> kali · Rp <?php echo number_format($masakan['total_penjualan'], 0, ',', '.'); ?></div>
                  </li>
                <?php } ?>
              </ol>
            <?php } else { ?>
              <p style="margin-top:16px;color:#7f8c8d;">Belum ada data pemesanan masakan.</p>
            <?php } ?>
          </article>

          <article class="panel" style="flex:1 1 320px;min-width:280px;">
            <h2>Status Order & Transaksi</h2>
            <p style="margin-top:6px;color:#5f6b7a;">Gambaran singkat antrian order dan transaksi.</p>
            <ul style="list-style:none;padding:0;margin:16px 0 0;display:flex;flex-direction:column;gap:12px;">
              <li style="display:flex;justify-content:space-between;color:#2c3e50;"><span>Order dalam proses</span><strong><?php echo number_format($orderProses); ?></strong></li>
              <li style="display:flex;justify-content:space-between;color:#2c3e50;"><span>Order selesai</span><strong><?php echo number_format($orderSelesai); ?></strong></li>
              <li style="display:flex;justify-content:space-between;color:#2c3e50;"><span>Transaksi hari ini</span><strong><?php echo number_format($transaksiToday); ?></strong></li>
              <li style="display:flex;justify-content:space-between;color:#2c3e50;"><span>Pendapatan hari ini</span><strong>Rp <?php echo number_format($revenueToday, 0, ',', '.'); ?></strong></li>
              <li style="display:flex;justify-content:space-between;color:#2c3e50;"><span>Average ticket size</span><strong>Rp <?php echo number_format($averageOrderValueRounded, 0, ',', '.'); ?></strong></li>
            </ul>
            <div style="margin-top:18px;">
              <a class="btn" href="transaksi.php" style="display:inline-flex;align-items:center;gap:6px;"><i class='bx bx-receipt'></i> Kelola transaksi</a>
            </div>
          </article>
        </div>

        <article class="panel" style="width:100%;max-width:1100px;margin-top:18px;">
          <h2>Perbandingan 7 Hari Terakhir</h2>
          <p style="margin-top:6px;color:#5f6b7a;">Jumlah order dan pendapatan dalam rentang 7 hari terakhir.</p>
          <div style="margin-top:18px;">
            <canvas id="chartOrdersRevenue" height="280"></canvas>
          </div>
        </article>
      </section>
    </div>
  </div>
  <script src="../assets/script.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
  <script>
var chartLabels = <?php echo json_encode($chartLabels); ?>;
var chartOrders = <?php echo json_encode($chartOrders); ?>;
var chartRevenue = <?php echo json_encode($chartRevenue); ?>;
var chartCanvas = document.getElementById('chartOrdersRevenue');

if (chartCanvas && typeof Chart !== 'undefined') {
  new Chart(chartCanvas, {
    type: 'line',
    data: {
      labels: chartLabels,
      datasets: [
        {
          label: 'Order',
          data: chartOrders,
          borderColor: '#3498db',
          backgroundColor: 'rgba(52, 152, 219, 0.15)',
          borderWidth: 2,
          tension: 0.35,
          yAxisID: 'y'
        },
        {
          label: 'Pendapatan',
          data: chartRevenue,
          borderColor: '#27ae60',
          backgroundColor: 'rgba(39, 174, 96, 0.15)',
          borderWidth: 2,
          tension: 0.35,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        },
        y1: {
          beginAtZero: true,
          position: 'right',
          grid: {
            drawOnChartArea: false
          },
          ticks: {
            callback: function(value) {
              return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            }
          }
        }
      },
      plugins: {
        legend: {
          display: true
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              if (context.dataset.label === 'Pendapatan') {
                return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
              }
              return context.dataset.label + ': ' + context.parsed.y;
            }
          }
        }
      }
    }
  });
}
  </script>
</body>
</html>
<?php
mysql_free_result($DailyRevenue);
mysql_free_result($DailyOrder);
mysql_free_result($TopMasakan);
mysql_free_result($SummaryRevenue);
mysql_free_result($StatusOrder);
mysql_free_result($SummaryOrder);
mysql_free_result($Ruser);
?>
