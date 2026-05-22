<?php

function make_id() {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $id = '';
    for ($i = 0; $i < 20; $i++) {
        $id .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $id;
}

function create_rect($x, $y, $w, $h, $text, $stroke_color="#000000", $bg_color="transparent", $fill_style="hachure", $roundness=true) {
    $rect_id = make_id();
    $text_id = make_id();
    
    // Text sizing approximation
    $char_width = 7.5;
    $font_size = 14;
    $lines = explode("\n", $text);
    $max_line_len = 0;
    foreach ($lines as $line) {
        $max_line_len = max($max_line_len, strlen($line));
    }
    $text_w = $max_line_len * $char_width;
    $text_h = count($lines) * ($font_size * 1.2);
    
    $text_x = $x + ($w - $text_w) / 2;
    $text_y = $y + ($h - $text_h) / 2;
    
    $rect = [
        "type" => "rectangle",
        "id" => $rect_id,
        "x" => $x,
        "y" => $y,
        "width" => $w,
        "height" => $h,
        "angle" => 0,
        "strokeColor" => $stroke_color,
        "backgroundColor" => $bg_color,
        "fillStyle" => $fill_style,
        "strokeWidth" => 2,
        "strokeStyle" => "solid",
        "roughness" => 1,
        "opacity" => 100,
        "groupIds" => [],
        "roundness" => $roundness ? ["type" => 3] : null,
        "seed" => rand(1, 1000000),
        "version" => 1,
        "versionNonce" => rand(1, 1000000),
        "isDeleted" => false,
        "boundElements" => [["type" => "text", "id" => $text_id]],
        "updated" => time() * 1000,
        "link" => null,
        "locked" => false
    ];
    
    $text_elem = [
        "type" => "text",
        "id" => $text_id,
        "x" => $text_x,
        "y" => $text_y,
        "width" => $text_w,
        "height" => $text_h,
        "angle" => 0,
        "strokeColor" => $stroke_color != "#ffffff" ? $stroke_color : "#1e293b",
        "backgroundColor" => "transparent",
        "fillStyle" => "hachure",
        "strokeWidth" => 1,
        "strokeStyle" => "solid",
        "roughness" => 1,
        "opacity" => 100,
        "groupIds" => [],
        "roundness" => null,
        "seed" => rand(1, 1000000),
        "version" => 1,
        "versionNonce" => rand(1, 1000000),
        "isDeleted" => false,
        "boundElements" => null,
        "updated" => time() * 1000,
        "link" => null,
        "locked" => false,
        "text" => $text,
        "fontSize" => $font_size,
        "fontFamily" => 1,
        "textAlign" => "center",
        "verticalAlign" => "middle",
        "containerId" => $rect_id,
        "originalText" => $text
    ];
    
    return [$rect, $text_elem];
}

function create_arrow($start_x, $start_y, $end_x, $end_y, $stroke_color="#000000") {
    $arrow_id = make_id();
    return [
        "type" => "arrow",
        "id" => $arrow_id,
        "x" => $start_x,
        "y" => $start_y,
        "width" => abs($end_x - $start_x),
        "height" => abs($end_y - $start_y),
        "angle" => 0,
        "strokeColor" => $stroke_color,
        "backgroundColor" => "transparent",
        "fillStyle" => "hachure",
        "strokeWidth" => 2,
        "strokeStyle" => "solid",
        "roughness" => 1,
        "opacity" => 100,
        "groupIds" => [],
        "roundness" => ["type" => 2],
        "seed" => rand(1, 1000000),
        "version" => 1,
        "versionNonce" => rand(1, 1000000),
        "isDeleted" => false,
        "boundElements" => null,
        "updated" => time() * 1000,
        "link" => null,
        "locked" => false,
        "points" => [
            [0, 0],
            [$end_x - $start_x, $end_y - $start_y]
        ],
        "lastCommittedPoint" => null,
        "startBinding" => null,
        "endBinding" => null,
        "startArrowhead" => null,
        "endArrowhead" => "arrow"
    ];
}

$elements = [];

// Theme colors
$c_orange = "#ea580c";
$c_amber = "#f59e0b";
$c_slate = "#1e293b";
$c_green = "#16a34a";
$c_purple = "#9333ea";

// 1. Start Node
list($r_start, $t_start) = create_rect(420, 30, 220, 60, "Pengunjung Masuk\nWebsite", $c_slate, "#f8fafc", "solid");
$elements[] = $r_start;
$elements[] = $t_start;

// 2. Homepage Node
list($r_home, $t_home) = create_rect(420, 140, 220, 60, "Halaman Beranda\nYayasan Cendekia", $c_orange, "#ffedd5", "solid");
$elements[] = $r_home;
$elements[] = $t_home;
$elements[] = create_arrow(530, 90, 530, 140, $c_slate);

// 3. Halaman Unit (TK & SD) & Artikel
list($r_tk, $t_tk) = create_rect(140, 260, 200, 60, "Halaman Unit\nTK / PAUD", $c_orange, "#fff7ed", "solid");
list($r_sd, $t_sd) = create_rect(430, 260, 200, 60, "Halaman Unit\nSD IT", $c_amber, "#fef9c3", "solid");
list($r_art, $t_art) = create_rect(720, 260, 200, 60, "Halaman Artikel\n& Informasi", $c_slate, "#f1f5f9", "solid");
$elements[] = $r_tk; $elements[] = $t_tk;
$elements[] = $r_sd; $elements[] = $t_sd;
$elements[] = $r_art; $elements[] = $t_art;

$elements[] = create_arrow(490, 200, 240, 260, $c_orange);
$elements[] = create_arrow(530, 200, 530, 260, $c_amber);
$elements[] = create_arrow(570, 200, 820, 260, $c_slate);

// 4. Cabang Detail TK
list($r_tk_cirebon, $t_tk_cirebon) = create_rect(30, 380, 190, 60, "TKIT TAHFIDZUL QURAN\n(Kota Cirebon)", $c_orange, "#ffedd5", "hachure");
list($r_tk_losari, $t_tk_losari) = create_rect(240, 380, 190, 60, "TKIT TAHFIDZUL QURAN 2\n(Losari - PAUD)", $c_amber, "#fef9c3", "hachure");
$elements[] = $r_tk_cirebon; $elements[] = $t_tk_cirebon;
$elements[] = $r_tk_losari; $elements[] = $t_tk_losari;

$elements[] = create_arrow(210, 320, 125, 380, $c_orange);
$elements[] = create_arrow(270, 320, 335, 380, $c_orange);

// 5. Cabang Detail SD
list($r_sd_cirebon, $t_sd_cirebon) = create_rect(460, 380, 190, 60, "SD IT Sabilul Quran\n(Kota Cirebon)", $c_orange, "#ffedd5", "hachure");
list($r_sd_losari, $t_sd_losari) = create_rect(670, 380, 190, 60, "SD IT Cendekia 2\n(Losari)", $c_amber, "#fef9c3", "hachure");
$elements[] = $r_sd_cirebon; $elements[] = $t_sd_cirebon;
$elements[] = $r_sd_losari; $elements[] = $t_sd_losari;

$elements[] = create_arrow(500, 320, 555, 380, $c_amber);
$elements[] = create_arrow(560, 320, 765, 380, $c_amber);

// 6. Google Forms Mappings
list($r_form_cirebon, $t_form_cirebon) = create_rect(130, 520, 230, 60, "Google Form\nCabang Kota Cirebon", $c_green, "#dcfce7", "solid");
list($r_form_losari, $t_form_losari) = create_rect(530, 520, 230, 60, "Google Form\nCabang Losari", $c_green, "#dcfce7", "solid");
$elements[] = $r_form_cirebon; $elements[] = $t_form_cirebon;
$elements[] = $r_form_losari; $elements[] = $t_form_losari;

$elements[] = create_arrow(125, 440, 215, 520, $c_green);  // TK Cirebon -> Form Cirebon
$elements[] = create_arrow(335, 440, 615, 520, $c_green);  // TK Losari -> Form Losari
$elements[] = create_arrow(555, 440, 275, 520, $c_green);  // SD Cirebon -> Form Cirebon
$elements[] = create_arrow(765, 440, 675, 520, $c_green);  // SD Losari -> Form Losari

// 7. Articles DB and Admin Dashboard
list($r_db, $t_db) = create_rect(1000, 260, 190, 60, "Database JSON\n(articles.json)", $c_slate, "#f1f5f9", "hachure");
list($r_admin, $t_admin) = create_rect(1000, 140, 190, 60, "Dashboard Admin\n(Masa Depan)", $c_purple, "#f3e8ff", "solid");
$elements[] = $r_db; $elements[] = $t_db;
$elements[] = $r_admin; $elements[] = $t_admin;

$elements[] = create_arrow(920, 290, 1000, 290, $c_slate);  // DB -> Artikel page
$elements[] = create_arrow(1095, 200, 1095, 260, $c_purple); // Admin -> DB update

// Excalidraw final wrapper
$excalidraw_data = [
    "type" => "excalidraw",
    "version" => 2,
    "source" => "https://excalidraw.com",
    "elements" => $elements,
    "appState" => [
        "viewBackgroundColor" => "#ffffff",
        "gridSize" => null
    ],
    "files" => new stdClass()
];

file_put_contents("sketsa_flow.excalidraw", json_encode($excalidraw_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo "Successfully generated sketsa_flow.excalidraw\n";
