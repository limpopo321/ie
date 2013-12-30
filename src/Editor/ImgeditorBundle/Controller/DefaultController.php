<?php

namespace Editor\ImgeditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Editor\ImgeditorBundle\Entity\Project;
use Editor\ImgeditorBundle\Form\ProjectType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Editor\ImgeditorBundle\Entity\Action;

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
        $project = new Project();
        $form = $this->createForm(new ProjectType(), $project);




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

        $project = new Project();
        $action = new Action();
        $form = $this->createForm(new ProjectType(), $project);
        $form->handleRequest($request);

        if ($form->isValid()) {

            // Zapisywanie obrazka
            // Zapisywanie danych
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);

            $id_action = uniqid();
            $action->setIdAction($id_action);
            $action->setImage($_SESSION['path']);
            $action->setJsonData('jakieś dane json'); // do ustalenia....
            $action->setPosition(0); //do pomyślenia ...
            $action->setUpdated(new \DateTime());
            $action->setProject($project);
            $em->persist($action);

            $em->flush();
        }

        // 2.
        // Tworzenie odpowiedzi
        $data = array(
            'status' => 'OK',
            'id_action' => $id_action,
            'image' => $_SERVER['SERVER_NAME'] . '/' . $project->getWebPath()
        );
        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

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
