<?php

namespace Editor\ImgeditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Editor\ImgeditorBundle\Entity\Photo;
use Editor\ImgeditorBundle\Form\PhotoType;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
    
    $photo = new Photo();
  
      
       $em = $this->getDoctrine()->getManager();
   $form = $this->createForm(new PhotoType(), $photo, array(
       'method'=>'POST',
       'action'=>  $this->generateUrl('editor_imgeditor_homepage')
      
       
   ));

   if($request->getMethod()=='POST'){

     $form->handleRequest($request);

      if($form->isValid()){

  
          $em->persist($photo);
      
          $em->flush();
        
//          print_r($_COOKIE);
//          exit;
         
         return $this->redirect($this->generateUrl('editor_imgeditor_loaded', array('hash_kroku'=>$_COOKIE['hash_kroku'], 'path'=>$_COOKIE['path'])));
      
      }
    }
      
      
       return $this->render('EditorImgeditorBundle:Default:index.html.twig', array('form'=> $form->createView(),));
   
  
        
        return $this->render('EditorImgeditorBundle:Default:index.html.twig');
    }
}
