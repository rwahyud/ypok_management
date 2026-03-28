<?php
// index.php
// Halaman utama monitoring sensor

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Monitoring Sensor Rizki</title>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">


    <link rel="stylesheet" href="assets/css/index.css">


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script type="text/javascript">
    $(document).ready(function() {

      function loadSensorData() {
        $("#ceksuhu").load("ceksuhu.php");
        $("#cek_kelembapan").load("cek_kelembapan.php");
        $("#cek_ldr").load("cek_ldr.php");
      }

      loadSensorData();

      // update setiap 1 detik (1000 ms)
      setInterval(loadSensorData, 1000);
    });
    </script>
  </head>

  <body>
    <div class="container text-center main-container">
    
      <div class="d-flex justify-content-center align-items-center mb-4">
        <h1 class="text-center">Welcome to My Web <br> Monitoring Sensor</h1>
      </div>

      <br>

      <div class="sensor-row">


        <div class="card text-center sensor-card">
          <div class="card-header card-header-suhu">
            Suhu
          </div>
          <div class="card-body">
            <h1><span id="ceksuhu">0 °C</span></h1>
          </div>
        </div>

        <div class="card text-center sensor-card">
          <div class="card-header card-header-kelembapan">
            Kelembapan
          </div>
          <div class="card-body">
            <h1><span id="cek_kelembapan">0 %</span></h1>
          </div>
        </div>

        <div class="card text-center sensor-card">
          <div class="card-header card-header-ldr">
            LDR (Cahaya)
          </div>
          <div class="card-body">
            <h1><span id="cek_ldr">0 lux</span></h1>
          </div>
        </div>

      </div>

      <div class="sensor-image-wrap">
        <img src="#" alt="Gambar Sensor" class="sensor-image">
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>

