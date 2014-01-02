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
        
        $router = $this->get('router');
        
        $ieoptions = json_encode(array(
            'urls' => array(
              'urlBase'         => $this->getRequest()->getBaseUrl(),
              'urlProject'      => $router->generate('editor_imgeditor_project'),
              'urlAction'       => $router->generate('editor_imgeditor_action', array('id_action' => ':id_action')),
              'urlFetch'        => $router->generate('editor_imgeditor_fetch_action', array('id_action' => '')) . '/',
              'urlRotate'       => $router->generate('editor_imgeditor_rotate', array('id_action' => ':id_action')),
              'urlCrop'         => $router->generate('editor_imgeditor_crop', array('id_action' => ':id_action')),
            )
        ));        
        
        return $this->render('EditorImgeditorBundle:Default:index.html.twig', array('form' => $form->createView(), 'ieoptions' => $ieoptions));
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
    public function createAction() {
        // 1.
        // Zapisywanie obrazka (obrazek jest przechowywany w tablicy $_FILES[image]

        $request = $this->getRequest();
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
                'id_action' => $action->getIdAction(),
                'image' => $_SERVER['SERVER_NAME'] . '/' . $project->getWebPath()
            );
            return new JsonResponse($data, 200, array('Content-Type: application/json'));
        
      }

    /**
     * Obracanie zdjecia o 90 stopni zgodnie ze wskazowkami zegara
     */
    public function rotateAction($id_action) {
        // poprawić dane z requesta
        // przydały by się też kierunki obratu (lewo prawo)
        $degrees = 90;
        // pobranie danych z Action na podstawie id_action
        $dane = $this->getDataFromAction($id_action);

        // 2.
        // obrót obrazka
        $obrazek = new Imagick($dane['project']->getUploadDir() . '/' . $dane['image']);
        $obrazek->rotateimage(new \ImagickPixel(), $degrees);
        $obrazek->writeimage($dane['new_path']);
        // 3.
        // zapisanie danych do bazy 
        // jeśli obrazek 'rotate' wykona się poprawnie
        $id_action = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project']);
        // 4.
        // Tworzenie odpowiedzi
        $data = array(
            'status' => 'OK',
            'id_action' => $id_action,
            'image' => $_SERVER['SERVER_NAME'] . '/' . $dane['new_path']
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

    /**
     * 
     * @param type $id_action
     * @param type $asAction
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function fetchAction($id_action) {
        $action = $this->getDoctrine()->getRepository('EditorImgeditorBundle:Action')->findOneBy(
                array('id_action' => $id_action)
        );

        $src = '/' . $action->getUploadDir() . '/' . $action->getImage();

        $response = array(
            'src' => $src,
            'id_action' => $id_action
        );
        return new JsonResponse($response);
    }

    private function getDataFromAction($id) {
        // pobranie adresu do obrazka, ale lepiej by bylo jakbyś mi go przekazał 
        //będzie znacznie mniej kombinacji
        $action_repo = $this->getDoctrine()->getRepository('EditorImgeditorBundle:Action')->findOneBy(
                array('id_action' => $id));
        // ustalenie projektu do którego ma być przypisane akcja  
        $project = $action_repo->getProject();
        //ustalenie ostatniej pozycji akcji w projekcie i...
        $position = $action_repo->getPosition();
        //... nadanie nowego nr pozycji
        $position++;
        $image = $action_repo->getImage();
        //nowa nazwa pliku po obróceniu
        $new_img_name = uniqid() . '.jpeg';
        // i nowa ścieżka do niego relatywna
        $new_path = $project->getUploadDir() . '/' . $new_img_name;
        //dane zwracane przez funkcje
        $data_from_action = array(
            'image' => $image,
            'new_path' => $new_path,
            'project' => $project,
            'position' => $position,
            'new_img_name' => $new_img_name,
            'new_path' => $new_path
        );

        return $data_from_action;
    }

    /**
     * 
     * zapisuje dane do encji action i zwraca nowy numer akcji
     * @return string $id_action
     */
    private function saveToAction($position, $image, $project, $json_data = null) {
        $id_action = uniqid();
        $action = new Action();
        $action->setIdAction($id_action);
        $action->setImage($image);
        $action->setJsonData($json_data);
        $action->setPosition($position); //do pomyślenia ...
        $action->setUpdated(new \DateTime());
        $action->setProject($project);
        $em = $this->getDoctrine()->getManager();
        $em->persist($action);
        $em->flush();

        return $id_action;
    }

}
