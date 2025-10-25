function closeModalLoginError() {
  document.getElementById('modalLoginError').style.display = 'none';
  // Hapus parameter error di URL tanpa reload
  if (window.history.replaceState) {
    const url = new URL(window.location);
    url.searchParams.delete('error');
    window.history.replaceState({}, document.title, url.pathname);
  }
}
window.onload = function() {
  if (window.location.search.indexOf('error=1') !== -1) {
    document.getElementById('modalLoginError').style.display = 'flex';
  }
}
window.onclick = function(event) {
  var modal = document.getElementById('modalLoginError');
  if (event.target === modal) {
    closeModalLoginError();
  }
}

function openModalUpdate(id, nama, harga, status) {
    var modal = document.getElementById('modalUpdate');
    modal.style.display = 'flex'; // gunakan flex agar modal-content bisa di tengah
    document.getElementById('id_masakan').value = id;
    document.getElementById('nama_masakan').value = nama;
    document.getElementById('harga').value = harga;
    document.getElementById('status_masakan').value = status;
}
function closeModalUpdate() {
    document.getElementById('modalUpdate').style.display = 'none';
}

function openModalUpdateUser(id, username, password, nama_user, id_level) {
  var modal = document.getElementById('modalUpdateUser');
  modal.style.display = 'flex';
  document.getElementById('id_user_update').value = id;
  document.getElementById('username_update').value = username;
  document.getElementById('password_update').value = password;
  document.getElementById('nama_user_update').value = nama_user;
  document.getElementById('id_level_update').value = id_level;
}
function closeModalUpdateUser() {
  document.getElementById('modalUpdateUser').style.display = 'none';
}
window.onclick = function(event) {
  var modal = document.getElementById('modalUpdateUser');
  if (event.target === modal) {
    closeModalUpdateUser();
  }
};

// Tutup modal jika klik di luar modal-content
window.onclick = function(event) {
    var modal = document.getElementById('modalUpdate');
    if (event.target === modal) {
        closeModalUpdate();
    }
}

function openModalUpdateOrder(id, id_meja, tanggal, id_user, keterangan, status_order) {
  var modal = document.getElementById('modalUpdateOrder');
  modal.style.display = 'flex';
  document.getElementById('id_order_update').value = id;
  document.getElementById('id_meja_update').value = id_meja;
  document.getElementById('tanggal_update').value = tanggal;
  document.getElementById('id_user_update').value = id_user;
  document.getElementById('keterangan_update').value = keterangan;
  document.getElementById('status_order_update').value = status_order;
}
function closeModalUpdateOrder() {
  document.getElementById('modalUpdateOrder').style.display = 'none';
}

function openModalDetailOrder(id_order) {
  // Jika panel sudah terbuka untuk id_order yang sama, tutup panel
  if (currentDetailOrderId === id_order && document.getElementById('panelDetailOrder').style.display === 'block') {
    closeModalDetailOrder();
    return;
  }
  currentDetailOrderId = id_order;
  // Tampilkan panel detail order
  document.getElementById('panelDetailOrder').style.display = 'block';
  var modal = document.getElementById('modalDetailOrder');
  modal.style.display = 'flex';
  // Filter dataDetailOrder
  var tbody = document.getElementById('tbody-detail-order');
  tbody.innerHTML = '';
  dataDetailOrder.filter(function(row) {
    return row.id_order == id_order;
  }).forEach(function(row) {
    var tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${row.id_detail_order}</td>
      <td>${row.id_masakan}</td>
      <td>${row.keterangan || ''}</td>
      <td>${row.status_detail_order}</td>
      <td>
        <button type="button" class="btn" style="background:#3498db; color:#fff;"
          onclick="openModalUpdateDetailOrder('${row.id_detail_order}','${row.id_masakan}','${row.keterangan ? row.keterangan.replace(/'/g,"&#39;") : ''}','${row.status_detail_order}')">
          <i class='bx bx-edit'></i> Update
        </button>
        <a href="delete_detail_order.php?id_detail_order=${row.id_detail_order}"
           onclick="return confirm('Yakin ingin menghapus data ini?')"
           class="btn" style="background:#e74c3c; color:#fff;">
           <i class='bx bx-trash'></i> Delete
        </a>
      </td>
    `;
    tbody.appendChild(tr);
  });
}
function closeModalDetailOrder() {
  document.getElementById('modalDetailOrder').style.display = 'none';
  document.getElementById('panelDetailOrder').style.display = 'none';
  currentDetailOrderId = null;
}

function openModalUpdateDetailOrder(id_detail_order, id_masakan, keterangan, status_detail_order) {
  var modal = document.getElementById('modalUpdateDetailOrder');
  modal.style.display = 'flex';
  document.getElementById('id_detail_order_update').value = id_detail_order;
  document.getElementById('id_masakan_update').value = id_masakan;
  document.getElementById('keterangan_detail_update').value = keterangan;
  document.getElementById('status_detail_order_update').value = status_detail_order;
}
function closeModalUpdateDetailOrder() {
  document.getElementById('modalUpdateDetailOrder').style.display = 'none';
}

function openModalTambahOrder() {
  var modal = document.getElementById('modalTambahOrder');
  if (!modal) {
    return;
  }
  modal.style.display = 'flex';
  var form = document.getElementById('formTambahOrder');
  if (form) {
    form.reset();
  }
  var tanggalInput = document.getElementById('tanggal_tambah');
  if (tanggalInput) {
    var today = new Date().toISOString().split('T')[0];
    tanggalInput.value = today;
  }
}

function closeModalTambahOrder() {
  var modal = document.getElementById('modalTambahOrder');
  if (modal) {
    modal.style.display = 'none';
  }
}

function openModalTambahDetailOrder(orderId) {
  var modal = document.getElementById('modalTambahDetailOrder');
  if (!modal) {
    return;
  }
  var targetOrderId = orderId || currentDetailOrderId;
  if (!targetOrderId) {
    alert('Silakan pilih order terlebih dahulu melalui tombol Detail.');
    return;
  }
  modal.style.display = 'flex';
  var form = document.getElementById('formTambahDetailOrder');
  if (form) {
    form.reset();
  }
  var hiddenField = document.getElementById('id_order_detail_tambah');
  if (hiddenField) {
    hiddenField.value = targetOrderId;
  }
  var displayField = document.getElementById('id_order_detail_display');
  if (displayField) {
    displayField.value = targetOrderId;
  }
}

function closeModalTambahDetailOrder() {
  var modal = document.getElementById('modalTambahDetailOrder');
  if (modal) {
    modal.style.display = 'none';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  var tambahOrderButton = document.getElementById('tambahOrder');
  if (tambahOrderButton) {
    tambahOrderButton.addEventListener('click', function() {
      openModalTambahOrder();
    });
  }

  var tambahDetailButton = document.getElementById('tambahDetailOrder');
  if (tambahDetailButton) {
    tambahDetailButton.addEventListener('click', function() {
      openModalTambahDetailOrder();
    });
  }
});

var currentDetailOrderId = null;