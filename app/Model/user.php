<?php
/* @TODO optimize all find() options across the controllers */
App::uses('AuthComponent', 'Controller/Component');
class User extends AppModel {
	public $name = 'User';
    public $conditions = array("User.active"=>1);
    
	public $hasMany = array(
        'UsersActivity' => array(
			'className'  => 'UsersActivity',
			'foreignKey' => 'user_id',
            'dependent'  => true,
            "limit"      => 100,
            "order"      => array("UsersActivity.id"=>"DESC")
		),
        'UsersFriend' => array(
			'className' => 'UsersFriend',
			'foreignKey' => 'user_id'
		)
	);
    
	public $belongsTo = array(
		'Role' => array(
			'className' => 'Role',
			'foreignKey' => 'role_id'
		)
	);
    
    public $hasOne = array(
		'UsersPrivacy' => array(
			'className' => 'UsersPrivacy',
			'foreignKey' => 'user_id'
		)
	);

    public $validate = array(
		'first_name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'First name is required.',
			),
		),
        'last_name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Last name is required.',
			),
		),
        'gender' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'What is your gender?',
			),
		),
        'network' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Your mobile network is required',
			),
		),
        'mobile_number' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Mobile number is required.',
			),
		),
        'email' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Email is required.',
			),
            'isUnique' => array(
                'rule'    => 'isUnique',
                'message' => 'This email address has already been taken.'
			),
            'emails' => array(
                'rule'    => 'email',
                'message' => 'Please supply a valid email address.'
			),
		),
        
        'password' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Password is required.',
			),
            'minLength'=>array(
                'rule' => array('minLength', '8'),
                'message' => 'Password should be mimimum of 8 characters long'
            ),
            'alphaNumeric'=>array(            
                'rule'    => 'alphaNumeric',
                'message' => 'Password must only contain letters and numbers.'
            )    
		)
	);

    public function afterFind($results, $primary) {
        return $results;
    }

    public function bindDatingsModel() {
        $this->bindModel(array(
            'hasOne' => array(
                'UsersDating' => array(
                    'foreignKey' => false,
                    'conditions' => array('User.id = UsersDating.user_id')
                )
            )
        ));
    }

    public function getUsersLocation($locationId) {
        $this->bindModel(array('belongsTo' => array('ProvincesLocation'=>array('foreignKey'=>"location_id"))));
        $param["conditions"] = array("ProvincesLocation.id"=>$locationId);
        $results = $this->ProvincesLocation->find("first", $param);
        return $results;
    }
	public function beforeSave() {
        if (isset($this->data[$this->alias]['password'])) {
            $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
        }
        return true;
    }

}
