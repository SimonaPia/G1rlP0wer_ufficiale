<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Whitelist".
 *
 * @property int $ID
 * @property string|null $NomeFonte
 * @property int|null $Fonte
 */
class Whitelist extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Whitelist';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Fonte'], 'integer'],
            [['NomeFonte'], 'string', 'max' => 255],
            [['Fonte'], 'exist', 'skipOnError' => true, 'targetClass' => Fonte::class, 'targetAttribute' => ['Fonte' => 'ID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'NomeFonte' => 'Nome Fonte',
            'Fonte' => 'Fonte',
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
