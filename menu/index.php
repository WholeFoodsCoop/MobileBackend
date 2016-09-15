<?php
/**
  Pull information about the deli menu
  directly out of wordpress tables
*/

if (!class_exists('PhpAutoLoader')) {
    require(dirname(__FILE__) . '/../../vendor-code/PhpAutoLoader/PhpAutoLoader.php');
}

$ts = strtotime('Monday this week');
$data = array(
    'monday' => array(
        'date' => date('l, F jS', $ts),
        'items' => array(
            '7:30 - 10:45AM',
            'Breakfast',
            '11:00AM - 8:30PM',
        ),
    ),
    'tuesday' => array(
        'date' => date('l, F jS', mktime(0,0,0,date('n',$ts), date('j',$ts)+1, date('Y', $ts))),
        'items' => array(
            '7:30 - 10:45AM',
            'Breakfast',
            '11:00AM - 8:30PM',
        ),
    ),
    'wednesday' => array(
        'date' => date('l, F jS', mktime(0,0,0,date('n',$ts), date('j',$ts)+2, date('Y', $ts))),
        'items' => array(
            '7:30 - 10:45AM',
            'Breakfast',
            '11:00AM - 8:30PM',
        ),
    ),
    'thursday' => array(
        'date' => date('l, F jS', mktime(0,0,0,date('n',$ts), date('j',$ts)+3, date('Y', $ts))),
        'items' => array(
            '7:30 - 10:45AM',
            'Breakfast',
            '11:00AM - 8:30PM',
        ),
    ),
    'friday' => array(
        'date' => date('l, F jS', mktime(0,0,0,date('n',$ts), date('j',$ts)+4, date('Y', $ts))),
        'items' => array(
            '7:30 - 10:45AM',
            'Breakfast',
            '11:00AM - 8:30PM',
        ),
    ),
    'saturday' => array(
        'date' => date('l, F jS', mktime(0,0,0,date('n',$ts), date('j',$ts)+5, date('Y', $ts))),
        'items' => array(
            '7:30 - 10:45AM',
            'Breakfast',
            '11:00AM - 8:30PM',
            'Chef\'s Choice',
        ),
    ),
    'sunday' => array(
        'date' => date('l, F jS', mktime(0,0,0,date('n',$ts), date('j',$ts)+6, date('Y', $ts))),
        'items' => array(
            '7:30AM - 4:00PM',
            'Brunch',
        ),
    ),
);

$dbc = Database::pDataConnect();
$title = date('mdy', strtotime('Monday this week'));

$prep = $dbc->prepare('SELECT ID FROM whfoco_posts WHERE post_title=?');
$res = $dbc->execute($prep, array($title));
if ($dbc->numRows($res)) {
    $row = $dbc->fetchRow($res);
    $prep = $dbc->prepare('SELECT meta_key, meta_value FROM whfoco_postmeta 
        WHERE post_id=? AND meta_key LIKE \'%_dinner_%_item%\'
        ORDER BY meta_id');
    $res = $dbc->execute($prep, $row['ID']);
    while ($w = $dbc->fetchRow($res)) {
        if (substr($w['meta_key'], 0, 1) == '_') continue;

        if (strstr($w['meta_key'], 'monday')) {
            $data['monday']['items'][] = $w['meta_value'];
        }
        if (strstr($w['meta_key'], 'tuesday')) {
            $data['tuesday']['items'][] = $w['meta_value'];
        }
        if (strstr($w['meta_key'], 'wednesday')) {
            $data['wednesday']['items'][] = $w['meta_value'];
        }
        if (strstr($w['meta_key'], 'thursday')) {
            $data['thursday']['items'][] = $w['meta_value'];
        }
        if (strstr($w['meta_key'], 'friday')) {
            $data['friday']['items'][] = $w['meta_value'];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($data);
