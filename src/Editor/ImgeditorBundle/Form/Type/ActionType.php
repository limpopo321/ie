<?php
namespace Editor\ImgeditorBundle\Form\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class ActionType extends AbstractType {
    
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('file', 'file',  array('label' => 'Załaduj zdjęcie'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'Editor\ImgeditorBundle\Entity\Action'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'editor_imgeditorbundle_action';
    }
}