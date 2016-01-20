<?php
namespace pedrovalmor\AclPlugin\View\Helper;

use Acl\Controller\Component\AclComponent;
use Cake\Controller\ComponentRegistry;
use Cake\View\Helper;
use Cake\View\View;

class AclManagerHelper extends Helper
{

    /**
     * Helpers used.
     *
     * @var array
     */
    public $helpers = ['Html'];

    /**
     * Acl Instance.
     *
     * @var object
     */
    public $Acl;

    /**
     * Construct method.
     *
     * @param \Cake\View\View $view The view that was fired.
     * @param array $config The config passed to the class.
     */
    public function __construct(View $view, $config = [])
    {
        parent::__construct($view, $config);

        $collection = new ComponentRegistry();
        $this->Acl = new AclComponent($collection);
    }

    /**
     *  Check if the Group have access to the aco
     *
     * @param \App\Model\Entity\Group $aro The Aro of the group you want to check
     * @param string                  $aco The path of the Aco like App/Blog/add
     *
     * @return bool
     */
    public function checkGroup($aro, $aco = null)
    {
        if (empty($aro) || empty($aco)) {
            return false;
        }
        return $this->Acl->check($aro, $aco);
    }
}
