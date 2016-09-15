<?php
/**
  Downloads PDF files from WFC website and converts
  them to a series of images. Images are store in "img"
  directory. Next level is a MD5 hash of the PDF URL.
*/

$url = 'http://wholefoods.coop/specials';
$cache = __DIR__ . '/cache.json';

if (!file_exists($cache) || (time() - filemtime($cache) > 3600)) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); 
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    $page = curl_exec($curl);
    curl_close($curl);
    preg_match_all('/href="(.+?\.pdf)"/i', $page, $matches);
    $pdfs = array();
    foreach (array_unique($matches[1]) as $pdf) {
        $hash = md5($pdf);
        $dir = __DIR__ . '/img/' . $hash;
        if (!is_dir($dir)) {
            mkdir($dir);
            $curl = curl_init($pdf);
            curl_setopt($curl, CURLOPT_AUTOREFERER, true); 
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            $fp = fopen($dir . '/temp.pdf', 'w');
            curl_setopt($curl, CURLOPT_FILE, $fp);
            curl_exec($curl);
            curl_close($curl);
            $t1 = microtime(true);
            $img = new Imagick($dir . '/temp.pdf');
            $t2 = microtime(true);
            $count = $img->getNumberImages();
            $t3 = microtime(true);
            for ($i=0; $i<$count; $i++) {
                $t4 = microtime(true);
                $img->setIteratorIndex($i);
                $img->setImageFormat('jpeg');
                $img->setImageCompressionQuality(100);
                $img->setResolution(300,300);
                $file = $dir . '/' . str_pad($i, 2, '0', STR_PAD_LEFT) . '.jpg';
                $img->writeImage($file);
                $t5 = microtime(true);
            }
            unlink($dir . '/temp.pdf');
        }
        $images = scandir($dir);
        $images = array_filter($images, function($i) { return substr($i, -4) === '.jpg'; });
        $urls = array_map(function($i) use ($hash) { return 'http://store.wholefoods.coop/api/flyers/img/' . $hash . '/' . basename($i); }, $images);
        $pdfs[] = array_values($urls);
    }
    file_put_contents($cache, json_encode($pdfs));
}

header('Content-type: application/json');
echo file_get_contents($cache);

