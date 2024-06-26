<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ish Vaqti Hisobi</title>
</head>
<body>
<form action="" method="post">
    <h2>Ish kunini kiriting </h2> (YYYY-MM-DD HH:MM): <br><br>
    ----------------------- Dushanba ------------------- <br><br>
    Kelish vaqti: <input type="datetime-local" name="kelish_vaqti[Dushanba]"><br>
    Ketish vaqti: <input type="datetime-local" name="ketish_vaqti[Dushanba]"><br><br>

    ----------------------- Seshanba ------------------- <br><br>
    Kelish vaqti: <input type="datetime-local" name="kelish_vaqti[Seshanba]"><br>
    Ketish vaqti: <input type="datetime-local" name="ketish_vaqti[Seshanba]"><br><br>

    ---------------------- Chorshanba ------------------ <br><br>
    Kelish vaqti: <input type="datetime-local" name="kelish_vaqti[Chorshanba]"><br>
    Ketish vaqti: <input type="datetime-local" name="ketish_vaqti[Chorshanba]"><br><br>

    ----------------------- Payshanba ------------------- <br><br>
    Kelish vaqti: <input type="datetime-local" name="kelish_vaqti[Payshanba]"><br>
    Ketish vaqti: <input type="datetime-local" name="ketish_vaqti[Payshanba]"><br><br>

    --------------------------  Juma---------------------- <br><br>
    Kelish vaqti: <input type="datetime-local" name="kelish_vaqti[Juma]"><br>
    Ketish vaqti: <input type="datetime-local" name="ketish_vaqti[Juma]"><br><br>

    <button type="submit"><h3>Yuborish</h3></button>
</form>

<?php

//declare(strict_types=1);

/**
 * Bir kunlik ish vaqtini hisoblash funksiyasi
 * @param string $kelishVaqti
 * @param string $ketishVaqti
 * @return float
 */
function ish_vaqtini_hisobla(string $kelishVaqti, string $ketishVaqti): float {
    $kelishVaqtiTimestamp = strtotime($kelishVaqti);
    $ketishVaqtiTimestamp = strtotime($ketishVaqti);

    // Davomiylikni soniyalarda hisoblash
    $davomiylikSekundda = $ketishVaqtiTimestamp - $kelishVaqtiTimestamp;

    // Davomiylikni soatlarda hisoblash
    return $davomiylikSekundda / 3600;
}

/**
 * Umumiy qarz vaqtini hisoblash funksiyasi
 * @param array $kunlikNatijalar
 * @return float
 */
function umumiy_qarz_vaqtini_hisobla(array $kunlikNatijalar): float {
    $umumiyQarzVaqti = 0;

    foreach ($kunlikNatijalar as $natija) {
        $umumiyQarzVaqti += $natija['qarzVaqti'];
    }

    return $umumiyQarzVaqti;
}

/**
 * Sana va ishlamagan kunlarga ko'ra saralash funksiyasi
 * @param array $natijalar
 * @return array
 */
function sana_va_ishlamagan_kunlarga_kora_saralash(array $natijalar): array {
    // Sanaga ko'ra saralash
    usort($natijalar, function($a, $b) {
        return strtotime($a['sana']) - strtotime($b['sana']);
    });

    // Ishlamagan kunlarga (qarzVaqti) ko'ra saralash
    usort($natijalar, function($a, $b) {
        return $a['qarzVaqti'] - $b['qarzVaqti'];
    });

    return $natijalar;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kelishVaqtiList = $_POST['kelish_vaqti'];
    $ketishVaqtiList = $_POST['ketish_vaqti'];

    $kunlikNatijalar = [];

    foreach ($kelishVaqtiList as $kun => $kelishVaqti) {
        $ketishVaqti = $ketishVaqtiList[$kun];

        if (!empty($kelishVaqti) && !empty($ketishVaqti)) {
            // Bir kunlik ish vaqti 9 soat deb hisoblaymiz
            $ishKunlikSoatlar = 9;

            $ishVaqti = ish_vaqtini_hisobla($kelishVaqti, $ketishVaqti);

            // Qarz vaqtini hisoblash
            $qarzVaqti = max(0, $ishKunlikSoatlar - $ishVaqti);

            $kunlikNatijalar[] = [
                'sana' => $kun,
                'ishVaqti' => $ishVaqti,
                'qarzVaqti' => $qarzVaqti
            ];
        }
    }

    if (!empty($kunlikNatijalar)) {
        // Natijalarni saralash
        $saralanganNatijalar = sana_va_ishlamagan_kunlarga_kora_saralash($kunlikNatijalar);

        // Umumiy qarz vaqtini hisoblash
        $umumiyQarzVaqti = umumiy_qarz_vaqtini_hisobla($saralanganNatijalar);

        // Natijalarni chiqarish
        echo "<h2>Natijalar:</h2>";
        echo "<pre>";
        print_r($saralanganNatijalar);
        echo "</pre>";

        // Umumiy qarz vaqtini chiqarish
        echo "<h2>Umumiy qarz vaqti: $umumiyQarzVaqti soat</h2>";

        // Umumiy ish vaqtini hisoblash
        $umumiyIshVaqti = array_reduce($saralanganNatijalar, function($carry, $item) {
            return $carry + $item['ishVaqti'];
        }, 0);

        // Umumiy ish vaqtini chiqarish
        echo "<h2>Umumiy ish vaqti: $umumiyIshVaqti soat</h2>";
    } else {
        echo "<p>Iltimos, barcha maydonlarni to'ldiring.</p>";
    }
}
?>
</body>
</html>
