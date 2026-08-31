<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:x="urn:schemas-microsoft-com:office:excel"
          xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
    <Styles>
        <Style ss:ID="Header">
            <Font ss:Bold="1" />
            <Interior ss:Color="#E9EEF7" ss:Pattern="Solid" />
        </Style>
    </Styles>
    <Worksheet ss:Name="<?php echo e(str($sheetName)->limit(28, '')); ?>">
        <Table>
            <Row>
                <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <Cell ss:StyleID="Header"><Data ss:Type="String"><?php echo e($label); ?></Data></Cell>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </Row>
            <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <Row>
                    <?php $__currentLoopData = array_keys($columns); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <Cell><Data ss:Type="String"><?php echo e((string) ($row[$key] ?? '')); ?></Data></Cell>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </Row>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </Table>
    </Worksheet>
</Workbook>
<?php /**PATH C:\Projects\flowmanager\resources\views/reports/excel.blade.php ENDPATH**/ ?>