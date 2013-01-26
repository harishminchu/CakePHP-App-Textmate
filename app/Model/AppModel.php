<?php

class AppModel extends Model{

/** Quick Tip - Doing Ad-hoc Joins in Model::find()
 *
 * http://bakery.cakephp.org/articles/nate/2009/01/21/quick-tip-doing-ad-hoc-joins-in-model-find
 * Herewith, a little-known query trick that allows you to do simple ad-hoc joins in your CakePHP finder queries. 
 * No binding or unbinding required.
 * Note: This only works if you are using the new Model::find() syntax, 
 * which only takes two parameters. If not, please refer to the Cookbook or API. 
 */
    public function find($type, $options = array()) {
        if (!isset($options['joins'])) {
            $options['joins'] = array();
        }

        switch ($type) {
            case 'matches':
                if (!isset($options['model']) || !isset($options['scope'])) {
                    break;
                }
                $assoc = $this->hasAndBelongsToMany[$options['model']];
                $bind = "{$assoc['with']}.{$assoc['foreignKey']} = {$this->alias}.{$this->primaryKey}";

                $options['joins'][] = array(
                    'table' => $assoc['joinTable'],
                    'alias' => $assoc['with'],
                    'type' => 'inner',
                    'foreignKey' => false,
                    'conditions'=> array($bind)
                );

                $bind = $options['model'] . '.' . $this->{$options['model']}->primaryKey . ' = ';
                $bind .= "{$assoc['with']}.{$assoc['associationForeignKey']}";

                $options['joins'][] = array(
                    'table' => $this->{$options['model']}->table,
                    'alias' => $options['model'],
                    'type' => 'inner',
                    'foreignKey' => false,
                    'conditions'=> array($bind) + (array)$options['scope'],
                );
                unset($options['model'], $options['scope']);
                $type = 'all';
            break;
        }
        return parent::find($type, $options);
    }  	
    
    function setBoolean($field) {
        $this->data[$this->name][$field] = isset($this->data[$this->name][$field]) ? 1 : 0;
    }

    function setDate($field, $format = 'Y-m-d H:i') {
        $model = $this->name;
        $fieldAddOns = array('min', 'hour', 'day', 'month', 'year');
        $dateInfo = array();
        foreach ($fieldAddOns as $fieldAddOn){
            $dateInfo[$fieldAddOn] =
            isset($this->data[$model][$field . '_' . $fieldAddOn]) ?
            intval($this->data[$model][$field . '_' . $fieldAddOn]) : null;
        }

        $this->data[$model][$field] = date($format, mktime($dateInfo['hour'], $dateInfo['min'],
                                      null, $dateInfo['month'],
                                      $dateInfo['day'], $dateInfo['year']));
    }
    
    function isUnique($fieldsAndValue) {
        $this->recursive = -1;
        $arg["conditions"] = $fieldsAndValue;
        $count = $this->find("count", $arg);
        if($count){ 
            return false;
        } else {
            return true;
        }
    } 
 	
}
?>