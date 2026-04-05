<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php'; // ดึงไฟล์ตั้งค่ามาใช้งาน



// --- 2. Query ข้อมูล ---
$today = date('Y-m-d');
$thai_date = date('j/m/') . (date('Y') + 543);
$time = date('H:i');

function getVal($pdo, $sql) {
    $stmt = $pdo->query($sql);
    return $stmt->fetchColumn() ?: 0;
}

$report = [
    'total'    => getVal($pdo, "SELECT COUNT(DISTINCT vn) FROM ovst WHERE vstdate = '$today'"),
    'er'       => getVal($pdo, "SELECT COUNT(o.vn) FROM ovst o LEFT JOIN er_regist e ON o.vn = e.vn WHERE o.main_dep='011' AND o.vstdate = '$today'"),
    'dental'   => getVal($pdo, "SELECT COUNT(vn) FROM ovst WHERE vstdate = '$today' AND main_dep ='005'"),
    'thai_med' => getVal($pdo, "SELECT COUNT(vn) FROM ovst WHERE vstdate = '$today' AND main_dep ='041'"),
    'physio'   => getVal($pdo, "SELECT COUNT(vn) FROM ovst WHERE vstdate = '$today' AND main_dep ='042'"),
    'mind'     => getVal($pdo, "SELECT COUNT(vn) FROM ovst WHERE vstdate = '$today' AND main_dep ='014'"),
    'refer'    => getVal($pdo, "SELECT COUNT(vn) FROM referout WHERE refer_date = '$today'"),
    'admit'    => getVal($pdo, "SELECT COUNT(DISTINCT an) FROM ipt WHERE regdate = '$today'"),
    'dch'      => getVal($pdo, "SELECT COUNT(DISTINCT an) FROM ipt WHERE dchdate = '$today'"),
    'opd_only' => getVal($pdo, "SELECT COUNT(DISTINCT vn) FROM ovst WHERE vstdate = '$today' AND (an IS NULL OR an ='')"),
    'auth'     => getVal($pdo, "SELECT COUNT(DISTINCT vp.vn) FROM visit_pttype vp LEFT JOIN ovst ov ON ov.vn=vp.vn WHERE ov.vstdate = '$today' AND vp.auth_code IS NOT NULL AND vp.auth_code <> ''")
];

// --- 3. เตรียมข้อมูล Flex Message ---
// กำหนด Icon และสีสำหรับแต่ละแผนก
$items = [
    ["icon" => "🏥", "label" => "ผู้ป่วยนอก (OPD)", "value" => $report['opd_only'], "color" => "#2196F3"],
    ["icon" => "✅", "label" => "ปิดสิทธิสำเร็จ", "value" => $report['auth'], "color" => "#2E7D32"],
    ["icon" => "🚨", "label" => "ฉุกเฉิน (ER)", "value" => $report['er'], "color" => "#F44336"],
    ["icon" => "🦷", "label" => "ทันตกรรม", "value" => $report['dental'], "color" => "#FF9800"],
    ["icon" => "🌿", "label" => "การแพทย์แผนไทย", "value" => $report['thai_med'], "color" => "#4CAF50"],
    ["icon" => "🏃", "label" => "กายภาพบำบัด", "value" => $report['physio'], "color" => "#9C27B0"],
    ["icon" => "🧠", "label" => "คลินิกใจสบาย", "value" => $report['mind'], "color" => "#00BCD4"],
    ["icon" => "🚑", "label" => "ส่งต่อ (Refer Out)", "value" => $report['refer'], "color" => "#607D8B"],
    ["icon" => "🛌", "label" => "รับใหม่ (Admit)", "value" => $report['admit'], "color" => "#795548"],
    ["icon" => "🏠", "label" => "จำหน่าย (DCH)", "value" => $report['dch'], "color" => "#3F51B5"]
    
];

$flex_rows = [];
foreach ($items as $item) {
    $flex_rows[] = [
        "type" => "box",
        "layout" => "horizontal",
        "margin" => "sm",
        "contents" => [
            ["type" => "text", "text" => $item['icon'] . " " . $item['label'], "size" => "sm", "color" => "#444444", "flex" => 4],
            ["type" => "text", "text" => (string)$item['value'], "size" => "sm", "weight" => "bold", "align" => "end", "color" => $item['color'], "flex" => 2],
            ["type" => "text", "text" => "คน", "size" => "xs", "color" => "#aaaaaa", "align" => "end", "flex" => 1]
        ]
    ];
}


$json_payload = [
    "messages" => [
        [
            "type" => "flex",
            "altText" => "📊 รายงานสถิติผู้รับบริการวันนี้",
            "contents" => [
                "type" => "bubble",
                "header" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "backgroundColor" => "#00847b",
                    "contents" => [
                        ["type" => "text", "text" => "Daily Hospital Report", "weight" => "bold", "color" => "#ffffff99", "size" => "xs"],
                        ["type" => "text", "text" => "📊 สถิติผู้รับบริการวันนี้", "weight" => "bold", "color" => "#ffffff", "size" => "xl"],
                        ["type" => "text", "text" => "วันที่ $thai_date | เวลา $time น.", "color" => "#ffffffcc", "size" => "xs", "margin" => "xs"]
                    ]
                ],
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "spacing" => "md",
                    "contents" => [
                        [
                            "type" => "box",
                            "layout" => "vertical",
                            "backgroundColor" => "#E8F5E9",
                            "cornerRadius" => "md",
                            "paddingAll" => "lg",
                            "contents" => [
                                ["type" => "text", "text" => "ยอดรวมผู้รับบริการทั้งหมด (VN)", "size" => "xs", "color" => "#2E7D32", "align" => "center", "weight" => "bold"],
                                ["type" => "text", "text" => (string)$report['total'], "size" => "xxl", "weight" => "bold", "align" => "center", "color" => "#1B5E20", "margin" => "xs"],
                                ["type" => "text", "text" => "ราย", "size" => "xs", "color" => "#2E7D32", "align" => "center"]
                            ]
                        ],
                        ["type" => "separator", "margin" => "lg"],
                        ["type" => "box", "layout" => "vertical", "margin" => "md", "spacing" => "sm", "contents" => $flex_rows]
                    ]
                ],
                "footer" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        ["type" => "text", "text" => "ระบบแจ้งเตือนอัตโนมัติ HOSxP", "size" => "xxs", "color" => "#aaaaaa", "align" => "center"]
                    ]
                ]
            ]
        ]
    ]
];

// --- 5. ส่งข้อมูล (ใช้ค่าจาก config.php) ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, MOPH_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json", 
    "client-key: " . MOPH_CLIENT_KEY, 
    "secret-key: " . MOPH_SECRET_KEY
]);
$response = curl_exec($ch);
curl_close($ch);

echo "MOPH Notify Sent: " . $response;
?>