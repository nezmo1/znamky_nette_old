<?php

namespace App\Presenters;

use Nette,
	App\Model;
       


/**
 * Homepage presenter.
 */
class UsersPresenter extends BasePresenter
{
    
    

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}
        
  
       

}