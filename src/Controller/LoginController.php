<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Stiri;
use App\Repository\AccountRepository;
use App\Repository\StiriRepository;
use http\Client\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Loader\Configurator;

require_once('..\..\public\phpmailer\mail_cod.php');

class LoginController extends AbstractController
{
    private AccountRepository $accountRepository;
    private StiriRepository $stiriRepository;

    public function __construct(AccountRepository $accountRepository, StiriRepository $stiriRepository){
        $this->accountRepository = $accountRepository;
        $this->stiriRepository = $stiriRepository;

    }
    #[Route('/login', name: 'app_login')]
    public function login(AccountRepository $accountRepository): Response
    {
        try {
            session_start();
        }catch(\Exception $exception){}
        return $this->render("login/login.html.twig");
    }

    #[Route('/signup', name: 'app_signup')]
    public function signup(): Response
    {
        try {
            session_start();
        }catch(\Exception $exception){}
        $mesaj = "";
        if(isset($_GET["error"])) {
            if ($_GET["error"] == 1) {
                $mesaj = "Verificati parola reintrodusa!";
            }else if($_GET["error"] == 2){
                $mesaj = "Exista deja un cont cu acest mail";
            }
        }
        return $this->render("login/signup.html.twig",[
            "mesaj" => $mesaj
        ]);
    }

    #[Route('/account/check', name: 'app_check_account')]
    public function accountCheck(AccountRepository $accountRepository): Response
    {
        try {
            session_start();
        }catch(\Exception $exception){}
        if(isset($_POST["Login"])){
            var_dump($_POST);
            $cont = $accountRepository->findOneBy(array("mail"=>$_POST["typeEmailX"],"parola"=>$_POST["typePasswordX"]));
            if(isset($cont)){
                $_SESSION["loggedIn"] = 1;
                $_SESSION["nume"] = $cont->getName();
                $_SESSION["rol"] = $cont->getRol();
                return $this->redirectToRoute('app_main');
            }
        }
        if(isset($_POST["Signup"])){
            if($_POST["typePasswordX"] != $_POST["typeRePasswordX"]){
                return $this->redirectToRoute('app_signup',[
                        "error" => 1
                        ]);
            }
            if($accountRepository->checkMail($_POST["typeEmailX"]) == true){
                return $this->redirectToRoute('app_signup',[
                        "error" => 2
                        ]);
            }
            $check = $this->accountRepository->findOneBy(array("mail"=>$_POST["typeEmailX"]));
            if(!isset($check)) {
                $newAccount = new Account();
                $newAccount->setName($_POST["typeNameX"]);
                $newAccount->setMail($_POST["typeEmailX"]);
                $newAccount->setParola($_POST["typePasswordX"]);
                $newAccount->setRol(0);
                $accountRepository->save($newAccount);
                sendEmail($_POST["typeEmailX"], $_POST["typeNameX"], $_POST["typePasswordX"]);
            }
        }
        return $this->redirectToRoute("app_login");
    }
    #[Route('/logout', name: 'app_logout')]
    public function logout(): Response
    {
        try {
            session_start();
        }catch(\Exception $exception){}
        unset($_SESSION);
        return $this->redirectToRoute("app_main");
    }
    #[Route('/', name: 'app_main')]
    public function main(StiriRepository $stiriRepository): Response
    {
        try {
            session_start();
        }catch(\Exception $exception){}
        $stiri = $stiriRepository->findAll();
        if(isset($_SESSION["rol"]) and isset($_SESSION["nume"])) {
            return $this->render("main/main.html.twig", [
                "nume" => $_SESSION["nume"],
                "rol" => $_SESSION["rol"],
                "stiri" => $stiri
            ]);
        }else{
            return $this->render("main/main.html.twig",[
                "nume" => NULL,
                "stiri" => $stiri
            ]);
        }
    }
    #[Route('/creeaza', name: 'app_article')]
    public function makeArticle(): Response
    {
        try {
            session_start();
        }catch(\Exception $exception){}
        var_dump($_SESSION);
        if(isset($_SESSION["rol"]) and ($_SESSION["rol"] == 1 or $_SESSION["rol"] == 2)){
            return $this->render('main/article.html.twig'
                , [
                    "nume" => $_SESSION["nume"],
                    "rol" => $_SESSION["rol"]
                ]);
        }else{
            return $this->redirectToRoute("app_main");
        }
    }
    #[Route('/creeaza/upload', name: 'app_article_upload')]
    public function uploadArticle(StiriRepository $stiriRepository): Response
    {
        try {
            session_start();
        }catch(\Exception $exception){}
        if(isset($_SESSION["rol"]) and ($_SESSION["rol"] == 1 or $_SESSION["rol"] == 2)){
            $articol = new Stiri();
            $articol -> setTitlu($_POST["titluArt"]);
            $articol -> setRezumat($_POST["rezumatArt"]);
            $articol -> setText($_POST["textArt"]);
            $articol -> setAutor($_SESSION["nume"]);
            $articol -> setPoza1($_POST["poza1Art"]);
            $articol -> setPoza2($_POST["poza2Art"]);
            $articol -> setPoza3($_POST["poza3Art"]);
            $stiriRepository -> save($articol);
            return $this->redirectToRoute("app_main");
        }else{
            return $this->redirectToRoute("app_main");
        }
    }
    #[Route('/vizionare/{id}', name: 'app_watch')]
    public function seeArticle(StiriRepository $stiriRepository,\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $stire = $stiriRepository -> find($request->get("id"));
        return $this->render("main/stire.html.twig",[
            "stire" => $stire
        ]);
    }
    #[Route('/update/{id}', name: 'app_article_update')]
    public function updateArticle(StiriRepository $stiriRepository,\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $stire = $stiriRepository -> find($request->get("id"));
        return $this->render("main/article.html.twig",[
            "stire" => $stire
        ]);
    }
    #[Route('/delete/{id}', name: 'app_article_delete')]
    public function deleteArticle(StiriRepository $stiriRepository,\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $stiriRepository->remove($stiriRepository->find($request->get("id")));
        return $this->redirectToRoute('app_main');
    }
    #[Route('/clasament', name: 'app_clasament')]
    public function teamRanks(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://baschet.ro/liga-nationala-de-baschet-masculin/clasament',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: XSRF-TOKEN=eyJpdiI6Ing0SjBKQ0cwQmxGVXh2VjcrU1orNWc9PSIsInZhbHVlIjoiQ1NGYWdhVzRjbEtkU0kxMVdlRm90MTdBK0ZNWUVaWGZHYTdQeXJmeitaWlVNNDN0Q1JRU0IzdThDRlZGV1JrViIsIm1hYyI6IjQxYjcyMTM1YjE1NGE4ODIzYzM2ZjEyZDA5NTJjYWMwZWE3MWRhZDAzMzUxM2I0YTRiOTAzN2ZlZWQ4NGIzZWUifQ%3D%3D; baschetro_session=eyJpdiI6IlYzdlJ5VzBoYmFNQ0g5TTBlZjdzQlE9PSIsInZhbHVlIjoibWVxYU1hQVlDUkxHc2RLUVlNUzROWXZ1VUdCZVh0QXBtSnVPdzVcL0hBNWU5OUVsQWxZTHdYQllqdGkrM1NkemQiLCJtYWMiOiI3ZmVhYzRmNTg0MDE5NjhlZmIyMTZiMjIyMDdkODUwNGFiM2ExMzBiMTkxZDFlM2QwNmNkZWVjYmYxOWE5YjU2In0%3D'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = explode('<td class="place">',$response);
        $conferintaA = array();
        for($i = 1; $i < 9; $i++){
            $conferintaA[] = '<td>' . $response[$i];
        }
        $ultimaEchipa = explode('<div class="tab-pane fade" id="home">',$response[9]);
        $conferintaA[] = '<td>' . $ultimaEchipa[0];

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://baschet.ro/liga-nationala-de-baschet-masculin/clasament?faza=1396&grupa=1884',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: XSRF-TOKEN=eyJpdiI6IkZcL29Kc09UcXpKMkp5VGp5S0h4NEVRPT0iLCJ2YWx1ZSI6IjUzOXNIQlNabERINHRoV2gxbCs3b0NqYTg5TzEwUXNMQ0tFa3VNVUZaeXJ3YUFcL29nR2NHM2p4NHQ0c25NUnhYIiwibWFjIjoiZWM2ZWIzMzkyMzFlYzVmNmRmYWZhZjJkNWQ1YzViYzBlYzlhZTYwNGY5M2NmNzJiNWFkMDc5MDFmZjg2MGQ3NiJ9; baschetro_session=eyJpdiI6IkxwWXB4ZktxeisrT201XC9YZjBxXC9Mdz09IiwidmFsdWUiOiJ1MDJ4ZHJcL0p0dHNUMlVcL0toNk9nSm5nMnpTVndTSWdMSGplMmtocDF4MmxQWHNoRzVcLzNnMGhYVU9TdU4xcmprIiwibWFjIjoiMDExYWMzZDllMDJhMTE5NmM4NTc1NGIyNDhmMDY1MzcwYTJlNGMyNDQ2MmU5ZWYzNmZmNTFmODE1NzRjNGNjZCJ9'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = explode('<td class="place">',$response);
        $conferintaB = array();
        for($i = 1; $i < 9; $i++){
            $conferintaB[] = '<td>' . $response[$i];
        }
        $ultimaEchipa = explode('<div class="tab-pane fade" id="home">',$response[9]);
        $conferintaB[] = '<td>' . $ultimaEchipa[0];

        return $this->render("main/clasament.html.twig",[
                "confA" => $conferintaA,
                "confB" => $conferintaB
            ]);
    }
    #[Route('/export', name: 'app_export')]
    public function ExportNews(){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet ->getActiveSheet();
        $sheet->setTitle("Stiri");
        $sheet->setCellValue("A1", "id");
        $sheet->setCellValue("B1", "rezumat");
        $sheet->setCellValue("C1", "text");
        $sheet->setCellValue("D1", "autor");
        $sheet->setCellValue("E1", "Poza1");
        $sheet->setCellValue("F1", "Poza2");
        $sheet->setCellValue("G1", "Poza3");
        $stiri = $this->stiriRepository->findAll();
        for($i=2;$i<=count($stiri)+1;$i++){
            $sheet->setCellValue("A".$i, $stiri[$i-2]->getId());
            $sheet->setCellValue("B".$i, $stiri[$i-2]->getRezumat());
            $sheet->setCellValue("C".$i, $stiri[$i-2]->getText());
            $sheet->setCellValue("D".$i, $stiri[$i-2]->getAutor());
            $sheet->setCellValue("E".$i, $stiri[$i-2]->getPoza1());
            $sheet->setCellValue("F".$i, $stiri[$i-2]->getPoza2());
            $sheet->setCellValue("G".$i, $stiri[$i-2]->getPoza3());
        }
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'stiri.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer -> save($temp_file);
        return $this->file($temp_file,$fileName,ResponseHeaderBag::DISPOSITION_INLINE);
    }
    #[Route('/import', name: 'app_import')]
    public function ImportNews(\Symfony\Component\HttpFoundation\Request $request)
    {
        $the_file = $request->files->get('uploaded_file');
        try{
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet        = $spreadsheet->getActiveSheet();
            $row_limit    = $sheet->getHighestDataRow();
            $row_range    = range( 2, $row_limit );
            $data = array();
            foreach ( $row_range as $row ) {
                $check = $this->stiriRepository->findOneBy(array("id" =>  $sheet->getCell( 'A' . $row )->getValue()));
                if(isset($check)){
                    continue;
                }
                $stire = new Stiri();
                $stire->setTitlu($sheet->getCell( 'B' . $row )->getValue());
                $stire->setRezumat($sheet->getCell( 'C' . $row )->getValue());
                $stire->setText($sheet->getCell( 'D' . $row )->getValue());
                $stire->setPoza1($sheet->getCell( 'E' . $row )->getValue());
                $stire->setPoza2($sheet->getCell( 'F' . $row )->getValue());
                $stire->setPoza3($sheet->getCell( 'G' . $row )->getValue());
                $this->stiriRepository->save($stire);
            }
        } catch (Exception $e) {
            var_dump($e);
        }

        return new Response("importate");
    }
}