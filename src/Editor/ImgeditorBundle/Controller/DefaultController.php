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
        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);
        // 4.
        // Tworzenie odpowiedzi

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

    /**
     * Przycinanie obrazka
     */
    public function cropAction($id_action) {
        // potrzebne mi będą:
        // szerokość, wysokość cropa oraz współrzędne lewego górnego rogu cropa w px oczywiście
        $crop_width = 380;
        $crop_height = 200;
        $x = 20;
        $y = 50;

        // pobranie danych obrazka z encji
        $dane = $this->getDataFromAction($id_action);
        // crop
        $obrazek = new Imagick($dane['project']->getUploadDir() . '/' . $dane['image']);
        $obrazek->cropimage($crop_width, $crop_height, $x, $y);
        $obrazek->writeimage($dane['new_path']);

        // 3.
        // zapisanie danych do bazy 
        // jeśli crop wykona się poprawnie
        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);
        // 4.
        // Tworzenie odpowiedzi

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

    /**
     * 
     * @param type $id_action
     * @return type \Symfony\Component\HttpFoundation\JsonRespons
     */
    public function contrastAction($id_action) {
        //potrzebny "skok" zakres mniej więcej -100 do 100 chociaż to i tak dużo raczej
        // liczby całkowite
        // skok co 2
        // - to większy
        // + to mniejszy
        $level = -10;
        //pobranie danych...
        $dane = $this->getDataFromAction($id_action);
        // zniejszanie/zwiększanie kontrastu

        $img = imagecreatefromjpeg($dane['project']->getUploadDir() . '/' . $dane['image']);
        imagefilter($img, IMG_FILTER_CONTRAST, $level);
        imagejpeg($img, $dane['new_path']);
        imagedestroy($img);

        // 3.
        // zapisanie danych do bazy 
        // jeśli contrast wykona się poprawnie
        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);
        // 4.
        // Tworzenie odpowiedzi

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

    public function brightnessAction($id_action) {
        // liczby całkowite
        // + to jaśniej 
        // - to ciemniej
        // 0 to bez zmian
        // skoki co 10 
        $brightness = 80;

        $dane = $this->getDataFromAction($id_action);
        $img = imagecreatefromjpeg($dane['project']->getUploadDir() . '/' . $dane['image']);
        imagefilter($img, IMG_FILTER_BRIGHTNESS, $brightness);
        imagejpeg($img, $dane['new_path']);
        imagedestroy($img);

        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

    /**
     * 
     * @param type $id_action
     * @return type \Symfony\Component\HttpFoundation\JsonRespons
     */
    public function grayscaleAction($id_action) {
        //pobranie danych...
        $dane = $this->getDataFromAction($id_action);
        // zniejszanie/zwiększanie kontrastu

        $img = imagecreatefromjpeg($dane['project']->getUploadDir() . '/' . $dane['image']);
        imagefilter($img, IMG_FILTER_GRAYSCALE);
        imagejpeg($img, $dane['new_path']);
        imagedestroy($img);

        // 3.
        // zapisanie danych do bazy 
        // jeśli contrast wykona się poprawnie
        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);
        // 4.
        // Tworzenie odpowiedzi

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

    /**
     * 
     * @param type $id_action
     * @return type \Symfony\Component\HttpFoundation\JsonRespons
     */
    public function sharpenAction($id_action) {
        // potrzebny promień wyostrzenia i odchylenie standardowe(sigma)
        // skoki co 1 lub co 0,5
        // typ float

        $radius = 1;
        $sigma = 0;

        //pobranie danych...
        $dane = $this->getDataFromAction($id_action);
        // zwiększanie wyostrzenia

        $obrazek = new Imagick($dane['project']->getUploadDir() . '/' . $dane['image']);
        $obrazek->sharpenimage($radius, $sigma);
        $obrazek->writeimage($dane['new_path']);
        $obrazek->destroy();

        // 3.
        // zapisanie danych do bazy 
        // jeśli contrast wykona się poprawnie
        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);
        // 4.
        // Tworzenie odpowiedzi

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
    }

    /**
     * 
     * @param type $id_action
     * @return type \Symfony\Component\HttpFoundation\JsonRespons
     */
    public function mirrorAction($id_action) {
        // potrzebny rodzaj:
        // 0 to w pionie
        // 1 to w poziomie

        $flip = 0;

        //pobranie danych...
        $dane = $this->getDataFromAction($id_action);
        // zwiększanie wyostrzenia

        $obrazek = new Imagick($dane['project']->getUploadDir() . '/' . $dane['image']);

        if ($flip === 0) {
            $obrazek->flipimage();
            $obrazek->writeimage($dane['new_path']);
            $obrazek->destroy();
        } elseif ($flip === 1) {
            $obrazek->flopimage();
            $obrazek->writeimage($dane['new_path']);
            $obrazek->destroy();
        }
        // 3.
        // zapisanie danych do bazy 
        // jeśli contrast wykona się poprawnie
        $data = $this->saveToAction($dane['position'], $dane['new_img_name'], $dane['project'], $dane['new_path']);
        // 4.
        // Tworzenie odpowiedzi

        return new JsonResponse($data, 200, array('Content-Type: application/json'));
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
     * zapisuje dane do encji action i zwraca $data
     * @return string $data
     */
    private function saveToAction($position, $image, $project, $new_path, $json_data = null) {
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

        $data = array(
            'status' => 'OK',
            'id_action' => $id_action,
            'image' => $_SERVER['SERVER_NAME'] . '/' . $new_path
        );

        return $data;
    }

}
