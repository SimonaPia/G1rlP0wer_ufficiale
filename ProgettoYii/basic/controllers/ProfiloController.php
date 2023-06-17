<?php

namespace app\controllers;

use app\models\UtenteRegistrato;
use app\models\Fonte;
use app\models\Blocklist;
use yii\g1rlp0wer\Query;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\Url;

class ProfiloController extends Controller
{
    public function actionProfilo()
    {
        $email=Yii::$app->session->get('email');
        $domain=Yii::$app->session->get('domain');
        $nomeUtente=UtenteRegistrato::find()->select('Nome')->where(['Email'=> $email])->distinct()->all();

        $nome=$nomeUtente[0]->Nome;

        $blocklist=new Blocklist();

        $fonte=Fonte::find()->select('ID')->where(['Fonte' => $domain])->distinct()->all();
        $utente=UtenteRegistrato::find()->select('ID')->where(['Nome' => $nome])->distinct()->all();

        $fonteBloccata=$fonte[0]->ID;
        $utenteBlocca=$utente[0]->ID;

        $blocklist->Utente=$utenteBlocca;
        $blocklist->Fonte=$fonteBloccata;

        $blocklist->save();

        $query = (new \yii\db\Query())
        ->select('fonte.Fonte')
        ->from('Fonte')
        ->join('JOIN', 'Blocklist', 'Fonte.ID = Blocklist.Fonte')
        ->join('JOIN', 'Utente', 'Blocklist.Utente = Utente.ID');

        $lista = $query->all();
       

        return $this->render('profilo', ['nome' => $nome, 'lista' => $lista]);
    }

}
