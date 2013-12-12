<?php
namespace Editor\ImgeditorBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LoadedController
 *
 * @author root
 */
class LoadedController {
    public function indexAction($path, $hash_kroku){
        
        
        
        return new Response('<html><body>'.$path.'<br />'. $hash_kroku.'</body></html>');
    }
}

?>
