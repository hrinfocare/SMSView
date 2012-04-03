<?php

class satisfaction
{

    function render( &$data, $type='satisfaction')
    {
        require_once("./pChart/class/pData.class.php");
        require_once("./pChart/class/pDraw.class.php");
        require_once("./pChart/class/pImage.class.php");
        require_once("./features/utils.php");
        $temp           = 'temp/';
        $datastring     = $data['table.satisfaction.data'];
        $schoolname     = $data['schoolnaam'];
        $column_count   = 0;
        
        $paramsTextTitles = array(
            'b' => 'double',
            'font' => 'Century Gothic',
            'cell_color' => '00A4E4',
            'sz' => 9.5
         );        
        
        $paramsTextTableHeader = array(
            'color' => 'FFFFFF',
            'font' => 'Century Gothic',
            'b' => 'double',
            'cell_color' => '00A4E4',         
            'sz' => 9.5
        );        

        $paramsTextTableTitle = array(
            'color' => '00A4E4',
            'font' => 'Century Gothic',
            'b' => 'double',
            'cell_color' => 'DAEEF3',//lightbleu
            'sz' => 9.5
        );        
        
        $paramsTextTable = array(
            'color' => '00A4E4',
            'font' => 'Century Gothic',
            'border_color'=>'00A4E4',
            'sz' => 9.5,
            'jc' => 'center'
        );        
        
        $paramsTextTableTitleReference = array(
            'color' => 'F78E1E',
            'font' => 'Century Gothic',
            'b' => 'double',            
            'cell_color' => 'FDE9D9',//lightorange
            'sz' => 9.5
        );        
        
        $paramsTextTableReference = array(
            'color' => 'F78E1E',
            'sz' => 9.5,
            'font' => 'Century Gothic',
            'jc' => 'center'
        );        
        
        $paramsTextTableHeaderReference = array(
            'color' => 'FFFFFF',
            'b' => 'double',
            'font' => 'Century Gothic',
            'cell_color' => 'F78E1E',         
            'sz' => 9.5
        );        

        $widthTableCols = array(
            3200,
            700,
            700
        );

        $paramsTable = array(
            'border' => 'single',
            'border_sz' => 10,
            'border_color'=>'00A4E4',
            'size_col' => $widthTableCols,
            'textWrap'=>0,
        );

        $paramsTableEmpty = array(
            'border' => 'none',
            'size_col' => 100,
        );

        $paramsTableReference = array(
            'border' => 'single',
            'border_sz' => 10,
            'border_color'=>'F78E1E',
            'size_col' => 700,
            'textWrap'=>0,
        );


        
        
        
        //konqord JSON is false becuse escape character on '
        $datastring     = str_replace('\\\'', '\'', $datastring);
        $satisfaction_data  = json_decode($datastring)->{$type};
        
        //add graphic to docx
        $satisfaction_docx = new CreateDocx();
        
        $satisfaction_table = array();
        $satisfaction_table_reference = array();
        for ($i=0 ; $i < 10 ; $i++){
            $count = 0;
 //           $satisfaction_table[$i][$count++] = $i; //number, will be changed after sort
            $paramsTextTable['text'] = $satisfaction_data->{'alle_scholen'}[$i][1];
            $text = $satisfaction_docx->addElement('addText', array($paramsTextTable));
            $satisfaction_table[$i][$count++] = $text; //title
            foreach ($satisfaction_data as $key => $satisfaction_column){
                if ($key == 'alle_scholen'){
                    $paramsTextTableReference['text'] = '';
                    $text = $satisfaction_docx->addElement('addText', array());
                    $text->{'border'} = $paramsTableEmpty;
                    $satisfaction_table[$i][$count++] = $text;

                    $paramsTextTableReference['text'] = Scale10($satisfaction_column[$i][2], 4);
                    $text = $satisfaction_docx->addElement('addText', array($paramsTextTableReference));
                    $text->{'border'} = $paramsTableReference;
                    $satisfaction_table[$i][$count++] = $text;
                } else {
                    $paramsTextTable['text'] = Scale10($satisfaction_column[$i][2], 4);
                    $text = $satisfaction_docx->addElement('addText', array($paramsTextTable));
                    $text->{'border'} = $paramsTable;
                    $satisfaction_table[$i][$count++] = $text;
                }
            }
            
        }
        
        $satisfaction_titles = array();
        $text = $satisfaction_docx->addElement('addText', array($paramsTextTableHeader));
        $satisfaction_titles[0][] = $text;
        
        foreach ($satisfaction_data as $key => $satisfaction_column){
                $column_count++;
                if ($key == 'alle_scholen'){
                    $paramsTextTableReference['text'] = '';
                    $text = $satisfaction_docx->addElement('addText', array());
                    $text->{'border'} = $paramsTableEmpty;
                    $satisfaction_titles[0][] = $text;
                    $paramsTextTableTitleReference['text'] = 'Alle scholen';
                    $text = $satisfaction_docx->addElement('addText', array($paramsTextTableTitleReference));
                    $text->{'border'} = $paramsTableReference;
                    $satisfaction_titles[0][] = $text;
                } else {
                    if ($key == 'peiling'){
                        $paramsTextTableTitle['text'] = 'Deze peiling';
                    } elseif ($key == 'vorige_peiling'){
                        $paramsTextTableTitle['text'] = 'Vorige peiling';
                    } else {
                        $paramsTextTableTitle['text'] = $key;
                    }
                    $text = $satisfaction_docx->addElement('addText', array($paramsTextTableTitle));
                    $text->{'border'} = $paramsTable;
                    $satisfaction_titles[0][] = $text;
                }
        }
        
        usort($satisfaction_table, 'cmp_peiling');
        
        
        
        //set right number
        $count = 1;
        for ($i = 0 ; $i < count($satisfaction_table) ; $i++){
            //$satisfaction_table[$i][0] = $count++.'.';
            $satisfaction_table[$i][0]->{'_embeddedText'}[0]["text"] = $count++.'. '.$satisfaction_table[$i][0]->{'_embeddedText'}[0]["text"] ;
            //now do the formatting of the number (should be done AFTER sort)
            for ($j=0 ; $j <= $column_count ; $j++){
                $value = $satisfaction_table[$i][$j+1]->{'_embeddedText'}[0]["text"];
                if (isset($value)){
                    $satisfaction_table[$i][$j+1]->{'_embeddedText'}[0]["text"] = sprintf("%01.1f", $value);
                }
            }
        }
        
        $paramsText = array(
            'b' => 'single',
            'font' => 'Arial'
        );        
        
        $widthTableColsHeader = array(
            4600,
        );

        $paramsTableHeader = array(
            'border' => 'single',
            'border_sz' => 10,
            'border_color'=>'00A4E4',
            'size_col' => $widthTableColsHeader,
            'textWrap'=>0
            
        );

        $satisfaction_header = array();
        $paramsTextTableHeader['text'] = 'Onze school';
        $text = $satisfaction_docx->addElement('addText', array($paramsTextTableHeader));
        $text->{'border'} = $paramsTable;
        $satisfaction_header[0][] = $text;
        $paramsTextTableHeader['text'] = '';
        $text = $satisfaction_docx->addElement('addText', array($paramsTextTableHeader));
        $text->{'border'} = $paramsTable;
        $satisfaction_header[0][] = $text;
        $paramsTextTableHeader['text'] = '';
        $text = $satisfaction_docx->addElement('addText', array($paramsTextTableHeader));
        $text->{'border'} = $paramsTable;
        $satisfaction_header[0][] = $text;
        $paramsTextTableHeader['text'] = '';
        $text = $satisfaction_docx->addElement('addText', array());
        $text->{'border'} = $paramsTableEmpty;
        $satisfaction_header[0][] = $text;
        
        
        
        $paramsTextTableHeaderReference['text'] = 'Referentie';
        $text = $satisfaction_docx->addElement('addText', array($paramsTextTableHeaderReference));
        $text->{'border'} = $paramsTableReference;
        $satisfaction_header[0][] = $text;
        
        
        //$satisfaction_docx->addText($type, $paramsText);
        $table1 = $satisfaction_docx->addTable($satisfaction_header);
        $table2 = $satisfaction_docx->addTable($satisfaction_titles);
        $table3 = $satisfaction_docx->addTable($satisfaction_table);


        $satisfaction_docx->createDocx($temp.$type);
        unset($satisfaction_docx);
        return $temp.$type.'.docx';
        
    }
    

}
        function cmp_peiling($a, $b)
        {
            if ($a[1]->{'_embeddedText'}[0]["text"] == $b[1]->{'_embeddedText'}[0]["text"]) {
                return 0;
            }
            return ($a[1]->{'_embeddedText'}[0]["text"] < $b[1]->{'_embeddedText'}[0]["text"]) ? 1 : -1;
        }