<?php
include("library.php");

function showUI() {
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Rate</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Deposit</title>
  <link rel="stylesheet" href="bootstrap-5.3.2-dist/css/bootstrap.min.css" />
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
  <script src="bootstrap-5.3.2-dist/js/bootstrap.bundle.min.js"></script> <!-- Tambahkan ini -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
</head>
<body>

<!-- Modal -->
<div class="modal fade" id="myForm" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"> <!-- Tambahkan warna latar belakang dan warna teks -->
        <h5 class="modal-title" id="myFormTitle">Tambah Data</h5> <!-- Ubah judul modal -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
           <label for="jenis_kamar" class="form-label">Jenis Kamar</label>
           <input type="text" class="form-control" id="jenis_kamar" placeholder="Isikan Jenis Kamar">
        </div>
        <div class="mb-3">
           <label for="rate" class="form-label">Rate</label>
           <input type="text" class="form-control" id="rate" placeholder="Isikan Rate">
        </div>
      </div>		
      <div class="modal-footer">
		<p id="feedback"><p>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button> <!-- Ubah teks tombol -->
        <button id="save" type="button" class="btn btn-primary">Simpan Perubahan</button> <!-- Ubah teks tombol -->
      </div>
    </div>
  </div>
</div>

<div class="container-fluid" style="margin: 20px;">
<p>
<button id="add" type="button" class="btn btn-primary btn-sm">Tambah</button> <!-- Ubah teks tombol -->
</p>
<table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Jenis Kamar</th><th>Rate</th> <!-- Ubah teks header tabel -->
            </tr>
        </thead>
</table>
</div>
<script>
var flag="none";

var table = $('#example').DataTable( {
    serverSide: true,
    ajax: {
        url: '?flag=show',
		type: 'POST'
    },
	columns: [
        { data: 'jenis_kamar' }, { data: 'rate' }
    ]
} );

var myModal = new bootstrap.Modal(document.getElementById('myForm'), {})

$('#add').click(function() {
	flag="add";
	$('#myFormTitle').text("Tambah Data"); <!-- Ubah judul modal -->
	$('#jenis_kamar').val("");
	$('#rate').val("");
	$('#feedback').text("");
	myModal.show();
});

function postToServer(obj, callBack) {
	$.post("?flag=" + flag,
		JSON.stringify(obj), 
	    	function(data,status) {
				if (data["status"]==1) {
					callBack();
				}
				else {
					$("#feedback").text(data["message"]);
				}
		}
	);
}

$('#save').click(function() {
	var formControl = document.getElementsByClassName("form-control");
	var data = {};
	for (var i=0;i<formControl.length;i++) {
		data[formControl[i].id] = formControl[i].value;
	}
	
	postToServer(data, function() {
		$('#myForm').modal('hide');	
		table.ajax.reload();
	});
});
</script>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</body>
</html>
<?php
}
if (isset($_REQUEST["flag"])) {
	if ($_REQUEST["flag"]=="show") {
		$con = openConnection();

		//untuk hitung total baris
		$sqlCount = "SELECT count(*) FROM rate;";

		//untuk mengembalikan data
		$length = intval($_REQUEST["length"]);
		$start = intval($_REQUEST["start"]);
		$sqlData = "SELECT jenis_kamar, rate FROM rate WHERE jenis_kamar LIKE :search LIMIT $length OFFSET $start";

		$data = array();
		$data["draw"]=intval($_REQUEST["draw"]);
		$data["recordsTotal"]=querySingleValue($con, $sqlCount, array());
		$param = array("search"=>$_REQUEST["search"]["value"]."%");
		$data["data"]=queryArrayRowsValues($con, $sqlData, $param);
		$data["recordsFiltered"]=sizeof($data["data"]);
		
		header("Content-type: application/json; charset=utf-8");
		echo json_encode($data);
	}
	else if($_REQUEST["flag"]=="add") {
		$response=array();
		try {
			$con = openConnection();
			$body = file_get_contents('php://input');
			$data = json_decode($body, true);		
			$sql = "INSERT into rate(jenis_kamar, rate) VALUES (:jenis_kamar, :rate);";		
			createRow($con, $sql, $data);
			$response["status"]=1;
			$response["message"]="Ok";
			$response["data"]=$data;
		}
		catch(Exception $e) {
			$response["status"]=0;
			$response["message"]=$e->getMessage();
			$response["data"]=null;						
		}
		
		header("Content-type: application/json; charset=utf-8");
		echo json_encode($response);
	}
}
else {
	showUI();
}
?>
