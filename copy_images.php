<?php
$source_dir = 'C:/Users/user/.gemini/antigravity/brain/f7834a87-6a83-4be2-8fbd-e99207451a3b/';
$dest_dir = 'assets/images/products/';

if (!is_dir($dest_dir)) {
    mkdir($dest_dir, 0777, true);
}

$files = [
    'broilers_1_1770127570390.png' => 'broilers.png',
    'layers_1_1770127589365.png'   => 'layers.png',
    'eggs_1_1770127608496.png'     => 'eggs.png',
    'processed_1_1770127623717.png'=> 'processed.png',
    'feed_1_1770127641871.png'     => 'feed.png'
];

foreach ($files as $src => $dest) {
    $source_path = $source_dir . $src;
    $dest_path = $dest_dir . $dest;
    
    if (file_exists($source_path)) {
        if (copy($source_path, $dest_path)) {
            echo "Copied $src to $dest<br>";
        } else {
            echo "Failed to copy $src<br>";
            echo "Error: " . print_r(error_get_last(), true) . "<br>";
        }
    } else {
        echo "Source file not found: $source_path<br>";
    }
}
?>
