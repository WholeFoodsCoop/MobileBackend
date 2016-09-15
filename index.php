<?php
/**
  Web service endpoint to search for item info
*/
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
if (!class_exists('PhpAutoLoader')) {
    require(dirname(__FILE__) . '/../vendor-code/PhpAutoLoader/PhpAutoLoader.php');
}

$example = array(
    'upc' => '0000000004011',
    'check-digits' => false,
);

header('Content-type: application/json');
if (!isset($input['upc'])) {
    echo json_encode($example);
} else {
    if (isset($input['check-digits']) && $input['check-digits']) {
        $input['upc'] = substr($input['upc'], 0, strlen($input['upc'])-1);
    }
    $upc = str_pad($input['upc'], 13, '0', STR_PAD_LEFT);
    $out = array();
    $dbc = Database::pDataConnect();
    $dateCutoff = date('Y-m-d 00:00:00', strtotime('60 days ago'));
    $query = '
        SELECT p.upc,
            NULL AS brand1,
            u.manufacturer AS brand2,
            p.description AS desc1,
            u.description AS desc2,
            p.size AS size1,
            u.sizing AS size2
        FROM PLUProducts AS p
            LEFT JOIN prodUser AS u ON p.upc=u.upc
        WHERE p.inUse=1 
            AND p.last_sold >= ?
            AND (p.upc=?
                OR p.description LIKE ?
                OR p.brand LIKE ?
                OR u.manufacturer LIKE ?
                OR u.description LIKE ?)
        ORDER BY p.last_sold DESC';
    $args = array(
        $dateCutoff,
        $upc,
        '%' . $input['upc'] . '%',
        '%' . $input['upc'] . '%',
        '%' . $input['upc'] . '%',
        '%' . $input['upc'] . '%',
    );
    $prep = $dbc->prepare($query);
    $res = $dbc->execute($prep, $args);

    $ret = array();
    while ($w = $dbc->fetchRow($res)) {
        $out['upc'] = $w['upc'];
        $out['brand'] = $w['brand2'] ? $w['brand2'] : $w['brand1'];
        $out['description'] = $w['desc2'] ? $w['desc2'] : $w['desc1'];
        $out['size'] = $w['size2'] ? $w['size2'] : $w['size1'];
        $ret[] = $out;
    }

    echo json_encode($ret);
}
