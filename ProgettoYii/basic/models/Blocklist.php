<?php

namespace app\models;

use Yii;
use app\models\UtenteRegistrato;
use app\models\Fonte;

/**
 * This is the model class for table "Blocklist".
 *
 * @property int $Utente
 * @property int $Fonte
 */
class Blocklist extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Blocklist';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Utente', 'Fonte'], 'required'],
            [['Utente', 'Fonte'], 'integer'],
            [['Utente', 'Fonte'], 'unique', 'targetAttribute' => ['Utente', 'Fonte']],
            [['Utente'], 'exist', 'skipOnError' => true, 'targetClass' => UtenteRegistrato::class, 'targetAttribute' => ['Utente' => 'ID']],
            [['Fonte'], 'exist', 'skipOnError' => true, 'targetClass' => Fonte::class, 'targetAttribute' => ['Fonte' => 'ID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Utente' => 'Utente',
            'Fonte' => 'Fonte',
        ];
    }

    public function getUtente()
    {
        return $this->hasOne(UtenteRegistrato::class, ['ID' => 'Utente']);
    }

    public function getFonte()
    {
        return $this->hasOne(Fonte::class, ['ID' => 'Fonte']);
    }

    public function getAuthKey()
    {
        return null;
    }

    public function getId()
    {
        return $this->ID;
    }

    public function validateAuthKey($authKey)
    {
        //return $this->authKey===$authKey;
        return null;
    }

    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type=null)
    {
        throw new \yii\base\NotSupportedException();
    }

    public static function findByEmail($email)
    {
        return self::findOne(['Email'=>$email]);
    }

    public function validatePassword($password)
    {
        return $this->Password===$password;
    }

    public function setPassword($password)
    {
        $this->Password=$password;
    }

	public function inserimentoForm()
	{
		$link=$this->link;
	}

	public function getLink()
	{
		return $link;
	}
}
