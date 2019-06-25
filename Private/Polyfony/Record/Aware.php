<?php
 
namespace Polyfony\Record;
use Polyfony\Exception as Exception;
use Polyfony\Query\Convert as Convert;
use Polyfony\Query as Query;
use Polyfony\Database as Database;

class Aware {
	
	// storing variable that does not reflect the database table structure
	protected $_;
	
	// storing the validators
	const VALIDATORS = [];

	// storing the filters
	const FILTERS = [];

	// create a object from scratch, of fetch it in from its table/id
	public function __construct(
		$conditions_to_find_the_record = null
	) {

		// init the list of altered columns
		$this->_ = [
			// id of the record
			'id'		=> isset($this->id) ? $this->id : null,
			// table of the record
			'table'		=> get_class($this) != 'Polyfony\Record' ? 
				str_replace('Models\\','',get_class($this)) : null,
			// list of altered columns since the retrieval from the database
			'altered'	=> []
		];
		// if conditions are provided
		if($conditions_to_find_the_record !== null) {
			// we instanciate ourself from an existing database record
			$this->__constructFromExistingRecord($conditions_to_find_the_record);
		}
		// return self
		return $this;
		
	}
	
	private function __constructFromExistingRecord($conditions) :void {

		// if conditions is not an array, we assume it is the id of the record
		$conditions = is_array($conditions) ? $conditions : ['id'=>$conditions];
		// grab that object from the database
		$record = self::_select()->first()->where($conditions)->execute();
		// if we didn't find the record
		if(!$record) {
			// throw a 404 Not found Exception
			Throw new Exception(
				"new Models\\{$this->_['table']} : Object not found in the database", 
				404
			);
		}
		// clone the found record
		$this->replicate($record);

	}

	private function replicate($clone) {
		// for each attribute
		foreach(get_object_vars($clone) as $attribute => $value) {
			// clone that attribute
			$this->{$attribute} = $value;
		}
		// replicate the id if it is available
		$this->_['id'] = isset($this->id) ? $this->id : $this->_['id'];
	}
	
	public function get(
		string $column, 
		bool $get_it_raw = false
	) {
		// return the columns or null if it does not exist		
		return 
			isset($this->{$column}) && 
			strlen($this->{$column}) ? 
				Convert::valueFromDatabase(
					$column, 
					$this->{$column}, 
					$get_it_raw
				) : 
				null;
	}
	
	public function set(
		$column_or_array, 
		$value = null
	) {
		// if we want to set a batch of values
		if(is_array($column_or_array)) {
			// for each value to set
			foreach($column_or_array as $column => $value) {
				// set that individual column
				$this->set($column, $value);
			}
		}
		// setting only a single value
		else {
			// validate the value according to what we know (nulls, existing columns, const validators)
			Validator::isThisValueAcceptable(
				$this->_['table'], 
				get_class($this), 
				$column_or_array, 
				$value
			);
			// filter the value according to the models defined filters
			$value = Filter::sanitizeThisValue(
				$column_or_array, 
				$value,
				get_class($this)
			);
			// convert the value depending on the column name
			$this->{$column_or_array} = Convert::valueForDatabase(
				$column_or_array, 
				$value
			);
			// update the altered list
			$this->alter($column_or_array);
		}
		// return self
		return($this);
	}

	// add an element to the end of a magic array column
	public function push(
		string $column_name, 
		$array_or_value
	) :self {

		// remove the _array extension (if any) and add it back
		$column_name = str_replace('_array', '', $column_name) . '_array';

		// get the column's value
		$column_s_array = (array) $this->get($column_name);

		// push the new value(s) in it
		array_push(
			$column_s_array, 
			$array_or_value
		);

		// push the array or value to the end of the column's array
		return $this->set([$column_name => $column_s_array]);

	}
	
	// magic
	public function __toArray(
		bool $raw = false, 
		bool $altered = false
	) {
		// declare an empty array
		$array = [];
		// what to iterate on
		$attributes = $altered ? 
			$this->_['altered'] : 
			array_keys(get_object_vars($this));
		// for each attribute of this object
		foreach($attributes as $attribute){
			// if the attribute is not internal
			if($attribute != '_') {
				// convert or not
				$array[$attribute] = $raw ? 
					$this->get($attribute,true) : 
					$this->get($attribute,false);
			}
		}
		return $array;
	}
	
	// magic
	public function __toString() {
		// a string to sybolize this record
		return $this->_['id'] ? $this->_['id'] : 0;
	}

	// magic
	public function __clone() {
		// set all columns as altered
		$this->_['altered'] = array_keys(get_object_vars($this));
		// remove the hidden column
		unset($this->_['altered'][0]);
		// remove the hidden id, so that the object is recognized as absent from the database
		$this->_['id'] = null;
		// remove the id attribute too
		$this->id = null;
	}

	private function alter(
		string $column
	) {
		// push
		$this->_['altered'][] = $column;
		// deduplicate
		$this->_['altered'] = array_unique($this->_['altered']);
	}
	
	// update or create
	public function save() :bool {
		
		// if an id already exists
		if($this->_['id']) {
			// we can update and return the number of affected rows (0 on error, 1 on success)
			return (bool) self::_update()
				->set(
					$this->__toArray(
						true, 
						true
					)
				)
				->where(['id'=>$this->_['id']])
				->execute();
		}
		// this is a new record
		else {
			// try to insert it
			$inserted_object = self::create(
				$this->__toArray(
					true, 
					true
				)
			);
			// if insertion succeeded, return true
			if($inserted_object) {
				// clone ourselves with what the database returneds, a full fledged object
				$this->replicate($inserted_object);
				// return success
				return true;
			}
			// didnt insert
			else {
				// failure feedback
				return false;
			}
		}
		
	}
	
	// delete
	public function delete() {
		// if id or table if missing
		if(
			!$this->_['table'] || 
			!$this->_['id']
		) {
			// throw an exception
			Throw new Exception(
				get_class($this).
				'->delete() : cannot delete a record without table and id', 
				400
			);
		}
		// if it went well
		return (bool) self::_delete()
			->where(['id'=>$this->_['id']])
			->execute();
	}

	// returns the name of the class that has extended this one (aka, the Table name)
	protected static function tableName() :string {
		// removed the namespace from the class name
		return str_replace('Models\\','',get_called_class());
	}
	
	// shortcut to insert an element
	public static function create(
		array $columns_and_values=[]
	) {

		return Database::query()
			->insert($columns_and_values)
			->into(self::tableName())
			->execute();

	}

	// shortcut that bootstraps a select query
	public static function _select(
		array $select=[]
	) :Query {

		// returns a Query object, to execute, or to complement with some more parameters
		return Database::query()
			->select($select)
			->from(self::tableName());

	}

	// shortcut that bootstraps an update query
	public static function _update() :Query {

		// returns a Query object, to execute, or to complement with some more parameters
		return Database::query()
			->update(self::tableName());

	}

	// shortcut that bootstraps a delete query
	public static function _delete() :Query {

		// returns a Query object, to execute, or to complement with some more parameters
		return Database::query()
			->delete()
			->from(self::tableName());

	}

}


?>
