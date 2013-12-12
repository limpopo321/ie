<?php

namespace Editor\ImgeditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use \Imagick;

/**
 * Photo
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Photo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="hash_kroku", type="string", length=255)
     */
    private $hash_kroku;

    /**
     * @var string
     *
     * @ORM\Column(name="hash_sesji", type="string", length=255)
     */
    private $hash_sesji;

    
    /**
     * @Assert\Image(
     *      maxWidth="6000",
     *      maxHeight="6000", 
     *      maxWidthMessage="Zdjęcie jest za duże max to {{ limit }}",
     *      maxHeightMessage="Zdjęcie jest za duże max to {{ limit }}",
     *      mimeTypes="image/jpeg"
     * )
     */
    private $file;

    
    
    public function getAbsolutePath(){
      return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
  }
  
  public function getWebPath(){
      return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
  }
 
   protected function getUploadRootDir()
    {
         return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }


  protected function getUploadDir(){
      return 'uploads/photos';
  }
  
  
  
  /**
   * 
   * @ORM\PrePersist()
   */
  public function preUpload(){
      if(null !== $this->file){
          $this->path = uniqid(rand(1, 999)).'.'.$this->file->guessExtension();
          $this->hash_kroku = uniqid();
          $this->hash_sesji = session_id();
          
          setcookie('hash_kroku', $this->hash_kroku);
          setcookie('hash_sesji', $this->hash_sesji);
          setcookie('path', $this->path);
          
          
      }
      
  }
  
  /**
   * 
   * @ORM\PostPersist()
   */
 public function upload(){
      if(null === $this->file){
          return;
      }  
      $this->file->move($this->getUploadRootDir(), $this->path);
       $this->file = null; 

       $adres = $this->getUploadDir().'/'.$this->getPath();
//       $oryginal_adres = $this->getUploadDir().'/oryginal/'.$this->getPath();
//      
//       
//        /////////========= bibiloteka GD ===========///
//       
//       ////             duże do podglądu      //////
//       
//       $image = imagecreatefromjpeg($adres);
//       
//       $img_width = imagesx($image);
//       $img_height = imagesy($image);
//       
//    if($img_width>950||$img_height>950){
//       if($img_width>$img_height){
//           $wsp = $img_width/950;
//           
//           $newwidth = $img_width/$wsp;
//           $newheight = $img_height/$wsp;
//           
//       }else {
//           $wsp = $img_height/950;
//           
//           $newwidth = $img_width/$wsp;
//           $newheight = $img_height/$wsp;
//       }
//       
//       
//       
//       header('Content-Type: image/jpeg');
//       $oryginal_img = imagecreatetruecolor($newwidth, $newheight);
//       imagecopyresized($oryginal_img, $image, 0, 0, 0, 0, $newwidth, $newheight, $img_width, $img_height);
//       
//         $matrix = array(
//           array(-1.2, -1, -1.2),
//           array(-1, 30, -1),
//           array(-1.2, -1, -1.2)
//       );
//       
//       $div = array_sum(array_map('array_sum', $matrix));
//       $offset = 0;
//       
//       imageconvolution($oryginal_img, $matrix, $div, $offset);
//       
//       imagejpeg($oryginal_img, $oryginal_adres, 90);
//    }else{
//        copy($adres, $oryginal_adres);
//    }
//    
//    /////////// ===    miniatura  ==== ///////
//    
//    
//    if($img_width>100||$img_height>66){
//       if($img_width>$img_height){
//           $wsp = $img_height/66;
//           
//           $newwidth = $img_width/$wsp;
//           $newheight = $img_height/$wsp;
//           
//       }else {
//           $wsp = $img_height/66;
//           
//           $newwidth = $img_width/$wsp;
//           $newheight = $img_height/$wsp;
//       }
//       
//       
//       
//       header('Content-Type: image/jpeg');
//       $oryginal_img = imagecreatetruecolor($newwidth, $newheight);
//       imagecopyresized($oryginal_img, $image, 0, 0, 0, 0, $newwidth, $newheight, $img_width, $img_height);
//       
//     
//       
//       imagejpeg($oryginal_img, $adres, 100);
//    }else{
//        copy($adres, $adres);
//    }
//    imagedestroy($oryginal_img);
//       
//       
// 

     
  }
  
  /**
   * 
   * @ORM\PostRemove()
   */
  public function removeUpload()
  {
      if($file=$this->getAbsolutePath()){
          unlink($file);
      }
  }

    
    
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Photo
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set hash_kroku
     *
     * @param string $hashKroku
     * @return Photo
     */
    public function setHashKroku($hashKroku)
    {
        $this->hash_kroku = $hashKroku;

        return $this;
    }

    /**
     * Get hash_kroku
     *
     * @return string 
     */
    public function getHashKroku()
    {
        return $this->hash_kroku;
    }

    /**
     * Set hash_sesji
     *
     * @param string $hashSesji
     * @return Photo
     */
    public function setHashSesji($hashSesji)
    {
        $this->hash_sesji = $hashSesji;

        return $this;
    }

    /**
     * Get hash_sesji
     *
     * @return string 
     */
    public function getHashSesji()
    {
        return $this->hash_sesji;
    }
    
     /**
     * Set file
     *
     * 
     * @return Album
     */
    public function setFile($file)
    {
        $this->file = $file;
    
        return $this;
    }

    /**
     * Get file
     *
     * 
     */
    public function getFile()
    {
        return $this->file;
    }
    
}
