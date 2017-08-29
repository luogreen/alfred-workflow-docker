<?php
$input = trim($argv[1]);
$output = array();
if($input == "op"){
        $items[]= array(
                'title' => 'remove containers',
                'arg' => 'all rm',
                'subtitle' => 'remove all inactive containers',
                'mods' => array('shift' => array('subtitle' => 'remove all containers', 'arg' => 'all rm -f')),
        );
        goto export;
}

exec("/usr/local/bin/docker ps --format \"{{.Names}}##{{.Image}}##{{.Status}}##{{.Ports}}\" ".$input, $output);
//解析docker ps输出
$data = array();
for($i=0;$i<count($output);$i++) {
        $info = array_values(array_filter(explode("##", $output[$i])));
        $data[] = array(
                'name' => trim($info[0]),
                'image' => trim($info[1]),
                'status' => trim($info[2]),
                'ports' => trim($info[3]),
        );
}

//格式化成alfred列表显示格式
$items = array();
foreach ($data as $key => $value) {
        $ports = str_replace('->', ':', str_replace('0.0.0.0:', '', $value['ports']));
        $item = array(
                'title' => $value['name'],
                'arg' => $value['name'],
                'subtitle' => $value['status'].($ports ? "  ports:{$ports}" : '')."  image:{$value['image']}",
                'autocomplete' => $value['name'],
        );

        list($status) = explode(" ", $value['status']);
        switch ($status) {
                case 'Up':
                        $item['mods'] = array(
                                'cmd' => array('subtitle' => 'login the container'),
                                'ctrl' => array('subtitle' => 'stop the container', 'arg' => "stop {$value['name']}")
                        );
                        break;
                case 'Exited':
                        $item['mods'] = array(
                                'ctrl' => array('subtitle' => 'start the container', 'arg' => "start {$value['name']}")
                        );
                        break;
                default:
                        $item['mods'] = array(
                                'ctrl' => array('subtitle' => 'remove the container', 'arg' => "rm {$value['name']}")
                        );
                        break;
        }
        $item['mods']['shift'] = array('subtitle' => 'force remove the container', 'arg' => "rm -f {$value['name']}");
        $items[] = $item;
}

export:
$list = array(
        'items' => $items
);
echo json_encode($list);
?>
