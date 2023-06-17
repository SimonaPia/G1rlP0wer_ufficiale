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
use app\models\Testo;
use app\models\Fonte;
use app\models\Segnalazione;
use yii\httpclient\Client;
use yii\helpers\Url;
use yii\controllers\FonteController;
use GuzzleHttp\Client as GuzzleClient;

require __DIR__ . '/../vendor/autoload.php';

class GestioneNotiziaController extends Controller
{
    public function actionInserimento()
    {
        $link_notizia=Yii::$app->session->get('notizia');

        if (stripos($link_notizia, "http") !== false && stripos(substr($link_notizia, -7), ".") !== false) 
        {
            $categoria=$this->categoria();
        }        

        if(!empty($categoria))
        {
            $data1 = $categoria['data'];

            $attributes = $data1['attributes'];
            $lastHtpResponseHeaders = $attributes['last_http_response_headers'];
            $contentType=$lastHtpResponseHeaders['Content-Type'];

            $lastAnalysisStats=$attributes['last_analysis_stats'];
            $harmless=$lastAnalysisStats['harmless'];
            $malicious=$lastAnalysisStats['malicious'];
            $suspicious=$lastAnalysisStats['suspicious'];
            $undetected=$lastAnalysisStats['undetected'];
            $timeout=$lastAnalysisStats['timeout'];

            $categories=$attributes['categories'];
            $argomenti=implode(', ', $categories);
            Yii::$app->session->set('argomenti', $argomenti);

            $indice=($harmless+$malicious+$suspicious+$undetected+$timeout)/5;

            $incongruenze=$this->incongruenze($indice);

            $notizia=new Notizia();

            $notizia->Notizia=$link_notizia;
            $notizia->Indice=$indice;
            $notizia->Categoria=$contentType;
            $notizia->Argomento=$argomenti;
            $notizia->Incongruenze=$incongruenze;

            $controllo=0;

            if(Yii::$app->request->post('segnala') !== null)
                $controllo=1;

            if(strstr($contentType, 'image/jpeg'))
            {
                $soggetti=$this->ricercaSoggetti();
                $metaDati=$lastHtpResponseHeaders['Last-Modified'];
                $immagine=new Immagine();
                $immagine->setMetadati($metaDati);
            }

            if(strstr($contentType, 'text'))
            {
                $this->ricercaSoggetti();
            }

            Yii::$app->session->set('controllo', $controllo);
            
            $redirectUrl = Url::to(['gestione-notizia/analisi', 'indice' => $indice]);

            if ($notizia->save()) {
                return $this->redirect($redirectUrl);
            }
            else 
            {
                // Salvataggio fallito, visualizza gli errori
                $errors = $notizia->getErrors();
                var_dump($errors);
            }
        }
        
        $indice=50;
        Yii::$app->session->set('indice', $indice);
        $redirectUrl = Url::to(['gestione-notizia/analisi']);
        return $this->redirect($redirectUrl);

        return $this->render('inserimento');
    }

    public function incongruenze($indice)
    {
        if($indice<20)
            return true;
        else
            return false;
    }

    public function categoria()
    {
        $link_notizia=Yii::$app->session->get('notizia');

        $urlId = rtrim(strtr(base64_encode($link_notizia), '+/', '-_'), '=');
    
        $apiKey = '7f131cfc0c77133d0ce81e4cea38e7acdb524ea930e443f31c6ddb9dd158829d';

        $url='https://www.virustotal.com/api/v3/urls/'.$urlId;

        $client = new Client(['baseUrl' => 'https://www.virustotal.com/api/v3/urls/']);
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

        return $responseData;
    }

    public function actionAnalisi()
    {
        $link_notizia=Yii::$app->session->get('notizia');
        $indice=Yii::$app->session->get('indice');

        /*$data='';
        $controllo = Yii::$app->request->get('controllo');
        if($controllo)
        {
            $messaggio=$this->segnalazione($link_notizia);
            $data='';
        }
        else
        {*/
            $client = new Client();

            $url='https://factchecktools.googleapis.com/v1alpha1/claims:search';

            $params=array(
                'key'=>'AIzaSyCoqCv86VXhWoIdCZnRLAEnrf6D62SU9MM',
                'query'=>$link_notizia
            );

            $stringaQuery=http_build_query($params);

            $requestUrl=$url.'?'.$stringaQuery;

            $response = $client->createRequest()
                        ->setMethod('GET')
                        ->setUrl($requestUrl)
                        ->send();

            if ($response->getStatusCode() == 200) {
                $data = $response->getData();
            } else {
                echo 'Richiesta fallita con ' . $response->getStatusCode() . ': ' . $response->getContent();
            }


            if (stripos($link_notizia, "http") !== false) 
            {
                if(stripos(substr($link_notizia, -7), ".") !== false)
                    $categoria=$this->categoria($link_notizia);          

            }         

            // Creazione dell'istanza del controller di destinazione
            $controller = Yii::$app->createController('fonte')[0];

            // Chiamata alla funzione desiderata del controller di destinazione
            $indiceFonte=$controller->actionAnalisiFonte();

            
            //$controllo=Yii::$app->session->get('controllo');

            $argomento=Yii::$app->session->get('argomenti');

            $fonti=$this->fontiArgomentiUguali();
            $notiziaAttendibile=$this->notiziaPiuAttendibile();

            $fontiInWhitelist=$controller->actionFontiInWhitelist();

            $fontiSimili=0;

            if(empty($fontiInWhitelist))
            {
                if($indiceFonte<80)
                {
                    $fontiSimili=$controller->actionFontiSimili();
                }
            }
        //}

        return $this->render('analisi', ['jsonData' => json_encode($data), 'indice' => json_encode($indice), 'messaggio' => $messaggio, 'fonti' => $fonti, 'notiziaAttendibile' => $notiziaAttendibile, 'indiceFonte' => $indiceFonte, 'fontiSimili' => $fontiSimili]);
    }

    public function ricercaSoggetti()
    {
        $link_notizia=Yii::$app->session->get('notizia');

        $client = new GuzzleClient();

        $response = $client->post('http://api.meaningcloud.com/topics-2.0', [
            'multipart' => [
                [
                    'name'     => 'key',
                    'contents' => '67c9440dc405a5d91f525337bc88baaf'
                ],
                [
                    'name'     => 'url',
                    'contents' => $link_notizia
                ],
                [
                    'name'     => 'lang',
                    'contents' => 'it'  # 2-letter code, like en es fr ...
                ],
                [
                    'name'     => 'tt',
                    'contents' => 'a'                   # all topics
                ]        
            ]
        ]);

        $status = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);
        $soggetti=json_encode($body);

        $dataSoggetti=json_decode($soggetti, true);
        $entity=$dataSoggetti['entity_list'];

        $primo=$entity['0'];
        $secondo=$entity['1'];
        $terzo=$entity['2'];
        $quinto=$entity['5'];

        $principale=$primo['form'];
        $secondario1=$secondo['form'];
        $secondario2=$terzo['form'];
        $luogo=$quinto['form'];

        $sogg=array($principale, $secondario1, $secondario2, $luogo);

        $soggetti=json_encode($sogg);
        $tempo=$dataSoggetti['time_expression_list'];
        $primo=$tempo['0'];
        $dataFatto=$primo['actual_time'];

        $dataFatto=json_encode($dataFatto);

        $testo=new Testo();
        $testo->setSoggetti($soggetti);
        $testo->setData($dataFatto);
    }  

    public function actionSegnalazione()
    {
        $model=new Notizia();
        //prendo la variabile dal form
        $link_notizia=$model->Notizia;

        $segnalazione=new Segnalazione();

        $segnalazione->Notizia=$link_notizia;
        $segn=Segnalazione::find()->select('Count')->where(['Notizia' => $link_notizia])->distinct()->all();
        if(empty($segn))
        {
            $segnalazione->Count=1;
            $count=1;
        }
        else
        {
            $count=$segn[0]->Count;
            $count++;
            $segnalazione->Count=$count;
        }
        
        

        if($count>5)
        {
            $segnalazione->Blacklistata=true;
        }

        $segnalazione->save();

        $messaggio='Grazie per la segnalazione';
        
        return $this->render('segnalazione', ['messaggio' => $messaggio]);
    }

    public function fontiArgomentiUguali()
    {
        $argomentoNotizia=Yii::$app->session->get('argomenti');

        $fonti=Fonte::find()->select('Fonte')->where(['Argomento' => $argomentoNotizia])->distinct()->all();

        return $fonti;
    }

    public function notiziaPiuAttendibile()
    {
        $argomentoNotizia=Yii::$app->session->get('argomenti');

        $risultato=Notizia::find()->select('Notizia', 'MAX(Indice)')->where(['Argomento' => $argomentoNotizia])->groupBy(['Notizia'])->all();
        //$indice=$risultato[0]['MAX(Indice)'];
        $indice=$risultato[0]['Notizia'];

        $notiziaAttendibile=Notizia::find()->select('Notizia')->where(['Indice' => $indice, 'Argomento' => $argomentoNotizia])->distinct()->all();

        return $notiziaAttendibile;
    }



}
