<?php

namespace app\controllers;

use yii\g1rlp0wer\Query;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Notizia;
use app\models\Immagine;
use app\models\Fonte;
use app\models\Whitelist;
use yii\httpclient\Client;
use yii\helpers\Url;

class FonteController extends Controller
{
	public function actionAnalisiFonte()
	{
		$link_notizia=Yii::$app->session->get('notizia');
    
        $apiKey = '7f131cfc0c77133d0ce81e4cea38e7acdb524ea930e443f31c6ddb9dd158829d';

        
        $domain = parse_url($link_notizia, PHP_URL_HOST);
        $domain_parts = explode(".", $domain);
        $domain = $domain_parts[count($domain_parts)-2] . "." . $domain_parts[count($domain_parts)-1];
        Yii::$app->session->set('domain', $domain);

        $url='https://www.virustotal.com/api/v3/domains/'.$domain;

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->addHeaders(['x-apikey' => $apiKey])
            ->send();

        $responseData=0;

        if ($response->isOk) {
            $responseData = $response->data;
        } else {
            // Gestione dell'errore nella richiesta
            echo "Errore nella richiesta di ottenimento delle informazioni dell'URL.";
        }


        $data1 = $responseData['data'];

        $attributes = $data1['attributes'];

        $lastAnalysisStats=$attributes['last_analysis_stats'];
        $harmless=$lastAnalysisStats['harmless'];
        $malicious=$lastAnalysisStats['malicious'];
        $suspicious=$lastAnalysisStats['suspicious'];
        $undetected=$lastAnalysisStats['undetected'];
        $timeout=$lastAnalysisStats['timeout'];

        $categories=$attributes['categories'];
        $argomenti=implode(', ', $categories);

        $indice=($harmless+$malicious+$suspicious+$undetected+$timeout)/5;

        $fonte=new Fonte();

        $fonte->Fonte=$domain;
        $fonte->Indice=$indice;
        $fonte->Argomento=$argomenti;

        if ($fonte->save()) {
            //return $this->redirect($redirectUrl);
        }
        else 
        {
            // Salvataggio fallito, visualizza gli errori
            $errors = $fonte->getErrors();
            var_dump($errors);
        }
        
        return $indice;
	}

    public function actionFontiInWhitelist()
    {
        $fonte=Yii::$app->session->get('domain');

        $fonti=Whitelist::find()->select('*')->where(['NomeFonte'=> $fonte])->distinct()->all();

        return $fonti;
    }

    public function actionFontiSimili()
    {
        $fonte=Yii::$app->session->get('domain');

        $fonti=Whitelist::find()->select('*')->where(['like', 'NomeFonte', '%' . $fonte . '%', false])->distinct()->all();

        return $fonti;
    }
}
