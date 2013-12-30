<?php

namespace Editor\ImgeditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Editor\ImgeditorBundle\Entity\Project;
use Editor\ImgeditorBundle\Form\ProjectType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Editor\ImgeditorBundle\Entity\Action;
use Imagick;

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




        return $this->render('EditorImgeditorBundle:Default:index.html.twig', array('form' => $form->createView(), 'baseUrl' => $this->getRequest()->getBaseUrl()));
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
            $action->setImage($project->getPath());
            // json_data nie będzie
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
        // poprawić dane z requesta
        $id_project = '52c1edc201f64';
        $id_action_post = '52c1fe239e3b0';
        $degrees = 13;
        // pobranie adresu do obrazka, ale lepiej by bylo jakbyś mi go przekazał 
        //będzie znacznie mniej kombinacji
        $project = $this->getDoctrine()->getRepository('EditorImgeditorBundle:Project')->findOneBy(
                array('id_project' => $id_project));

        $action = $this->getDoctrine()->getRepository('EditorImgeditorBundle:Action')->findOneBy(
                array('project' => $project->getId(), 'id_action' => $id_action_post), array('id' => 'DESC'), 1);
        //ustalenie ostatniej pozycji akcji w projekcie i...
        $action_position = $this->getDoctrine()->getRepository('EditorImgeditorBundle:Action')->findOneBy(
                array('project' => $project->getId()), array('id' => 'DESC'), 1);
        $position = $action_position->getPosition();
        //... nadanie nowego nr pozycji
        $position++;
        $image = $action->getImage();

        //nowa nazwa pliku po obróceniu
        $new_img_name = uniqid() . '.jpeg';

        $new_path = $project->getUploadDir() . '/' . $new_img_name;

        // 2.
        // obrót obrazka

        $obrazek = new Imagick($project->getUploadDir() . '/' . $image);
        $obrazek->rotateimage(new \ImagickPixel(), $degrees);
        $obrazek->writeimage($new_path);

        // 3.
        // zapisanie danych do bazy 
        // jeśli obrazek 'rotate' wykona się poprawnie

        $em = $this->getDoctrine()->getManager();

        $id_action = uniqid();
        $action = new Action();
        $action->setIdAction($id_action);
        $action->setImage($new_img_name);
        // json_data nie będzie
        $action->setPosition($position); //do pomyślenia ...
        $action->setUpdated(new \DateTime());
        $action->setProject($project);
        $em->persist($action);
        $em->flush();

        // 4.
        // Tworzenie odpowiedzi
        $data = array(
            'status' => 'OK',
            'id_action' => $id_action,
            'image' => $_SERVER['SERVER_NAME'] . '/' . $new_path
        );
        return new JsonResponse($data, 200, array('Content-Type: application/json'));
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
