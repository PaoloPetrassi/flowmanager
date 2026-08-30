@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
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
    <Worksheet ss:Name="{{ str($sheetName)->limit(28, '') }}">
        <Table>
            <Row>
                @foreach ($columns as $label)
                    <Cell ss:StyleID="Header"><Data ss:Type="String">{{ $label }}</Data></Cell>
                @endforeach
            </Row>
            @foreach ($rows as $row)
                <Row>
                    @foreach (array_keys($columns) as $key)
                        <Cell><Data ss:Type="String">{{ (string) ($row[$key] ?? '') }}</Data></Cell>
                    @endforeach
                </Row>
            @endforeach
        </Table>
    </Worksheet>
</Workbook>
