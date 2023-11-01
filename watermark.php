<form method="post" enctype="multipart/form-data">
    <input type="file" name="file" />
    <input type="submit" name="submit" value="Upload" />
</form>

<?php
//load library
include('UserInformation.php');
require_once('PDFTK/vendor/autoload.php');
use mikehaertl\pdftk\Pdf;
use setasign\Fpdi\Fpdi;

require_once('vendor/autoload.php');
require('rotation.php');

class PDF1 extends PDF_Rotate
{
    function Header()
    {
        //put watermark
        $this->setFont('ARIAL', 'B', 34);
        $this->setTextColor(255, 192, 203);
        date_default_timezone_set("Asia/Calcutta");
        $ip_add = UserInfo::get_ip();
        $date = date("Y.m.d");
        $time = date("h:i:sa");
        $this->RotateText(35, 190, $ip_add . "-" . $date . "-" . $time, 45);
    }

    function RotateText($x, $y, $txt, $angle)
    {
        //text rotated at its angle
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

}

// echo '<pre>';
// print_r($_FILES);
if (isset($_POST['submit']) and $_FILES['file']['type'] == 'application/pdf') {
    $pdf_name = $_FILES['file']['name'];
    move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['name']);

    //$file = 'CHATAPP.pdf';
    $pdf1 = new Fpdi();
    // //$pdf = new PDF();
    if (file_exists("./" . $pdf_name)) {
        $pagecount = $pdf1->setSourceFile($pdf_name);
    } else {
        die('Source PDF not found!');
    }

    $pdf = new PDF1();
    $pdf->SetFont('Arial', '', 12);
    for ($i = 0; $i < $pagecount; $i++) {
        $pdf->AddPage();
    }
    //to generate output pdf
    //$des = "c://xampp//htdocs//Add_Watermark//water.pdf";
    $pdf->Output('F', 'c://xampp//htdocs//Add_Watermark//watermark.pdf');


    //$filename = "final.pdf";
    $pdf2 = new Pdf('c://xampp//htdocs//Add_Watermark//watermark.pdf', [
        //'command' => '/some/other/path/to/pdftk',
        // or on most Windows systems:
        'command' => 'C:\Program Files (x86)\PDFtk\bin\pdftk.exe',
        'useExec' => true, // May help on Windows systems if execution fails
    ]);
    $result = $pdf2->multiStamp('c://xampp//htdocs//Add_Watermark//' . $pdf_name)
        ->saveAs('./' . $pdf_name);
    //echo ($result);
    if ($result === false) {
        //echo ('hii');
        $error = $pdf2->getError();
        echo ($error);
    }
    echo "<br>";
    echo "OS: " . UserInfo::get_os();
    echo "<BR>";
    echo "Browser: " . UserInfo::get_browser();
    echo "<br>";
    echo "Device: " . UserInfo::get_device();
    @unlink('watermark.pdf');


} else {
    echo "error file type";
    unset($_POST['submit']);
}
//echo shell_exec('cd c:\xampp\htdocs\Add_Watermark & pdftk water.pdf multistamp CHATAPP.pdf output merg.pdf');
?>

<!-- <script type="text/javascript">
    const { google } = require('googleapis');
    const clint_id = "1027429316569-0uo6e88kqmp75uvr7op4594cncvlk3q5.apps.googleusercontent.com";
    const client_sec = "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";
    const redirect_uri = "https://developers.google.com/oauthplayground";
    const refersh_token = "1//045ZO1BYfBLu0CgYIARAAGAQSNwF-L9IrrLt20pDOPRogYjORicx4ipQb5m008ztdhK8mLNxa4ADWCh8SBj3mtMaZdBUehvt2DJ8";

    const oauth2Client = new google.auth.OAuth2(
        clint_id,
        client_sec,
        redirect_uri
    )

    oauth2Client.setCredentials({ refresh_token: refersh_token })

    const drive = google.drive(
        {
            version: 'v3',
            auth: oauth2Client

        }
    )

</script> -->