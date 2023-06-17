<?php
/** @var yii\web\View $this */
use yii\controller\FonteController;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class='notizia'>
<h1>BENVENUTA/O NEL TUO PROFILO <?= $nome?></h1>

<div>
    <p>    
        
        <h3>Le tue fonti bloccate sono<br>
        <?php  for ($i = 0; $i < count($lista); $i++) {
            $row = $lista[$i];
            echo $row['Fonte'] .'<br>';
        }?>
        
        
    </p>
</div>
</div>
