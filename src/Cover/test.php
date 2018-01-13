<?php
error_reporting(E_ALL);

include('./Interface.php');
include('./Abstract/Simple.php');
include('./Abstract/Array.php');
include('./Array.php');

class NEW_TYPE extends Krugozor_Cover_Array {}

echo "> Content var \$array type of Cover_Array:\n";
$array = new NEW_TYPE( array('foo', 12345, 'element' => array('key' => 'value', 'key2' => 'value2')) );
print_r($array);
echo "\n\n";

echo "> echo \$array->item(0);\n";
echo $array->item(0); // foo
echo "\n\n";

echo "> echo \$array->element->key;\n";
echo $array->element->key; // value
echo "\n\n";

echo "> echo \$array['element']['key'];\n";
echo $array['element']['key']; // value
echo "\n\n";

echo "> echo \$array->element->count();\n";
echo $array->element->count(); // 2
echo "\n\n";

echo "> echo \$array->element->append('Hellow, PHP!')->item(0);\n";
echo $array->element->append('Hellow, PHP!')->item(0); // Hellow, PHP!
echo "\n\n";

echo "> echo \$array->element->count();\n";
echo $array->element->count(); // 3
echo "\n\n";

echo "> print_r(\$array->getDataAsArray());\n";
print_r($array->getDataAsArray());
echo "\n\n";

echo "> foreach by \$array->element:\n";
foreach ($array->element as $key => $value) {
    echo "$key => $value \n";
}
echo "\n\n";

echo "> \$array->is_array = array(1, 2, 3);\n";
echo "> print_r(\$array->is_array);\n";
$array->is_array = array(1, 2, 3);
print_r($array->is_array);
echo "\n";

echo "> var_dump(\$array->non_exists_prop);\n";
var_dump($array->non_exists_prop);
echo "\n";

echo "> print_r(\$array['non_exists_prop']);\n";
var_dump($array['non_exists_prop']);
echo "\n\n";

echo "> \$array['non_exists_prop']['non_exists_prop']['property'] = true;\n";
echo "> print_r(\$array['non_exists_prop']);\n";
$array['non_exists_prop']['non_exists_prop']['property'] = true;
print_r($array['non_exists_prop']);

// так нельзя.
// $array->non_exists_prop_2->non_exists_prop_2->property_2 = true;

echo "> echo \$array['non_exists_prop']['non_exists_prop'];\n";
echo $array['non_exists_prop']['non_exists_prop']; // string ''
echo "\n\n";