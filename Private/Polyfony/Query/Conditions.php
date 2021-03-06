<?php

namespace Polyfony\Query;

class Conditions extends Base {

	// whereIdenticalTo
	// add a condition
	public function where(array $conditions) :self {
		// for each provided strict condition
		foreach($conditions as $column => $value) {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} = :{$placeholder} )";
			// save the value
			$this->Values[":{$placeholder}"] = $value;
		}
		// return self to the next method
		return $this;
	}


	// where NotIdenticalTo
	// add a condition
	public function whereNot(array $conditions) :self {
		// for each provided strict condition
		foreach($conditions as $column => $value) {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} <> :{$placeholder} OR {$column} IS NULL )";
			// save the value
			$this->Values[":{$placeholder}"] = $value;
		}
		// return self to the next method
		return $this;
	}
	

	public function whereStartsWith(array $conditions) :self {
		// for each provided strict condition
		foreach($conditions as $column => $value) {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} LIKE :{$placeholder} )";
			// save the value
			$this->Values[":{$placeholder}"] = "{$value}%";
		}
		// return self to the next method
		return $this;
	}


	public function whereEndsWith(array $conditions) :self {
		// for each provided strict condition
		foreach($conditions as $column => $value) {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} LIKE :{$placeholder} )";
			// save the value
			$this->Values[":{$placeholder}"] = "%$value";
		}
		// return self to the next method
		return $this;
	}


	public function whereContains(array $conditions) :self {
		// for each provided strict condition
		foreach($conditions as $column => $value) {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} LIKE :{$placeholder} )";
			// save the value
			$this->Values[":{$placeholder}"] = "%{$value}%";
		}
		// return self to the next method
		return $this;
	}


	public function whereMatch(array $conditions) :self {	
		// for each provided strict condition
		foreach($conditions as $column => $value) {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} MATCH :{$placeholder} )";
			// save the value
			$this->Values[":{$placeholder}"] = $value;
		}
		// return self to the next method
		return $this;
	}

	// legacy alias
	public function whereHigherThan(array $conditions) :self {
		return $this->whereGreaterThan($conditions);
	}

	public function whereGreaterThan(array $conditions) :self {
		// for each provided strict condition
		foreach($conditions as $column => $value) {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} > :{$placeholder} )";
			// save the value
			$this->Values[":{$placeholder}"] = $value;
		}
		// return self to the next method
		return $this;
	}

	// legacy alias
	public function whereLowerThan(array $conditions) :self {
		return $this->whereLessThan($conditions);
	}

	public function whereLessThan(array $conditions) :self {
		// for each provided strict condition
		foreach($conditions as $column => $value) {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$column);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} < :{$placeholder} )";
			// save the value
			$this->Values[":{$placeholder}"] = $value;
		}
		// return self to the next method
		return $this;
	}


	public function whereBetween(
		string $column, 
		float $lower, 
		float $higher
	) :self {
		// secure the column name
		list(
			$column, 
			$placeholder
		) = Convert::columnToPlaceholder($this->Quote ,$column);
		// save the condition
		$this->Conditions[] = "{$this->Operator} ( {$column} BETWEEN :min_{$placeholder} AND :max_{$placeholder} )";
		// add the min value
		$this->Values[":min_{$placeholder}"] = $lower;
		// add the max value
		$this->Values[":max_{$placeholder}"] = $higher;
		// return self to the next method
		return $this;
	}

	// this should be renamed whereBlank
	// we are still supporting NON-array parameter, this will be removed at some point in time
	public function whereEmpty($conditions) :self {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each condition
			foreach($conditions as $column) {
				// add the condition
				$this->whereEmpty($column);
			}
		}
		else {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$conditions);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} == :empty_{$placeholder} OR {$column} IS NULL )";
			// add the empty value
			$this->Values[":empty_{$placeholder}"] = '';
		}
		// return self to the next method
		return $this;
	}

	// this should be renamed whereNotBlank
	// we are still supporting NON-array parameter, this will be removed at some point in time
	public function whereNotEmpty($conditions) :self {
		// if provided conditions are an array
		if(is_array($conditions)) {
			// for each condition
			foreach($conditions as $column) {
				// add the condition
				$this->whereNotEmpty($column);
			}
		}
		else {
			// secure the column name
			list(
				$column, 
				$placeholder
			) = Convert::columnToPlaceholder($this->Quote ,$conditions);
			// save the condition
			$this->Conditions[] = "{$this->Operator} ( {$column} <> :empty_{$placeholder} AND {$column} IS NOT NULL )";
			// add the empty value
			$this->Values[":empty_{$placeholder}"] = '';
		}
		// return self to the next method
		return $this;
	}

	// this should only accept arrays... 
	// this behavior is not coherent with the rest of the class
	public function whereNull($column) :self {
		// secure the column name
		list(
			$column, 
			$placeholder
		) = Convert::columnToPlaceholder($this->Quote ,$column);
		// save the condition
		$this->Conditions[] = "{$this->Operator} ( {$column} IS NULL )";
		// add the empty value
		$this->Values[":empty_{$placeholder}"] = '';
		// return self to the next method
		return $this;
	}

	// this should only accept arrays... 
	// this behavior is not coherent with the rest of the class
	public function whereNotNull($column) :self {
		// secure the column name
		list($column) = Convert::columnToPlaceholder($this->Quote ,$column);
		// save the condition
		$this->Conditions[] = "{$this->Operator} ( {$column} IS NOT NULL )";
		// return self to the next method
		return $this;
	}


}