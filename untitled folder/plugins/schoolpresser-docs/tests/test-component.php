<?php

class SPD_Component_Test extends WP_UnitTestCase {

	function test_sample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function test_class_exists() {
		$this->assertTrue( class_exists( 'SPD_Component') );
	}

	function test_class_access() {
		$this->assertTrue( schoolpresser_docs()->component instanceof SPD_Component );
	}
}
