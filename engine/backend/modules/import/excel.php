<?php
class Excel
{
    private $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?\>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:html=\"http://www.w3.org/TR/REC-html40\">";

    private $footer = "</Workbook>";

    private $lines = array ();

    private $worksheet_title = "Table1";
    
    public function encodeRow($array)
    {
    	$cells = "";

        // foreach key -> write value into cells
        foreach ($array as $k => $v):
        	$v = htmlspecialchars($v);
			$v = str_replace(array("\r\n", "\r", "\n"), '&#10;', $v);
            $cells .= "<Cell><Data ss:Type=\"String\">" . utf8_encode($v) . "</Data></Cell>\n"; 

        endforeach;

        // transform $cells content into one row
        return "<Row>\n" . $cells . "</Row>\n";
    }

    public function addRow ($array)
    {
        $this->lines[] = $this->encodeRow($array);
    }

    public function addArray ($array)
    {

        // run through the array and add them into rows
        foreach ($array as $k => $v):
            $this->addRow ($v);
        endforeach;

    }

    public function setTitle ($title)
    {
        // strip out special chars first
        $title = preg_replace ("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);

        // now cut it to the allowed length
        $title = substr ($title, 0, 31);

        // set title
        $this->worksheet_title = $title;
    }
    
    public function generateXML ($filename)
    {
        // deliver header (as recommended in php manual)
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");

        // print out document to the browser
        // need to use stripslashes for the damn ">"
        echo $this->getHeader();
        echo implode ("\n", $this->lines);
        echo $this->getFooter();
    }
    
    public function getAll()
    {
    	$r = $this->getHeader();
        $r .= implode ('', $this->lines);
        $r .= $this->getFooter();
        return $r;
    }
    
    public function getHeader ()
    {
    	$header = stripslashes ($this->header);
        $header .= "\n<Worksheet ss:Name=\"" . $this->worksheet_title . "\">\n<Table>\n";
        $header .= "<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
        return $header;
    }
    
    public function getFooter ()
    {
        $footer = "</Table>\n</Worksheet>\n";
        $footer .= $this->footer;
        return $footer;
    }
}