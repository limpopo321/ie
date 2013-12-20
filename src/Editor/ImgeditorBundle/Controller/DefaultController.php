<?php

namespace Editor\ImgeditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Editor\ImgeditorBundle\Entity\Action;
use Editor\ImgeditorBundle\Form\Type\ActionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller {

    /**
     * Strona glowna aplikacji
     * To jest tzw "One page application"
     * Tylko ta akcja zwraca html 
     * pozostale akcje sluza tylko obrobce danych i zawsze zwracaja dane 
     * w formacie JSON
     * 
     */
    public function indexAction() {        
        $action         = new Action();        
        $form           = $this->createForm(new ActionType(), $action);  
        
      
        
        
        return $this->render('EditorImgeditorBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * Zapisywanie i skalowani obrazka jesli jest 
     * on wiekszy niz zakaladany rozmiar maksymalny
     * 
     * Jesli obrazek udalo sie poprawnie zapisac:
     *  - tworzymy nowy hash projektu i zapisujemy go w sesji
     *  - tworzymy odpowiedni rekord w bazie danych
     * 
     */
    public function createAction(Request $request) {      
        // 1.
        // Zapisywanie obrazka (obrazek jest przechowywany w tablicy $_FILES[image]

        $action = new Action();
        $form = $this->createForm(new ActionType(), $action);
        
        
        $form->handleRequest($request);
        
        
        if($form->isValid()){
            
            // Zapisywanie obrazka
            $action->upload();
            
            // Tworzenie hasha projektu
            $id_project = md5(time());
            $session    = $this->get('session');
            $session->set("id_project", $id_project);
            
            // Zapisywanie danych
            $em = $this->getDoctrine()->getManager();
            $action->setIdProject($id_project);
            $action->setPosition(0);
            $em->persist($action);           
            $em->flush();
            
            exit('after_save');
            
            
            
            
            
        }
       
        
        $formIsValid = $form->isValid();
       
        print_r($formIsValid); exit;
        
        
        
        
        
        // 2.
        // Tworzenie odpowiedzi
        $data = array(
            'status' => 'OK',
            'hash_kroku' => 'xyzxyzxyzxyzxyzxyz',
            'image' => 'http://domena.pl/sciezka/do/obrazka.jpg'
        );
        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

//    public function indexAction(Request $request) {
//        $photo = new Photo();
//
//
//        $em = $this->getDoctrine()->getManager();
//        $form = $this->createForm(new PhotoType(), $photo, array(
//            'method' => 'POST',
//            'action' => $this->generateUrl('editor_imgeditor_homepage')
//        ));
//
//        if ($request->getMethod() == 'POST') {
//
//            $form->handleRequest($request);
//
//            if ($form->isValid()) {
//                $em->persist($photo);
//                $em->flush();
//                return $this->redirect($this->generateUrl('editor_imgeditor_loaded', array('hash_kroku' => $_COOKIE['hash_kroku'], 'path' => $_COOKIE['path'])));
//            }
//        }
//        return $this->render('EditorImgeditorBundle:Default:index.html.twig', array('form' => $form->createView(),));
//        
//    }

    /**
     * Obracanie zdjecia o 90 stopni zgodnie ze wskazowkami zegara
     */
    public function rotateAction() {
        
    }

    /**
     * Przycinanie obrazka
     */
    public function cropAction() {
        
    }

    /**
     * "Wstecz"
     */
    public function undoAction() {
        
    }

    /**
     * "Do przodu"
     */
    public function redoAction() {
        
    }

}
