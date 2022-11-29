<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Stiri;
use App\Repository\AccountRepository;
use App\Repository\StiriRepository;
use http\Client\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Loader\Configurator;

class LoginController extends AbstractController
{
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
            $newAccount = new Account();
            $newAccount -> setName($_POST["typeNameX"]);
            $newAccount -> setMail($_POST["typeEmailX"]);
            $newAccount -> setParola($_POST["typePasswordX"]);
            $newAccount -> setRol(0);
            $accountRepository -> save($newAccount);
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
}