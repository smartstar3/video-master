namespace MotionArray\Models\StaticData;

class <?= $className ?> extends StaticDBData
{
<?php foreach($rows as $row): ?>
<?php foreach($row['const'] as $constKey => $constValue):
        $itemValue = $row['item'][$constKey];
        if(is_string($itemValue)){
            $itemValue = "'{$itemValue}'";
        }
        ?>
    public const <?= $constValue ?> = <?= $itemValue ?>;
<?php endforeach; ?>

<?php endforeach; ?>
    protected $modelClass = \<?= $modelClass ?>::class;

    protected $data = [
<?php foreach($rows as $row):
$item = $row['item'];
?>
        [
<?php foreach($item as $key => $value):
if ($value === null) {
    $value = 'null';
}
else if(is_int($value) || is_float($value)) {
    $value = $value;
} else {
    $value = "'{$value}'";
}
$const = $row['const'][$key] ?? false;
if($const){
    $value = 'self::' . $const;
}
?>
            '<?= $key ?>' => <?= $value ?>,
<?php endforeach; ?>
        ],
<?php endforeach; ?>
    ];
}
