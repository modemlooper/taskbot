<?php

class SPD_Docs_Test extends WP_UnitTestCase {

	function test_sample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function test_class_exists() {
		$this->assertTrue( class_exists( 'SPD_Docs') );
	}

	function test_class_access() {
		$this->assertTrue( schoolpresser_docs()->docs instanceof SPD_Docs );
	}

  function test_cpt_exists() {
    $this->assertTrue( post_type_exists( 'spd-docs' ) );
  }
}
