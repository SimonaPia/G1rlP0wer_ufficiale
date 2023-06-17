<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Segnalazione".
 *
 * @property int $ID
 * @property string|null $Notizia
 * @property int|null $Count
 * @property int|null $Blacklistata
 */
class Segnalazione extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Segnalazione';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Count', 'Blacklistata'], 'integer'],
            [['Notizia'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Notizia' => 'Notizia',
            'Count' => 'Count',
            'Blacklistata' => 'Blacklistata',
        ];
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
