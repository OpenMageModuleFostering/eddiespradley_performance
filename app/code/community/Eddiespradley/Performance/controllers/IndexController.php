<?php

# app/code/community/eddiespradley/performance/controllers/IndexController.php

class Eddiespradley_Performance_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {

		$this->loadLayout();


		
		$this->_addLeft($this->getLayout()
								->createBlock('esperformance/left')
								->setTemplate('esperformance/left.phtml'));

		$this->_addContent($this->getLayout()
								->createBlock('esperformance/main')
								->setTemplate('esperformance/main.phtml'));


		#$this->getResponse()->setBody($block->toHtml());
		$this->renderLayout();

    }   

    public function testAction()
    {
    	echo "123";
    }

    public function enablecacheAction()
    {
   
echo "<pre>";
        foreach(Mage::app()->getCacheInstance()->getTypes() as $cache)
        {
          	$types[$cache->getId()] = 1;
        }
        
       print_r($types);
             Mage::app()->saveUseCache($types);
             $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__("All cache types are enabled."));
        
        #$this->_redirect('*/*');
        echo "123";
        exit;
    }


 }
