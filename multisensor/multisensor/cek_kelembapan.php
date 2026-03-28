<?php
    // Array database names to try
    $databases = ['dbsensor', 'db_sensor', 'multisensor'];
    $koneksi = null;
    
    // Try connecting to different database names
    foreach($databases as $db_name) {
        $koneksi = @mysqli_connect("localhost", "root", "", $db_name);
        if ($koneksi) {
            break;
        }
    }
    
    // Check connection
    if (!$koneksi) {
        echo "0"; // Return 0 if no connection
        exit;
    }

    // Query data from tb_sensor table
    $sql = "SELECT * FROM tb_sensor ORDER BY id DESC LIMIT 1";
    $query = mysqli_query($koneksi, $sql);

    // Check if query successful and has data
    if ($query && mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_array($query);
        $kelembapan = $data['kelembapan'];
        
        // Check if kelembapan value exists
        if($kelembapan == "" || $kelembapan == null) {
            $kelembapan = 0;
        }
        
        echo $kelembapan;
    } else {
        echo "0"; // Return 0 if no data
    }

    // Close connection
    if ($koneksi) {
        mysqli_close($koneksi);
    }
?>