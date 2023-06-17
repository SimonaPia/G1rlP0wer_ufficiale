<?php
/** @var yii\web\View $this */
use yii\controller\FonteController;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
?>

<div class='notizia'>
<h1>Analisi della notizia</h1>

<div>
    <?php
        $data=json_decode($jsonData, true);

        if(empty($data))
        {
            if($indice<80)
                $risultato='notizia fake';
            else
                $risultato='notizia vera';
        }
        else 
        {
            $claims = $data['claims'];
            $claim = $claims[0];

            $claimReview = $claim['claimReview'];
            $review = $claimReview[0];

            $publisher = $review['publisher'];
            $publisherName = $publisher['name'];
            $publisherSite = $publisher['site'];

            $url = $review['url'];
            $title = $review['title'];
            $reviewDate = $review['reviewDate'];

            $textualRating = $review['textualRating'];
            $risultato=json_encode($textualRating);

            $languageCode = $review['languageCode'];
        }


        
        
    ?>
    <p>
        Il link inserito è <?= $risultato?>
        ed ha indice <?= $indice?><br>
        <?= $messaggio?><br>
        La fonti aventi stesso argomento sono <?php for ($i = 0; $i < count($fonti); $i++) {
            echo $fonti[$i]->Fonte . "<br>";
        }?><br>
        La notizia più attendibili avente lo stesso argomento è <?php for ($i = 0; $i < count($notiziaAttendibile); $i++) {
            echo $notiziaAttendibile[$i]->Notizia . "<br>";
        }?><br>
        L'indice della fonte della notizia è <?= $indiceFonte?><br>
        <?php if(!empty($fontiSimili))
            {
                for ($i = 0; $i < count($fontiSimili); $i++) {
                    echo "La fonte attendibile a cui devi rivolgerti è " . $fontiSimili[$i]->NomeFonte . "<br>";
            }
                
        }
        else
        {
            echo 'Non ci sono fonti simili';
        }?><br>

        <?php $form=ActiveForm::begin(); ?>
        <?php if (Yii::$app->session->get('isLoggedIn')): ?>
            <?= Html::submitButton('Blocca fonte', ['class'=>'blocca', 'formaction' => Url::to(['profilo/profilo'])]) ?>
        <?php endif; ?>
        <?php ActiveForm::end(); ?>
        
        
    </p>
</div>
</div>
