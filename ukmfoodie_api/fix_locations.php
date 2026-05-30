<?php
include 'db.php';

$replacements = [
    "Kolej Ungku Omar (KUO)" => "Kolej Ungku Omar",
    "Kolej Ibrahim Yaakub (KIY)" => "Kolej Ibrahim Yaakob",
    "Kolej Dato' Onn (KDO)" => "Kolej Dato' Onn",
    "Kolej Tun Hussein Onn (KTHO)" => "Kolej Tun Hussein Onn",
    "Kolej Aminuddin Baki (KAB)" => "Kolej Aminuddin Baki",
    "Kolej Rahim Kajai (KRK)" => "Kolej Rahim Kajai",
    "Kolej Ibu Zain (KIZ)" => "Kolej Ibu Zain",
    "Kolej Keris Mas (KKM)" => "Kolej Keris Mas",
    "Kolej Pendeta Za'ba (KPZ)" => "Kolej Pendeta Za'ba",
    "Kolej Burhanuddin Helmi (KBH)" => "Kolej Burhanuddin Helmi",
    "Fakulti Teknologi dan Sains Maklumat (FTSM)" => "Fakulti Teknologi & Sains Maklumat (FTSM)",
    "Fakulti Ekonomi dan Pengurusan (FEP)" => "Fakulti Ekonomi & Pengurusan (FEP)",
    "Pusat Pelajar (Pusanika)" => "Pusanika",
    "Fakulti Kejuruteraan dan Alam Bina (FKAB)" => "Fakulti Kejuruteraan & Alam Bina (FKAB)"
];

$success_count = 0;
foreach ($replacements as $old => $new) {
    $old_safe = $conn->real_escape_string($old);
    $new_safe = $conn->real_escape_string($new);
    
    $sql = "UPDATE stalls SET location_area = '$new_safe' WHERE location_area = '$old_safe'";
    if ($conn->query($sql)) {
        $success_count += $conn->affected_rows;
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

echo "Successfully updated $success_count stalls location names!\n";
$conn->close();
?>
