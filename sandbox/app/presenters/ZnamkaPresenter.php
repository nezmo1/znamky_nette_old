<?php

namespace App\Presenters;

use Nette,
	App\Model;
 use Nette\Forms\FormContainer;
     

class ZnamkaPresenter extends BasePresenter
{
  
    
 public $database;
 public $tridap;
 
 public $hodnoty;
 
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        
    }
    
  public function VyberTrid($ucitel){
    $predmety=  $this->database->query('SELECT uv.ucitele_uvazek, uv.ucitel,uv.predmet as `predmet`,uv.trida as `trida`,t.jmeno_tridy as `jmeno_tridy`,p.nazev as `nazev` FROM `ucitele_uvazek` as `uv` 
INNER JOIN trida as `t` on uv.trida=t.id_tridy
INNER JOIN predmet as `p` on uv.predmet=p.id_predmetu
WHERE ucitel= ?', $ucitel,' ORDER by trida');  
    
   
    $predmety_pom=array();
     $predmety_pom+= array ('n'  => 'Vyberte předmět',); 
                foreach ($predmety as $predmet) {  
                 $predmety_pom+= array (''.$predmet->trida.''  => ''.$predmet->jmeno_tridy.'',); 
                } 
    return $predmety_pom;
  }  
  
  
  public function VyberPredmetu($ucitel,$trida){
    $predmety=  $this->database->query('SELECT uv.ucitele_uvazek, uv.ucitel,uv.predmet as `predmet`,uv.trida as `trida`,t.jmeno_tridy as `jmeno_tridy`,p.nazev as `nazev` FROM `ucitele_uvazek` as `uv` 
INNER JOIN trida as `t` on uv.trida=t.id_tridy
INNER JOIN predmet as `p` on uv.predmet=p.id_predmetu
WHERE ucitel= ?', $ucitel,' and trida= ?',$trida ,' ORDER by nazev');  
    
   
    
   
   
    $predmety_pom=array();
                foreach ($predmety as $predmet) {  
                $skupina=$this->database->table('skupina')->where('ucitel',$ucitel)->where('trida',$trida)->where('predmet',$predmet->predmet)->fetchAll(); 
                
                if($skupina!=FALSE){
                    foreach($skupina as $sk){
                     $predmety_pom+= array ('s_'.$sk->id_skupiny.''  => ''.$sk->nazev_skupiny.'',);   
                    }
                        
                    }
                   else {
                     $predmety_pom+= array ('p_'.$predmet->predmet.''  => ''.$predmet->nazev.'',);   
                   }
                        
     
                
                    
                } 
    return $predmety_pom;
  }  
   
 protected function createComponentNovaZnamka()
	{
     
     
    $get=$this->request->getParameters();
               if(isset($get['tridac'])){
                 $tridac= $get['tridac'];  
              
                
               }
               
                 
		$form = new Nette\Application\UI\Form;
                $action_pom='Znamka:novaznamka?tridac='.$tridac;
             $form->setAction($this->presenter->link($action_pom));

                
		$ucitel=$this->getUser();
                $form->addSelect('trida', 'Třída nebo skupina', $this->VyberTrid($ucitel->id));
                if(isset($get['tridac'])){
                 $form['trida']->setDefaultValue($tridac);   
               }
               
                if(isset($get['tridac']) and ($get['tridac']!='n')){
                 $form->addSelect('predmet', 'Předmět', $this->VyberPredmetu($ucitel->id,$tridac));  
               }
                
                
                $form->addText('popis', 'Popis:')
                        ->setAttribute('placeholder', 'Popis k udělené známce (např. Písemný test - Žahavci)')
			->setRequired('Zadejte popis');
                 $form->addText('datum', 'Datum:')
                        ->setAttribute('placeholder', 'Datum')
                         ->setAttribute('readonly', 'readonly')
                         ->setDefaultValue($today = date("Y-m-d"))
                         
			->setRequired('Zadejte datum');
                  $vaha= array(
                  '1'  => 'Nízká', 
                  '2'  => 'Normální', 
                  '3' => 'Vysoká',
                  
      
                );
                   
                $form->addSelect('vaha', 'Váha:', $vaha);
                $form['vaha']->setDefaultValue('2'); 
                 $form->addSubmit('send', 'Vygenerovat formulář');
		// call method signInFormSucceeded() on success
		 $form->onSuccess[] = $this->vygenForm;
                
                
                $renderer = $form->getRenderer();
$renderer->wrappers['controls']['container'] = NULL;

$renderer->wrappers['pair']['container'] = "tr";
$renderer->wrappers['pair']['.odd'] = 'alt';
$renderer->wrappers['label']['container'] = 'td';

$renderer->wrappers['control']['.text'] = 'udaje';
$renderer->wrappers['control']['.password'] = 'udaje';
$renderer->wrappers['control']['.submit'] = 'login-prehled';


		return $form;
	}
        
        
   
        
  public function vygenForm($form, $values){
  
      $this->hodnoty=$values;
      
   $this->redirect('Znamka:vygenFormNovaZnamka?predmet='.$values['predmet'].'&trida='.$values['trida'].'&popis='.$values['popis'].'&vaha='.$values['vaha'].'&datum='.$values['datum'].'');
  
  
  }     
  
  
  protected function createComponentFormHromadnaZnamka()
	{
     
     
    $get=$this->request->getParameters();
               
               
                 
		$form = new Nette\Application\UI\Form;
              

                 $action_pom='Znamka:vygenFormNovaZnamka?predmet='.$get['predmet'].'&trida='.$get['trida'].'&popis='.$get['popis'].'&vaha='.$get['vaha'].'&datum='.$get['datum'].'';
 
             $form->setAction($this->presenter->link($action_pom)); 
		$ucitel=$this->getUser();
               $casti = explode("_", $get['predmet']);
               if($casti[0]=="s"){
                $vyber_zaku=  $this->database->query('SELECT zak,u.jmeno as `jmeno`,u.prijmeni `prijmeni` FROM clenove_skupiny INNER JOIN users as `u` on zak=u.id_users WHERE id_skupiny= ?',$casti[1],' ORDER BY prijmeni,jmeno')->fetchAll();
                 
               }
               else{
                $vyber_zaku=  $this->database->query('SELECT u.jmeno as `jmeno`,u.prijmeni as `prijmeni`  FROM `ucitele_uvazek` 
INNER JOIN users as `u` on ucitele_uvazek.trida=u.trida
WHERE ucitel= ?',$ucitel->id,' and predmet= ?',$casti['1'],' ORDER BY prijmeni,jmeno')->fetchAll();
                   
               }
  $sirka=  $this->database->query('SELECT * FROM nastaveni_personal WHERE id_user= ?',$ucitel->id,'and parametr="sirka_znamek_ano"')->fetch();              
                $pom_cislo=1;
                if($vyber_zaku!=FALSE){
                  foreach($vyber_zaku as $vyber_zak){
                    $id_zak="zak_".$pom_cislo;
                   
                    $form->addText($id_zak, 'Žák')
                        ->setAttribute('readonly', 'readonly')
                        ->setDefaultValue($vyber_zak['jmeno'].' '.$vyber_zak['prijmeni']);  
                    $znamka="znamka_".$pom_cislo;
                    if($sirka!=FALSE){
                     $form->addTextArea($znamka, "Znamka");    
                    }
                    
                    else{
                     $form->addText($znamka, "Znamka");   
                    }
                    
                                        
                    
                  
                  $h_zak="hzak_".$pom_cislo;
                  $form->addHidden($h_zak); 
                  $pom_cislo++;       
                }   
                }
                else{
                  $form->addText('zak_1', 'Žák')
                        ->setAttribute('placeholder', 'Nebyli nalezeni žádní žáci')  
                          ->setAttribute('readonly', 'readonly'); 
                }
               
                 $form->addHidden('pocet_zaznamu');
                      
		 $form->addHidden('datum')
                      ->setValue($get['datum']); 
                 $form->addHidden('trida')
                      ->setValue($get['trida']);
                 $form->addHidden('popis')
                      ->setValue($get['popis']);
                 $form->addHidden('vaha')
                      ->setValue($get['vaha']); 
                 $form->addHidden('predmet')
                      ->setValue($get['predmet']); 
                 $form->addSubmit('send', 'Vložit známky');
		// call method signInFormSucceeded() on success
		 $form->onSuccess[] = $this->vlozitZnamky;
                
                
                $renderer = $form->getRenderer();
$renderer->wrappers['controls']['container'] = NULL;

$renderer->wrappers['pair']['container'] = "tr";
$renderer->wrappers['pair']['.odd'] = 'alt';
$renderer->wrappers['label']['container'] = 'td';

$renderer->wrappers['control']['.text'] = 'udaje';
$renderer->wrappers['control']['.password'] = 'udaje';
$renderer->wrappers['control']['.submit'] = 'login-prehled';


		return $form;
	}
  
  public function vlozitZnamky($form, $values){
      // dump($values);
      $ucitel=$this->getUser();
      $casti = explode("_", $values['predmet']);
      if($casti[0]=="p"){
        for($i=1;$i<$values['pocet_zaznamu'];$i++){
           $znamka='znamka_'.$i; 
           $zak='hzak_'.$i; 
        if($values[$znamka]!=""){
               $this->database->query('INSERT INTO znamky SET znamka= ?',$values[$znamka],', popis= ?',$values['popis'],', datum= ?',$values['datum'],', vaha= ?',$values['vaha'],', ucitel= ?',$ucitel->id,', zak= ?',$values[$zak],', predmet= ?', $casti[1]);  
                                }
         }  
      }
      
      
      if($casti[0]=="s"){
          $predmet=  $this->database->query('SELECT predmet FROM skupina WHERE id_skupiny= ?',$casti[1])->fetch();
        for($i=1;$i<$values['pocet_zaznamu'];$i++){
           $znamka='znamka_'.$i; 
           $zak='hzak_'.$i; 
        if($values[$znamka]!=""){
               $this->database->query('INSERT INTO znamky SET znamka= ?',$values[$znamka],', popis= ?',$values['popis'],', datum= ?',$values['datum'],', vaha= ?',$values['vaha'],', ucitel= ?',$ucitel->id,', zak= ?',$values[$zak],', predmet= ?', $predmet->predmet);  
                                }
         }  
      }
      $this->redirect('Znamka:novaznamka?tridac=n&uspech=ano');
  }
  
  
  
 
	public function renderNovaznamka()
{
            $user =  $this->getUser();
    if ((!$user->isInRole('4')) and (!$user->isInRole('3')) and (!$user->isInRole('2')) ) {
             $this->redirect('Pristup:pristup');
       }
       
      $trida=$get=$this->request->getParameters(); 
if($trida['tridac']!='n'){
    $this->template->trida_p=  $this->tridap;      
 
}
 else{
   $this->template->trida_p=  "n";  
 }  

 if(isset($trida['uspech'])){
    $this->template->uspech=  "ano";      
 
}
 else{
   $this->template->uspech=  "ne";  
 } 
 
 
}	

public function rendervygenFormNovaZnamka()
{
            $user =  $this->getUser();
    if ((!$user->isInRole('4')) and (!$user->isInRole('3')) and (!$user->isInRole('2')) ) {
             $this->redirect('Pristup:pristup');
       }
       $ucitel=$this->getUser();
       $get=$this->request->getParameters();
        $casti = explode("_", $get['predmet']);
               if($casti[0]=="s"){
                   $this->template->vyber_zaku=  $this->database->query('SELECT zak,u.jmeno as `jmeno`,u.prijmeni `prijmeni` FROM clenove_skupiny INNER JOIN users as `u` on zak=u.id_users WHERE id_skupiny= ?',$casti[1])->fetchAll();
                 
               }
               
               else{
              $this->template->vyber_zaku=  $this->database->query('SELECT u.id_users as `zak`, u.jmeno as `jmeno`,u.prijmeni as `prijmeni`  FROM `ucitele_uvazek` 
INNER JOIN users as `u` on ucitele_uvazek.trida=u.trida
WHERE ucitel= ?',$ucitel->id,' and predmet= ?',$casti['1'])->fetchAll();
                   
               }
        
            $this->template->pom_cislo=1;
            
            
  $this->template->sirka=  $this->database->query('SELECT * FROM nastaveni_personal WHERE id_user= ?',$ucitel->id,'and parametr="sirka_znamek_ano"')->fetch();              
               
}	
        
 
        
}


