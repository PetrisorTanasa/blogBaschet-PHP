<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Comentarii;
use App\Entity\StatisticiVizitatori;
use App\Entity\Stiri;
use App\Repository\AccountRepository;
use App\Repository\ComentariiRepository;
use App\Repository\StatisticiRepository;
use App\Repository\StiriRepository;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use http\Client\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Loader\Configurator;
//
require_once(__DIR__ . '/../../public/phpmailer/mail_cod.php');

//require_once('phpmailer/mail_cod.php');

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
        if(isset($_SESSION["rol"])) {
            return $this->render("login/signup.html.twig", [
                "mesaj" => $mesaj,
                "rol" => $_SESSION["rol"]
            ]);
        }else{
            return $this->render("login/signup.html.twig", [
                "mesaj" => $mesaj
            ]);
        }
    }

    #[Route('/account/check/{check?}', name: 'app_check_account')]
    public function accountCheck(AccountRepository $accountRepository, MailerInterface $mailer, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        try {
            session_start();
        }catch(\Exception $exception){}
        if(isset($_POST["check"])) {
            if ($_POST["typeEmailX"] == "") {
                return $this->render("login/login.html.twig", [
                    "error" => 3
                ]);
            } else {
                $cont = $accountRepository->findOneBy(array("mail" => $_POST["typeEmailX"]));
                if(!isset($cont)){
                    return $this->render("login/login.html.twig", [
                        "error" => 5
                    ]);
                }
                $email = new EmailSend();
                $email->sendEmail($cont->getMail(), $cont->getName(), $cont->getParola());
                return $this->render("login/login.html.twig", [
                    "error" => 4
                ]);
            }
        }
        if(isset($_POST["Login"])){
            if(!isset($_SESSION["incercare"])) {
                $_SESSION["incercare"] = 1;
            }else{
                $_SESSION["incercare"]++;
            }
            if($_SESSION["incercare"] % 3 == 1) {
                if (isset($_SESSION["cod"]) and $_SESSION["cod"] != $_POST["captcha"]) {
                    return $this->render("login/login.html.twig", [
                        "error" => 2,
                        "incercare" => $_SESSION["incercare"]
                    ]);
                }
            }
            $cont = $accountRepository->findOneBy(array("mail"=>$_POST["typeEmailX"],"parola"=>$_POST["typePasswordX"]));
            if(isset($cont)){
                $_SESSION["loggedIn"] = 1;
                $_SESSION["nume"] = $cont->getName();
                $_SESSION["rol"] = $cont->getRol();
                $_SESSION["incercare"] = 1;
                return $this->redirectToRoute('app_main');
            }else{
                return $this->render("login/login.html.twig",[
                    "error" => 1,
                    "incercare" => $_SESSION["incercare"]
                ]);
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
                $newAccount = new Account();
                $newAccount->setName($_POST["typeNameX"]);
                $newAccount->setMail($_POST["typeEmailX"]);
                $newAccount->setParola($_POST["typePasswordX"]);
                if(isset($_SESSION["rol"])and $_SESSION["rol"] == 2 and isset($_POST["rol"])){
                    $newAccount->setRol($_POST["rol"]);
                }else {
                    $newAccount->setRol(0);
                }
                $accountRepository->save($newAccount);
                $email = new EmailSend();
                $email->sendEmail($_POST["typeEmailX"],$_POST["typeNameX"],$_POST["typePasswordX"]);
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
    public function main(StiriRepository $stiriRepository, StatisticiRepository $statisticiRepository): Response
    {
        try {
            session_start();
        }catch(\Exception $exception){}
//        if(!isset($_SESSION["sistem"])){
//            $_SESSION["sistem"] = $_SERVER["HTTP_SEC_CH_UA_PLATFORM"];
//            $statistica
//            $statistica = new StatisticiVizitatori();
//            $statistica->set==
//        }
        $stiri = $stiriRepository->findAll();
        if(isset($_SESSION["rol"]) and isset($_SESSION["nume"])) {
            return $this->render("main/main.html.twig", [
                "nume" => $_SESSION["nume"],
                "rol" => $_SESSION["rol"],
                "stiri" => array($stiri[0],$stiri[1],$stiri[2])
            ]);
        }else{
            return $this->render("main/main.html.twig",[
                "nume" => NULL,
                "stiri" => array($stiri[0],$stiri[1],$stiri[2])
            ]);
        }
    }
    #[Route('/stiri', name: 'app_stiri')]
    public function Stiri(StiriRepository $stiriRepository): Response
    {
        try {
            session_start();
        } catch (\Exception $exception) {
        }
        $stiri = $stiriRepository->findAll();
        if(isset($_SESSION["rol"]) and isset($_SESSION["nume"])) {
            return $this->render("main/stiriView.html.twig", [
                "nume" => $_SESSION["nume"],
                "rol" => $_SESSION["rol"],
                "stiri" => $stiri
            ]);
        }else{
            return $this->render("main/stiriView.html.twig",[
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
            if(isset($_POST["updateStire"])){
                $articol = $stiriRepository->find($_POST["updateStire"]);
            }else {
                $articol = new Stiri();
            }
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
    public function seeArticle(ComentariiRepository $comentariiRepository,StiriRepository $stiriRepository,\Symfony\Component\HttpFoundation\Request $request): Response
    {
            try {
                session_start();
            }catch(\Exception $exception){}
        $comentarii = $comentariiRepository->findBy(array("id_anunt"=>$request->get("id")));
        $stire = $stiriRepository -> find($request->get("id"));
        if(isset($_SESSION["rol"])) {
            return $this->render("main/stire.html.twig", [
                    "stire" => $stire,
                    "id" => $request->get("id"),
                    "comentarii" => $comentarii,
                    "rol" => $_SESSION["rol"]
                ]
            );
        }else {
            return $this->render("main/stire.html.twig", [
                    "stire" => $stire,
                    "id" => $request->get("id"),
                    "comentarii" => $comentarii
                ]
            );
        }
    }
    #[Route('/update/{id}', name: 'app_article_update')]
    public function updateArticle(StiriRepository $stiriRepository,\Symfony\Component\HttpFoundation\Request $request): Response
    {
        try {
            session_start();
        } catch (\Exception $exception) {
        }
        if (isset($_SESSION["rol"]) and ($_SESSION["rol"] == 1 or $_SESSION["rol"] == 2)) {
            $stire = $stiriRepository->find($request->get("id"));
            return $this->render("main/article.html.twig", [
                "stire" => $stire
            ]);
        }
        return new Response("Nu aveti permisiune");
    }
    #[Route('/delete/{id}', name: 'app_article_delete')]
    public function deleteArticle(StiriRepository $stiriRepository,\Symfony\Component\HttpFoundation\Request $request): Response
    {        try {
        session_start();
    }catch(\Exception $exception){}
        if(isset($_SESSION["rol"]) and ($_SESSION["rol"] == 1 or $_SESSION["rol"] == 2)){
        $stiriRepository->remove($stiriRepository->find($request->get("id")));
        return $this->redirectToRoute('app_main');
        }
        return new Response("Nu aveti permisiune");
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
            try {
                session_start();
            }catch(\Exception $exception){}
            if($_SESSION["rol"]>=1) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle("Stiri");
                $sheet->setCellValue("A1", "id");
                $sheet->setCellValue("B1", "titlu");
                $sheet->setCellValue("C1", "rezumat");
                $sheet->setCellValue("D1", "text");
                $sheet->setCellValue("E1", "autor");
                $sheet->setCellValue("F1", "Poza1");
                $sheet->setCellValue("G1", "Poza2");
                $sheet->setCellValue("H1", "Poza3");
                $stiri = $this->stiriRepository->findAll();
                for ($i = 2; $i <= count($stiri) + 1; $i++) {
                    $sheet->setCellValue("A" . $i, $stiri[$i - 2]->getId());
                    $sheet->setCellValue("B" . $i, $stiri[$i - 2]->getTitlu());
                    $sheet->setCellValue("C" . $i, $stiri[$i - 2]->getRezumat());
                    $sheet->setCellValue("D" . $i, $stiri[$i - 2]->getText());
                    $sheet->setCellValue("E" . $i, $stiri[$i - 2]->getAutor());
                    $sheet->setCellValue("F" . $i, $stiri[$i - 2]->getPoza1());
                    $sheet->setCellValue("G" . $i, $stiri[$i - 2]->getPoza2());
                    $sheet->setCellValue("H" . $i, $stiri[$i - 2]->getPoza3());
                }
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $fileName = 'stiri.xlsx';
                $temp_file = tempnam(sys_get_temp_dir(), $fileName);
                $writer->save($temp_file);
                return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
            }
    }
    #[Route('/import', name: 'app_import')]
    public function ImportNews(\Symfony\Component\HttpFoundation\Request $request)
    {
        try {
            session_start();
        }catch(\Exception $exception){}
        if($_SESSION["rol"]>=1) {
            $the_file = $request->files->get('uploaded_file');
            try {
                $spreadsheet = IOFactory::load($the_file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $row_limit = $sheet->getHighestDataRow();
                $row_range = range(2, $row_limit);
                $data = array();
                foreach ($row_range as $row) {
                    $stire = new Stiri();
                    $stire->setTitlu($sheet->getCell('B' . $row)->getValue());
                    $stire->setRezumat($sheet->getCell('C' . $row)->getValue());
                    $stire->setText($sheet->getCell('D' . $row)->getValue());
                    $stire->setAutor($sheet->getCell('E' . $row)->getValue());
                    $stire->setPoza1($sheet->getCell('F' . $row)->getValue());
                    $stire->setPoza2($sheet->getCell('G' . $row)->getValue());
                    $stire->setPoza3($sheet->getCell('H' . $row)->getValue());
                    $this->stiriRepository->save($stire);
                }
            } catch (Exception $e) {
            }
            return new Response("importate");
        }            return new Response("neimportate");
    }
    #[Route('/comenteaza', name: 'app_comentariu')]
    public function Comentarii(ComentariiRepository $comentariiRepository, StiriRepository $stiriRepository)
    {
        try {
            session_start();
        } catch (\Exception $exception) {
        }
        if(isset($_SESSION["nume"]) and isset($_SESSION["rol"])){
            $comentariu = new Comentarii();
            $comentariu->setNumeComentator($_SESSION["nume"]);
            $comentariu->setIdAnunt($_POST["id_anunt"]);
            $comentariu->setComentariu($_POST["comentariu"]);
            $comentariiRepository->save($comentariu);
        }
        $comentarii = $comentariiRepository->findBy(array("id_anunt"=>$_POST["id_anunt"]));
        $stire = $stiriRepository -> find($_POST["id_anunt"]);
        return $this->redirectToRoute("app_watch",[
            "stire" => $stire,
            "id" => $_POST["id_anunt"],
            "comentarii" => $comentarii
        ]);
    }
    #[Route('/statistici', name: 'app_stats')]
    public function Stats(ComentariiRepository $comentariiRepository, StiriRepository $stiriRepository){
        $autoriStiri = [];
        foreach($stiriRepository->findAll() as $bucata){
            if(!array_key_exists($bucata->getAutor(),$autoriStiri)){
                $autoriStiri[$bucata->getAutor()] = 1;
            }else{
                $autoriStiri[$bucata->getAutor()]++;
            }
        }
        $autoriComentarii = [];
        foreach($comentariiRepository->findAll() as $bucata){
            if(!array_key_exists($bucata->getNumeComentator(),$autoriComentarii)){
                $autoriComentarii[$bucata->getNumeComentator()] = 1;
            }else{
                $autoriComentarii[$bucata->getNumeComentator()]++;
            }
        }

        $stats = array(['Categories','Pie expense']);
        foreach ($autoriStiri as $key=>$value){
            array_push($stats,[$key,$value]);
        }
        $pieChart = new PieChart();
        $pieChart->getData()->setArrayToDataTable(
            $stats
        );
        $pieChart->getOptions()->setTitle('Statistici.php autori stiri');
        $pieChart->getOptions()->setHeight(200);
        $pieChart->getOptions()->setWidth(500);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);

        $stats2 = array(['Categories','Pie expense']);
        foreach ($autoriComentarii as $key=>$value){
            array_push($stats2,[$key,$value]);
        }

        $pieChart2 = new PieChart();
        $pieChart2->getData()->setArrayToDataTable(
            $stats2
        );
        $pieChart2->getOptions()->setTitle('Statistici.php autori comentarii');
        $pieChart2->getOptions()->setHeight(200);
        $pieChart2->getOptions()->setWidth(500);
        $pieChart2->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart2->getOptions()->getTitleTextStyle()->setColor('#009900');
        $pieChart2->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart2->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart2->getOptions()->getTitleTextStyle()->setFontSize(20);
        return $this->render("main/stats.html.twig",[
            "piechart" => $pieChart,
            "piechart2" => $pieChart2
        ]);
    }
    #[Route('/importa_baschet_ro', name: 'app_import_baschetro')]
    public function importBaschetRo(StiriRepository $stiriRepository){
        for($i=10;$i>=1;$i--){
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://baschet.ro/liga-nationala-de-baschet-masculin/stiri?page=' . $i,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: XSRF-TOKEN=eyJpdiI6ImFWdSttdGs2U1JWWEd3eENzbG4rOWc9PSIsInZhbHVlIjoiYXNFZFdrYllhMXpPVnpmSkJVeFwvek4yQnJQdWlFU2djZGx6b1pLYzZRN0VFQnc4SnJ2OG1IandmOSt6ZU03czIiLCJtYWMiOiJjMTgzYzllMDQzYzVlOTg2NWU0MDk5MTFlMThhYzdkY2IwZGJlODM4MDdhYmVhZmI1OWQ3NWQ4ZjRlYjhkMzM5In0%3D; baschetro_session=eyJpdiI6IkNMWkorSm9LeG0wbVJKUGw2dmY0SXc9PSIsInZhbHVlIjoiQTRSdWhRUHhuSnp0NTdwZ0pvR0s4Ymw1N2xCMUozcVo0eGpnMWlka0NVb0JiUzQ4anFYdVlDODFlMnZKelMrNyIsIm1hYyI6ImZkMGQ3NmYxNDJjMjYyZjE1NTY1YzBjYmY4MzIyN2ZmNzk4YmU1Yzc5ZDJjMjFkOWI4YTg2OGI1YmFlMDIyNGEifQ%3D%3D'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $r = explode('<a href="https://baschet.ro/liga-nationala-de-baschet-masculin/stiri/', $response);
            $i = 0;
            unset($r[0]);
            unset($r[1]);
            unset($r[2]);
            unset($r[3]);
            foreach($r as $link){
                $linkul = explode('"',$link);
                $stire = $stiriRepository->findOneBy(array("identificator"=>$linkul[0]));
                if(isset($stire)){
                    continue;
                }
                    $stire = new Stiri();

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://baschet.ro/liga-nationala-de-baschet-masculin/stiri/'  . $linkul[0],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Cookie: XSRF-TOKEN=eyJpdiI6ImZDclNHZyttNU14ZlJGQVdaS2VIZWc9PSIsInZhbHVlIjoiak1oXC9JVkp3UmdXUVRBcTZVQzZRWUpEd29CdXpBQ0hwUEhQOWZlK0FYWVBheUZ3SjJYbGt2bVZLUlZwT0MrTnkiLCJtYWMiOiIwZmVmYTgwOTcwYzZhOGYxMzk5NWY0M2IyMDI3OTc0ZTgzZGRmZDZlYmM3NmMzM2Q1MjgwODZjNzc0NjVlYzkwIn0%3D; baschetro_session=eyJpdiI6IkNVY0FycStHU1dZUFwvY00rWFBhMElBPT0iLCJ2YWx1ZSI6IldRRGtPN1RvYWxydVhrTUJRaHQ4NEtsUW9ZTE4zN3orcll6b0txRVUzVkp3b0tVTDNjdVdXbzc2NWtWcXRIZmkiLCJtYWMiOiIyNDE4MTE2MTFiYmNmOGFmMzI0MTE1M2JlMGY2MGRhZmI3NmIwMDAyNTA4NzZmYjM1MGM1OWVmOTNkMWU0NTFjIn0%3D'
                    ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);

                $titlu = explode("<h1>",$response);
                $titlul = explode("</h1>",$titlu[2]);

                $stire->setTitlu($titlul[0]);

                $rezumat = explode("<strong>",$response);
                $rezumatul = explode("</strong>",$rezumat[1]);

                $stire->setRezumat($rezumatul[0]);

                $descriere = explode("<div class=\"single-news-content\">",$response);
                $descrierea = explode("</div>",$descriere[1]);

                $autor = explode('<a href="https://baschet.ro/articole/autor/',$response);
                $autorl = explode(">",$autor[2]);
                $autorul = explode("</a>",$autorl[1]);

                $poza = explode('<img src="https://www.baschet.ro/storage/',$descriere[1]);
                $pozal = explode('alt="',$poza[1]);

                $stire->setText($descrierea[0]);
                $stire->setIdentificator($linkul[0]);
                $stire->setPoza1("https://www.baschet.ro/storage/" . substr(trim($pozal[0]),0,-1));
                $stire->setPoza2("https://www.frbaschet.ro/public/storage/posts/September2022/66toscCpztJSBhafSwFf-largepost.jpg");
                $stire->setAutor(substr($autorul[0], 0, -3));

                $stiriRepository->save($stire);
            }
        }
        return new Response("Au fost importate.");
    }
}