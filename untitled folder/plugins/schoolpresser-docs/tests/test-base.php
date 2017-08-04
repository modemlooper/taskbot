<?php

class BaseTest extends WP_UnitTestCase {

	function test_sample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function test_class_exists() {
		$this->assertTrue( class_exists( 'SchoolPresser_Docs') );
	}
	
	function test_get_instance() {
		$this->assertTrue( schoolpresser_docs() instanceof SchoolPresser_Docs );
	}
}
